import json
import subprocess
import time
import urllib.error
import urllib.request
from pathlib import Path

import pytest

ROOT = Path(__file__).resolve().parent.parent
DB_FILE = ROOT / 'data.sqlite'


@pytest.fixture(scope="module")
def server():
    if DB_FILE.exists():
        DB_FILE.unlink()
    proc = subprocess.Popen(
        ["php", "-S", "127.0.0.1:8001", "server.php"],
        cwd=str(ROOT),
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    time.sleep(1)
    yield "http://127.0.0.1:8001"
    proc.terminate()
    proc.wait()
    if DB_FILE.exists():
        DB_FILE.unlink()


def test_status_endpoint(server):
    with urllib.request.urlopen(f"{server}/api/status") as response:
        data = json.loads(response.read().decode())
    assert data["status"] == "ok"


FEATURES = {
    "dashboard": "stats",
    "terapiak": "therapies",
    "gyogyszerek": "medications",
    "ertesitesek": "notifications",
    "betegek": "patients",
}


@pytest.mark.parametrize("feature,key", FEATURES.items())
def test_secured_endpoints(server, feature, key):
    url = f"{server}/api/{feature}"
    req = urllib.request.Request(url)
    with pytest.raises(urllib.error.HTTPError) as excinfo:
        urllib.request.urlopen(req)
    assert excinfo.value.code == 403

    req = urllib.request.Request(url, headers={"X-API-Key": "secret123"})
    with urllib.request.urlopen(req) as response:
        data = json.loads(response.read().decode())
    assert data["feature"] == feature
    assert key in data


def test_chat_access_levels(server):
    url = f"{server}/api/chat"

    # unauthenticated
    req = urllib.request.Request(url)
    with pytest.raises(urllib.error.HTTPError) as excinfo:
        urllib.request.urlopen(req)
    assert excinfo.value.code == 403

    # rendszergazda sees all categories
    headers = {"X-API-Key": "secret123", "X-Role": "rendszergazda"}
    req = urllib.request.Request(url, headers=headers)
    with urllib.request.urlopen(req) as response:
        data = json.loads(response.read().decode())
    cats = {m["category"] for m in data["messages"]}
    assert cats == {"general", "partner", "organization", "private"}

    # admin sees general and partner
    headers = {"X-API-Key": "secret123", "X-Role": "admin"}
    req = urllib.request.Request(url, headers=headers)
    with urllib.request.urlopen(req) as response:
        data = json.loads(response.read().decode())
    cats = {m["category"] for m in data["messages"]}
    assert cats == {"general", "partner"}

    # gondozo sees general and own private
    headers = {"X-API-Key": "secret123", "X-Role": "gondozo", "X-User": "gondozo1"}
    req = urllib.request.Request(url, headers=headers)
    with urllib.request.urlopen(req) as response:
        data = json.loads(response.read().decode())
    cats = {m["category"] for m in data["messages"]}
    assert cats == {"general", "private"}


def test_module_crud_operations(server):
    headers = {"X-API-Key": "secret123", "Content-Type": "application/json"}

    # add and remove therapy
    tdata = {"patient": "patient1", "type": "Massage", "status": "planned"}
    req = urllib.request.Request(f"{server}/api/terapiak", data=json.dumps(tdata).encode(), headers=headers, method="POST")
    with urllib.request.urlopen(req) as response:
        added = json.loads(response.read().decode())
    tid = added["added"]["id"]
    req = urllib.request.Request(f"{server}/api/terapiak/{tid}", headers={"X-API-Key": "secret123"}, method="DELETE")
    with urllib.request.urlopen(req) as response:
        deleted = json.loads(response.read().decode())
    assert deleted["deleted"] == tid

    # add and remove medication
    mdata = {"name": "Ibuprofen", "stock": 30}
    req = urllib.request.Request(f"{server}/api/gyogyszerek", data=json.dumps(mdata).encode(), headers=headers, method="POST")
    with urllib.request.urlopen(req) as response:
        added = json.loads(response.read().decode())
    mid = added["added"]["id"]
    req = urllib.request.Request(f"{server}/api/gyogyszerek/{mid}", headers={"X-API-Key": "secret123"}, method="DELETE")
    with urllib.request.urlopen(req) as response:
        deleted = json.loads(response.read().decode())
    assert deleted["deleted"] == mid

    # add and remove notification
    ndata = {"text": "Teszt", "urgent": False}
    req = urllib.request.Request(f"{server}/api/ertesitesek", data=json.dumps(ndata).encode(), headers=headers, method="POST")
    with urllib.request.urlopen(req) as response:
        added = json.loads(response.read().decode())
    nid = added["added"]["id"]
    req = urllib.request.Request(f"{server}/api/ertesitesek/{nid}", headers={"X-API-Key": "secret123"}, method="DELETE")
    with urllib.request.urlopen(req) as response:
        deleted = json.loads(response.read().decode())
    assert deleted["deleted"] == nid

    # add and remove patient
    pdata = {"id": "patientX", "name": "Teszt Elek"}
    req = urllib.request.Request(f"{server}/api/betegek", data=json.dumps(pdata).encode(), headers=headers, method="POST")
    with urllib.request.urlopen(req) as response:
        added = json.loads(response.read().decode())
    pid = added["added"]["id"]
    req = urllib.request.Request(f"{server}/api/patients/{pid}/chart", headers={"X-API-Key": "secret123"})
    with urllib.request.urlopen(req) as response:
        chart = json.loads(response.read().decode())
    assert chart["patient"] == pid
    req = urllib.request.Request(f"{server}/api/betegek/{pid}", headers={"X-API-Key": "secret123"}, method="DELETE")
    with urllib.request.urlopen(req) as response:
        deleted = json.loads(response.read().decode())
    assert deleted["deleted"] == pid


def test_rendszergazda_can_add_admin(server):
    url = f"{server}/api/users/add?role=admin"
    headers = {"X-API-Key": "secret123", "X-Role": "rendszergazda"}
    req = urllib.request.Request(url, headers=headers)
    with urllib.request.urlopen(req) as response:
        data = json.loads(response.read().decode())
    assert data["added"] == "admin"


def test_admin_cannot_add_rendszergazda(server):
    url = f"{server}/api/users/add?role=rendszergazda"
    headers = {"X-API-Key": "secret123", "X-Role": "admin"}
    req = urllib.request.Request(url, headers=headers)
    with pytest.raises(urllib.error.HTTPError) as excinfo:
        urllib.request.urlopen(req)
    assert excinfo.value.code == 403


def test_admin_can_add_gondozo(server):
    url = f"{server}/api/users/add?role=gondozo"
    headers = {"X-API-Key": "secret123", "X-Role": "admin"}
    req = urllib.request.Request(url, headers=headers)
    with urllib.request.urlopen(req) as response:
        data = json.loads(response.read().decode())
    assert data["added"] == "gondozo"


def test_patient_chart_endpoint(server):
    url = f"{server}/api/patients/patient1/chart"
    headers = {"X-API-Key": "secret123"}
    req = urllib.request.Request(url, headers=headers)
    with urllib.request.urlopen(req) as response:
        data = json.loads(response.read().decode())
    assert data["patient"] == "patient1"
    for field in ["medications", "diseases", "therapies", "caregiver"]:
        assert field in data


def test_admin_assigns_and_removes_caregiver(server):
    assign = f"{server}/api/patients/patA/caregiver?caregiver=gondozo2"
    headers = {"X-API-Key": "secret123", "X-Role": "admin"}
    req = urllib.request.Request(assign, headers=headers, method="POST")
    with urllib.request.urlopen(req) as response:
        data = json.loads(response.read().decode())
    assert data["caregiver"] == "gondozo2"

    chart = f"{server}/api/patients/patA/chart"
    req = urllib.request.Request(chart, headers={"X-API-Key": "secret123"})
    with urllib.request.urlopen(req) as response:
        info = json.loads(response.read().decode())
    assert info["caregiver"] == "gondozo2"

    remove = f"{server}/api/patients/patA/caregiver"
    req = urllib.request.Request(remove, headers=headers, method="DELETE")
    with urllib.request.urlopen(req) as response:
        data = json.loads(response.read().decode())
    assert data["caregiver"] is None

    req = urllib.request.Request(chart, headers={"X-API-Key": "secret123"})
    with urllib.request.urlopen(req) as response:
        info = json.loads(response.read().decode())
    assert info["caregiver"] == "gondozo1"


def test_gondozo_cannot_assign_caregiver(server):
    assign = f"{server}/api/patients/patB/caregiver?caregiver=g2"
    headers = {"X-API-Key": "secret123", "X-Role": "gondozo"}
    req = urllib.request.Request(assign, headers=headers, method="POST")
    with pytest.raises(urllib.error.HTTPError) as excinfo:
        urllib.request.urlopen(req)
    assert excinfo.value.code == 403


def test_vacation_toggle(server):
    url = f"{server}/api/users/gondozo1/vacation?on=1"
    headers = {"X-API-Key": "secret123", "X-Role": "gondozo", "X-User": "gondozo1"}
    req = urllib.request.Request(url, headers=headers, method="POST")
    with urllib.request.urlopen(req) as response:
        data = json.loads(response.read().decode())
    assert data["vacation"] is True

    url2 = f"{server}/api/users/gondozo1/vacation?on=0"
    headers = {"X-API-Key": "secret123", "X-Role": "gondozo", "X-User": "gondozo2"}
    req = urllib.request.Request(url2, headers=headers, method="POST")
    with pytest.raises(urllib.error.HTTPError) as excinfo:
        urllib.request.urlopen(req)
    assert excinfo.value.code == 403

    headers = {"X-API-Key": "secret123", "X-Role": "admin"}
    req = urllib.request.Request(url2, headers=headers, method="POST")
    with urllib.request.urlopen(req) as response:
        data = json.loads(response.read().decode())
    assert data["vacation"] is False

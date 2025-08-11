import json
import subprocess
import time
import urllib.error
import urllib.request
from pathlib import Path

import pytest

ROOT = Path(__file__).resolve().parent.parent


@pytest.fixture(scope="module")
def server():
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


def test_status_endpoint(server):
    with urllib.request.urlopen(f"{server}/api/status") as response:
        data = json.loads(response.read().decode())
    assert data["status"] == "ok"


FEATURES = [
    "dashboard",
    "terapiak",
    "gyogyszerek",
    "ertesitesek",
    "betegek",
]


@pytest.mark.parametrize("feature", FEATURES)
def test_secured_endpoints(server, feature):
    url = f"{server}/api/{feature}"
    req = urllib.request.Request(url)
    with pytest.raises(urllib.error.HTTPError) as excinfo:
        urllib.request.urlopen(req)
    assert excinfo.value.code == 403

    req = urllib.request.Request(url, headers={"X-API-Key": "secret123"})
    with urllib.request.urlopen(req) as response:
        data = json.loads(response.read().decode())
    assert data["feature"] == feature


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

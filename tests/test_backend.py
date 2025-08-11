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
    "chat",
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

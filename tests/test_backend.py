import json
import subprocess
import time
import urllib.request
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent


def test_status_endpoint():
    proc = subprocess.Popen(
        ["php", "-S", "127.0.0.1:8001", "server.php"],
        cwd=str(ROOT),
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    try:
        time.sleep(1)
        with urllib.request.urlopen("http://127.0.0.1:8001/api/status") as response:
            data = json.loads(response.read().decode())
        assert data["status"] == "ok"
    finally:
        proc.terminate()
        proc.wait()

#!/bin/bash
set -e

php -S localhost:8000 server.php >/tmp/php-server.log 2>&1 &
SERVER_PID=$!
sleep 1

# Positive test: valid POST and GET
payload='{"name":"Anna","taj":"123456789","phone":"+361234567","address":"Budapest 1","doctor_names":"Dr Test","doctor_address":"Budapest 2","doctor_phone":"+36111222"}'
STATUS=$(curl -s -o /tmp/out.txt -w "%{http_code}" -X POST -H "Content-Type: application/json" -d "$payload" "http://localhost:8000/server.php")
if [ "$STATUS" != "200" ]; then
  echo "Expected 200 for valid POST, got $STATUS"
  cat /tmp/out.txt
  kill $SERVER_PID
  exit 1
fi
ID=$(php -r 'echo json_decode(file_get_contents("/tmp/out.txt"), true)["id"];')

STATUS=$(curl -s -o /tmp/out.txt -w "%{http_code}" "http://localhost:8000/server.php?id=$ID")
if [ "$STATUS" != "200" ]; then
  echo "Expected 200 for valid GET, got $STATUS"
  cat /tmp/out.txt
  kill $SERVER_PID
  exit 1
fi

echo "Positive POST/GET tests passed"

# Test invalid GET parameter
STATUS=$(curl -s -o /tmp/out.txt -w "%{http_code}" "http://localhost:8000/server.php?id=abc")
if [ "$STATUS" != "400" ]; then
  echo "Expected 400 for invalid id, got $STATUS"
  cat /tmp/out.txt
  kill $SERVER_PID
  exit 1
fi

echo "Invalid GET parameter test passed"

# Test invalid TAJ in POST
payload='{"name":"Anna","taj":"abc","phone":"+361234567","address":"Budapest 1","doctor_names":"Dr Test","doctor_address":"Budapest 2","doctor_phone":"+36111222"}'
STATUS=$(curl -s -o /tmp/out.txt -w "%{http_code}" -X POST -H "Content-Type: application/json" -d "$payload" "http://localhost:8000/server.php")
if [ "$STATUS" != "400" ]; then
  echo "Expected 400 for invalid TAJ, got $STATUS"
  cat /tmp/out.txt
  kill $SERVER_PID
  exit 1
fi

echo "Invalid TAJ POST test passed"

# Test missing field in POST
payload='{"name":"Anna","taj":"123456789","address":"Budapest 1","doctor_names":"Dr Test","doctor_address":"Budapest 2","doctor_phone":"+36111222"}'
STATUS=$(curl -s -o /tmp/out.txt -w "%{http_code}" -X POST -H "Content-Type: application/json" -d "$payload" "http://localhost:8000/server.php")
if [ "$STATUS" != "400" ]; then
  echo "Expected 400 for missing field, got $STATUS"
  cat /tmp/out.txt
  kill $SERVER_PID
  exit 1
fi

echo "Missing field POST test passed"

kill $SERVER_PID

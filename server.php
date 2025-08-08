<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/api/status') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
    return;
}

if ($path === '/' || $path === '') {
    header('Content-Type: text/html');
    readfile('telemedicine-html-app.html');
    return;
}

http_response_code(404);
echo 'Not Found';


<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

function require_auth() {
    $key = $_SERVER['HTTP_X_API_KEY'] ?? '';
    if ($key !== 'secret123') {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'forbidden']);
        return false;
    }
    return true;
}

function require_role($allowed) {
    $role = strtolower($_SERVER['HTTP_X_ROLE'] ?? '');
    if (!in_array($role, $allowed)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'unauthorized']);
        return false;
    }
    return $role;
}

if ($path === '/api/status') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
    return;
}

$secure = [
    '/api/dashboard' => 'dashboard',
    '/api/terapiak' => 'terapiak',
    '/api/gyogyszerek' => 'gyogyszerek',
    '/api/chat' => 'chat',
    '/api/ertesitesek' => 'ertesitesek',
    '/api/betegek' => 'betegek'
];

if (array_key_exists($path, $secure)) {
    if (!require_auth()) {
        return;
    }
    header('Content-Type: application/json');
    echo json_encode(['feature' => $secure[$path]]);
    return;
}

if ($path === '/api/users/add' || $path === '/api/users/delete') {
    if (!require_auth()) {
        return;
    }
    $actor = require_role(['rendszergazda', 'admin']);
    if (!$actor) {
        return;
    }
    $target = strtolower($_GET['role'] ?? '');
    if ($actor === 'admin' && in_array($target, ['rendszergazda', 'admin'])) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'forbidden']);
        return;
    }
    header('Content-Type: application/json');
    echo json_encode([
        $path === '/api/users/add' ? 'added' : 'deleted' => $target
    ]);
    return;
}

if ($path === '/' || $path === '') {
    header('Content-Type: text/html');
    readfile('telemedicine-html-app.html');
    return;
}

http_response_code(404);
echo 'Not Found';


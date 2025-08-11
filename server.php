<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

function load_data() {
    $file = __DIR__ . '/data.json';
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        if (is_array($data)) {
            return $data;
        }
    }
    return ['caregivers' => [], 'vacation' => []];
}

function save_data($data) {
    $file = __DIR__ . '/data.json';
    file_put_contents($file, json_encode($data));
}

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
    '/api/ertesitesek' => 'ertesitesek',
    '/api/betegek' => 'betegek'
];

if ($path === '/api/chat') {
    if (!require_auth()) {
        return;
    }
    $role = strtolower($_SERVER['HTTP_X_ROLE'] ?? '');
    $user = strtolower($_SERVER['HTTP_X_USER'] ?? '');
    $messages = [
        ['id' => 1, 'category' => 'general', 'text' => 'Üdvözöljük a közösségi üzenőfalon'],
        ['id' => 2, 'category' => 'partner', 'partner' => 'partner1', 'text' => 'Partner1 értesítése'],
        ['id' => 3, 'category' => 'organization', 'org' => 'org1', 'text' => 'Org1 híre'],
        ['id' => 4, 'category' => 'private', 'user' => 'gondozo1', 'text' => 'Személyes üzenet gondozo1-nek']
    ];
    $filtered = array_values(array_filter($messages, function ($m) use ($role, $user) {
        if ($role === 'rendszergazda') {
            return true;
        }
        if ($role === 'admin') {
            return in_array($m['category'], ['general', 'partner']);
        }
        if ($role === 'gondozo') {
            return $m['category'] === 'general' || ($m['category'] === 'private' && strtolower($m['user'] ?? '') === $user);
        }
        return false;
    }));
    header('Content-Type: application/json');
    echo json_encode(['messages' => $filtered]);
    return;
}

if (array_key_exists($path, $secure)) {
    if (!require_auth()) {
        return;
    }
    $payload = ['feature' => $secure[$path]];
    switch ($path) {
        case '/api/dashboard':
            $payload['stats'] = ['patients' => 2, 'therapies' => 1];
            break;
        case '/api/terapiak':
            $payload['therapies'] = [
                ['patient' => 'patient1', 'type' => 'Physiotherapy', 'status' => 'active']
            ];
            break;
        case '/api/gyogyszerek':
            $payload['medications'] = [
                ['name' => 'Aspirin', 'stock' => 20],
                ['name' => 'Vitamin C', 'stock' => 50]
            ];
            break;
        case '/api/ertesitesek':
            $payload['notifications'] = [
                ['text' => 'Rendszerkarbantartás', 'urgent' => false],
                ['text' => 'Új frissítés', 'urgent' => true]
            ];
            break;
        case '/api/betegek':
            $payload['patients'] = [
                ['id' => 'patient1', 'name' => 'János'],
                ['id' => 'patient2', 'name' => 'Anna']
            ];
            break;
    }
    header('Content-Type: application/json');
    echo json_encode($payload);
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

if (preg_match('#^/api/patients/([^/]+)/caregiver$#', $path, $m)) {
    if (!require_auth()) {
        return;
    }
    $role = require_role(['rendszergazda', 'admin']);
    if (!$role) {
        return;
    }
    $id = $m[1];
    $data = load_data();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $cg = $_GET['caregiver'] ?? '';
        $data['caregivers'][$id] = $cg;
        save_data($data);
        header('Content-Type: application/json');
        echo json_encode(['patient' => $id, 'caregiver' => $cg]);
        return;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        unset($data['caregivers'][$id]);
        save_data($data);
        header('Content-Type: application/json');
        echo json_encode(['patient' => $id, 'caregiver' => null]);
        return;
    }
}

if (preg_match('#^/api/users/([^/]+)/vacation$#', $path, $m)) {
    if (!require_auth()) {
        return;
    }
    $actorRole = require_role(['rendszergazda', 'admin', 'gondozo', 'beteg']);
    if (!$actorRole) {
        return;
    }
    $actorUser = strtolower($_SERVER['HTTP_X_USER'] ?? '');
    $target = strtolower($m[1]);
    if (!in_array($actorRole, ['rendszergazda', 'admin']) && $actorUser !== $target) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'forbidden']);
        return;
    }
    $on = ($_GET['on'] ?? '') === '1';
    $data = load_data();
    $data['vacation'][$target] = $on;
    save_data($data);
    header('Content-Type: application/json');
    echo json_encode(['user' => $target, 'vacation' => $on]);
    return;
}

if (preg_match('#^/api/patients/([^/]+)/chart$#', $path, $m)) {
    if (!require_auth()) {
        return;
    }
    $id = $m[1];
    $data = load_data();
    $caregiver = $data['caregivers'][$id] ?? 'gondozo1';
    $chart = [
        'patient' => $id,
        'medications' => ['Aspirin', 'Vitamin C'],
        'diseases' => ['Hypertension'],
        'therapies' => ['Physiotherapy'],
        'caregiver' => $caregiver
    ];
    header('Content-Type: application/json');
    echo json_encode($chart);
    return;
}

if ($path === '/' || $path === '') {
    header('Content-Type: text/html');
    readfile('telemedicine-html-app.html');
    return;
}

if ($path === '/patient-therapy.html') {
    header('Content-Type: text/html');
    readfile('patient-therapy.html');
    return;
}

http_response_code(404);
echo 'Not Found';


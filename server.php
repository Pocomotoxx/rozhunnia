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
    return [
        'terapiak' => [
            ['id' => 1, 'patient' => 'patient1', 'type' => 'Physiotherapy', 'status' => 'active']
        ],
        'gyogyszerek' => [
            ['id' => 1, 'name' => 'Aspirin', 'stock' => 20],
            ['id' => 2, 'name' => 'Vitamin C', 'stock' => 50]
        ],
        'ertesitesek' => [
            ['id' => 1, 'text' => 'Rendszerkarbantartás', 'urgent' => false],
            ['id' => 2, 'text' => 'Új frissítés', 'urgent' => true]
        ],
        'betegek' => [
            [
                'id' => 'patient1',
                'name' => 'János',
                'medications' => ['Aspirin', 'Vitamin C'],
                'diseases' => ['Hypertension'],
                'therapies' => ['Physiotherapy'],
                'caregiver' => 'gondozo1'
            ],
            [
                'id' => 'patient2',
                'name' => 'Anna',
                'medications' => [],
                'diseases' => [],
                'therapies' => [],
                'caregiver' => 'gondozo1'
            ]
        ],
        'caregivers' => ['patient1' => 'gondozo1'],
        'vacation' => []
    ];
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

function get_json_body() {
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function next_id($items) {
    $ids = array_map(function ($i) {
        return $i['id'] ?? 0;
    }, $items);
    return $ids ? max($ids) + 1 : 1;
}

if ($path === '/api/status') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
    return;
}

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

// Dashboard stats are calculated dynamically
if ($path === '/api/dashboard') {
    if (!require_auth()) {
        return;
    }
    $data = load_data();
    $payload = [
        'feature' => 'dashboard',
        'stats' => [
            'patients' => count($data['betegek']),
            'therapies' => count($data['terapiak']),
            'medications' => count($data['gyogyszerek']),
            'notifications' => count($data['ertesitesek'])
        ]
    ];
    header('Content-Type: application/json');
    echo json_encode($payload);
    return;
}

// Therapies CRUD
if (preg_match('#^/api/terapiak(?:/(\d+))?$#', $path, $m)) {
    if (!require_auth()) {
        return;
    }
    $data = load_data();
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        header('Content-Type: application/json');
        echo json_encode(['feature' => 'terapiak', 'therapies' => $data['terapiak']]);
        return;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $body = get_json_body();
        $body['id'] = next_id($data['terapiak']);
        $data['terapiak'][] = $body;
        save_data($data);
        header('Content-Type: application/json');
        echo json_encode(['added' => $body]);
        return;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($m[1])) {
        $id = (int)$m[1];
        $data['terapiak'] = array_values(array_filter($data['terapiak'], fn($t) => $t['id'] != $id));
        save_data($data);
        header('Content-Type: application/json');
        echo json_encode(['deleted' => $id]);
        return;
    }
}

// Medications CRUD
if (preg_match('#^/api/gyogyszerek(?:/(\d+))?$#', $path, $m)) {
    if (!require_auth()) {
        return;
    }
    $data = load_data();
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        header('Content-Type: application/json');
        echo json_encode(['feature' => 'gyogyszerek', 'medications' => $data['gyogyszerek']]);
        return;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $body = get_json_body();
        $body['id'] = next_id($data['gyogyszerek']);
        $data['gyogyszerek'][] = $body;
        save_data($data);
        header('Content-Type: application/json');
        echo json_encode(['added' => $body]);
        return;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($m[1])) {
        $id = (int)$m[1];
        $data['gyogyszerek'] = array_values(array_filter($data['gyogyszerek'], fn($t) => $t['id'] != $id));
        save_data($data);
        header('Content-Type: application/json');
        echo json_encode(['deleted' => $id]);
        return;
    }
}

// Notifications CRUD
if (preg_match('#^/api/ertesitesek(?:/(\d+))?$#', $path, $m)) {
    if (!require_auth()) {
        return;
    }
    $data = load_data();
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        header('Content-Type: application/json');
        echo json_encode(['feature' => 'ertesitesek', 'notifications' => $data['ertesitesek']]);
        return;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $body = get_json_body();
        $body['id'] = next_id($data['ertesitesek']);
        $data['ertesitesek'][] = $body;
        save_data($data);
        header('Content-Type: application/json');
        echo json_encode(['added' => $body]);
        return;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($m[1])) {
        $id = (int)$m[1];
        $data['ertesitesek'] = array_values(array_filter($data['ertesitesek'], fn($t) => $t['id'] != $id));
        save_data($data);
        header('Content-Type: application/json');
        echo json_encode(['deleted' => $id]);
        return;
    }
}

// Patients CRUD
if (preg_match('#^/api/betegek(?:/([^/]+))?$#', $path, $m)) {
    if (!require_auth()) {
        return;
    }
    $data = load_data();
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($m[1])) {
        $list = array_map(fn($p) => ['id' => $p['id'], 'name' => $p['name']], $data['betegek']);
        header('Content-Type: application/json');
        echo json_encode(['feature' => 'betegek', 'patients' => $list]);
        return;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($m[1])) {
        $body = get_json_body();
        if (!isset($body['id'])) {
            $body['id'] = 'patient' . (count($data['betegek']) + 1);
        }
        $body += ['medications' => [], 'diseases' => [], 'therapies' => [], 'caregiver' => 'gondozo1'];
        $data['betegek'][] = $body;
        save_data($data);
        header('Content-Type: application/json');
        echo json_encode(['added' => $body]);
        return;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($m[1])) {
        $id = $m[1];
        $data['betegek'] = array_values(array_filter($data['betegek'], fn($p) => $p['id'] !== $id));
        unset($data['caregivers'][$id]);
        save_data($data);
        header('Content-Type: application/json');
        echo json_encode(['deleted' => $id]);
        return;
    }
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
        foreach ($data['betegek'] as &$p) {
            if ($p['id'] === $id) {
                $p['caregiver'] = $cg;
                break;
            }
        }
        save_data($data);
        header('Content-Type: application/json');
        echo json_encode(['patient' => $id, 'caregiver' => $cg]);
        return;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        unset($data['caregivers'][$id]);
        foreach ($data['betegek'] as &$p) {
            if ($p['id'] === $id) {
                $p['caregiver'] = 'gondozo1';
                break;
            }
        }
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
    $patient = null;
    foreach ($data['betegek'] as $p) {
        if ($p['id'] === $id) {
            $patient = $p;
            break;
        }
    }
    if ($patient) {
        $caregiver = $data['caregivers'][$id] ?? ($patient['caregiver'] ?? 'gondozo1');
        $chart = [
            'patient' => $patient['id'],
            'medications' => $patient['medications'],
            'diseases' => $patient['diseases'],
            'therapies' => $patient['therapies'],
            'caregiver' => $caregiver
        ];
    } else {
        $caregiver = $data['caregivers'][$id] ?? 'gondozo1';
        $chart = [
            'patient' => $id,
            'medications' => [],
            'diseases' => [],
            'therapies' => [],
            'caregiver' => $caregiver
        ];
    }
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


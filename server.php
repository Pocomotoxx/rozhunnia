<?php
 $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

function get_db() {
    static $db = null;
    if ($db === null) {
        $db = new SQLite3(__DIR__ . '/data.sqlite');
        init_db($db);
    }
    return $db;
}

function init_db($db) {
    $db->exec('CREATE TABLE IF NOT EXISTS terapiak (id INTEGER PRIMARY KEY, patient TEXT, type TEXT, status TEXT)');
    $db->exec('CREATE TABLE IF NOT EXISTS gyogyszerek (id INTEGER PRIMARY KEY, name TEXT, stock INTEGER)');
    $db->exec('CREATE TABLE IF NOT EXISTS ertesitesek (id INTEGER PRIMARY KEY, text TEXT, urgent INTEGER)');
    $db->exec('CREATE TABLE IF NOT EXISTS betegek (id TEXT PRIMARY KEY, name TEXT, medications TEXT, diseases TEXT, therapies TEXT, caregiver TEXT)');
    $db->exec('CREATE TABLE IF NOT EXISTS caregivers (patient_id TEXT PRIMARY KEY, caregiver TEXT)');
    $db->exec('CREATE TABLE IF NOT EXISTS vacation (user TEXT PRIMARY KEY, flag INTEGER)');

    $count = $db->querySingle('SELECT COUNT(*) FROM betegek');
    if ($count == 0) {
        $db->exec("INSERT INTO betegek (id, name, medications, diseases, therapies, caregiver) VALUES
            ('patient1','János','[\"Aspirin\",\"Vitamin C\"]','[\"Hypertension\"]','[\"Physiotherapy\"]','gondozo1'),
            ('patient2','Anna','[]','[]','[]','gondozo1')");
        $db->exec("INSERT INTO terapiak (id, patient, type, status) VALUES (1,'patient1','Physiotherapy','active')");
        $db->exec("INSERT INTO gyogyszerek (id, name, stock) VALUES (1,'Aspirin',20),(2,'Vitamin C',50)");
        $db->exec("INSERT INTO ertesitesek (id, text, urgent) VALUES (1,'Rendszerkarbantartás',0),(2,'Új frissítés',1)");
        $db->exec("INSERT INTO caregivers (patient_id, caregiver) VALUES ('patient1','gondozo1')");
    }
}

function load_data() {
    $db = get_db();
    $data = [
        'terapiak' => [],
        'gyogyszerek' => [],
        'ertesitesek' => [],
        'betegek' => [],
        'caregivers' => [],
        'vacation' => []
    ];
    $res = $db->query('SELECT id, patient, type, status FROM terapiak');
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $data['terapiak'][] = $row;
    }
    $res = $db->query('SELECT id, name, stock FROM gyogyszerek');
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $data['gyogyszerek'][] = $row;
    }
    $res = $db->query('SELECT id, text, urgent FROM ertesitesek');
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $row['urgent'] = (bool)$row['urgent'];
        $data['ertesitesek'][] = $row;
    }
    $res = $db->query('SELECT id, name, medications, diseases, therapies, caregiver FROM betegek');
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $row['medications'] = $row['medications'] ? json_decode($row['medications'], true) : [];
        $row['diseases'] = $row['diseases'] ? json_decode($row['diseases'], true) : [];
        $row['therapies'] = $row['therapies'] ? json_decode($row['therapies'], true) : [];
        $data['betegek'][] = $row;
    }
    $res = $db->query('SELECT patient_id, caregiver FROM caregivers');
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $data['caregivers'][$row['patient_id']] = $row['caregiver'];
    }
    $res = $db->query('SELECT user, flag FROM vacation');
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $data['vacation'][$row['user']] = (bool)$row['flag'];
    }
    return $data;
}

function save_data($data) {
    $db = get_db();
    $db->exec('DELETE FROM terapiak');
    foreach ($data['terapiak'] as $t) {
        $stmt = $db->prepare('INSERT INTO terapiak (id, patient, type, status) VALUES (:id,:patient,:type,:status)');
        $stmt->bindValue(':id', $t['id'], SQLITE3_INTEGER);
        $stmt->bindValue(':patient', $t['patient']);
        $stmt->bindValue(':type', $t['type']);
        $stmt->bindValue(':status', $t['status']);
        $stmt->execute();
    }
    $db->exec('DELETE FROM gyogyszerek');
    foreach ($data['gyogyszerek'] as $m) {
        $stmt = $db->prepare('INSERT INTO gyogyszerek (id, name, stock) VALUES (:id,:name,:stock)');
        $stmt->bindValue(':id', $m['id'], SQLITE3_INTEGER);
        $stmt->bindValue(':name', $m['name']);
        $stmt->bindValue(':stock', $m['stock'], SQLITE3_INTEGER);
        $stmt->execute();
    }
    $db->exec('DELETE FROM ertesitesek');
    foreach ($data['ertesitesek'] as $n) {
        $stmt = $db->prepare('INSERT INTO ertesitesek (id, text, urgent) VALUES (:id,:text,:urgent)');
        $stmt->bindValue(':id', $n['id'], SQLITE3_INTEGER);
        $stmt->bindValue(':text', $n['text']);
        $stmt->bindValue(':urgent', $n['urgent'] ? 1 : 0, SQLITE3_INTEGER);
        $stmt->execute();
    }
    $db->exec('DELETE FROM betegek');
    foreach ($data['betegek'] as $p) {
        $stmt = $db->prepare('INSERT INTO betegek (id,name,medications,diseases,therapies,caregiver) VALUES (:id,:name,:med,:dis,:ther,:care)');
        $stmt->bindValue(':id', $p['id']);
        $stmt->bindValue(':name', $p['name']);
        $stmt->bindValue(':med', json_encode($p['medications']));
        $stmt->bindValue(':dis', json_encode($p['diseases']));
        $stmt->bindValue(':ther', json_encode($p['therapies']));
        $stmt->bindValue(':care', $p['caregiver']);
        $stmt->execute();
    }
    $db->exec('DELETE FROM caregivers');
    foreach ($data['caregivers'] as $pid => $cg) {
        $stmt = $db->prepare('INSERT INTO caregivers (patient_id, caregiver) VALUES (:pid,:cg)');
        $stmt->bindValue(':pid', $pid);
        $stmt->bindValue(':cg', $cg);
        $stmt->execute();
    }
    $db->exec('DELETE FROM vacation');
    foreach ($data['vacation'] as $user => $on) {
        $stmt = $db->prepare('INSERT INTO vacation (user, flag) VALUES (:u,:on)');
        $stmt->bindValue(':u', $user);
        $stmt->bindValue(':on', $on ? 1 : 0, SQLITE3_INTEGER);
        $stmt->execute();
    }
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


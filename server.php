<?php
header('Content-Type: application/json; charset=utf-8');

// Initialize database connection
try {
    $db = new PDO('sqlite:' . __DIR__ . '/data.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        taj TEXT NOT NULL,
        phone TEXT NOT NULL,
        address TEXT NOT NULL,
        doctor_names TEXT NOT NULL,
        doctor_address TEXT NOT NULL,
        doctor_phone TEXT NOT NULL
    )');
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    if (!is_array($data)) {
        invalid('Invalid JSON');
    }

    $name = $data['name'] ?? null;
    $taj = $data['taj'] ?? null;
    $phone = $data['phone'] ?? null;
    $address = $data['address'] ?? null;
    $doctorNames = $data['doctor_names'] ?? null;
    $doctorAddress = $data['doctor_address'] ?? null;
    $doctorPhone = $data['doctor_phone'] ?? null;

    if (!is_string($name) || !preg_match('/^[\p{L}\s]+$/u', $name)) {
        invalid('Name must contain only letters and spaces');
    }
    if (!is_string($taj) || !ctype_digit($taj) || strlen($taj) !== 9) {
        invalid('TAJ must be a 9-digit number');
    }
    if (!is_string($phone) || !preg_match('/^\+?\d+$/', $phone)) {
        invalid('Phone must contain only digits and may start with +');
    }
    if (!is_string($address) || $address === '') {
        invalid('Delivery address is required');
    }

    if (is_array($doctorNames)) {
        $names = [];
        foreach ($doctorNames as $n) {
            if (!is_string($n) || !preg_match('/^[\p{L}\s]+$/u', $n)) {
                invalid('Doctor names must contain only letters and spaces');
            }
            $names[] = $n;
        }
        $doctorNamesStr = implode(', ', $names);
    } elseif (is_string($doctorNames) && preg_match('/^[\p{L}\s,]+$/u', $doctorNames)) {
        $doctorNamesStr = $doctorNames;
    } else {
        invalid('Doctor names must be a string or array of names');
    }

    if (!is_string($doctorAddress) || $doctorAddress === '') {
        invalid('Doctor address is required');
    }
    if (!is_string($doctorPhone) || !preg_match('/^\+?\d+$/', $doctorPhone)) {
        invalid('Doctor phone must contain only digits and may start with +');
    }

    $stmt = $db->prepare('INSERT INTO orders (name, taj, phone, address, doctor_names, doctor_address, doctor_phone)
                          VALUES (:name, :taj, :phone, :address, :doctor_names, :doctor_address, :doctor_phone)');
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':taj', $taj, PDO::PARAM_STR);
    $stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
    $stmt->bindValue(':address', $address, PDO::PARAM_STR);
    $stmt->bindValue(':doctor_names', $doctorNamesStr, PDO::PARAM_STR);
    $stmt->bindValue(':doctor_address', $doctorAddress, PDO::PARAM_STR);
    $stmt->bindValue(':doctor_phone', $doctorPhone, PDO::PARAM_STR);
    $stmt->execute();

    echo json_encode(['id' => $db->lastInsertId()]);
    exit;
}

if ($method === 'GET') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id === null || $id === false) {
        invalid('Missing or invalid id parameter');
    }

    $stmt = $db->prepare('SELECT id, name, taj, phone, address, doctor_names, doctor_address, doctor_phone FROM orders WHERE id = :id');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
    } else {
        echo json_encode($row);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);

function invalid(string $message): void
{
    http_response_code(400);
    echo json_encode(['error' => $message]);
    exit;
}

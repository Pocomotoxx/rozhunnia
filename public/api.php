<?php
session_start();
require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/logger.php';

$pdo = get_db_connection();

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_messages':
        $stmt = $pdo->query("SELECT * FROM chat_messages ORDER BY id");
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($messages);
        break;

    case 'send_message':
        $text = trim($_POST['text']);
        if (!empty($text)) {
            $stmt = $pdo->prepare("INSERT INTO chat_messages (sender, text, time) VALUES (?, ?, ?)");
            $stmt->execute([
                $_SESSION['user']['name'],
                $text,
                date('H:i')
            ]);
            log_message("User '{$_SESSION['user']['name']}' sent a chat message.");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Message is empty']);
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>

<?php
session_start();

require_once __DIR__ . '/../src/database.php';

$pdo = get_db_connection();

// Handle login
if (isset($_POST['role'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = ?");
    $stmt->execute([$_POST['role']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user'] = $user;
    }
    header('Location: /');
    exit;
}

// Handle add therapy submission
if (isset($_POST['action']) && $_POST['action'] === 'add_therapy' && isset($_SESSION['user'])) {
    $patient = trim($_POST['patient']);
    $type = trim($_POST['type']);
    $status = trim($_POST['status']);

    if (!empty($patient) && !empty($type) && !empty($status)) {
        $stmt = $pdo->prepare("INSERT INTO therapies (patient, type, status) VALUES (?, ?, ?)");
        $stmt->execute([$patient, $type, $status]);
    }
    header('Location: /');
    exit;
}

// Handle chat message submission
if (isset($_POST['action']) && $_POST['action'] === 'chat' && isset($_SESSION['user'])) {
    $text = trim($_POST['text']);
    if (!empty($text)) {
        $stmt = $pdo->prepare("INSERT INTO chat_messages (sender, text, time) VALUES (?, ?, ?)");
        $stmt->execute([
            $_SESSION['user']['name'],
            $text,
            date('H:i')
        ]);
    }
    header('Location: /');
    exit;
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: /');
    exit;
}

// Check if user is logged in and include the appropriate view
if (isset($_SESSION['user'])) {
    // If logged in, show the main application
    require_once __DIR__ . '/../templates/main.php';
} else {
    // If not logged in, show the login page
    require_once __DIR__ . '/../templates/main.php';
}
?>

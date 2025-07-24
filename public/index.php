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

// Handle add medication submission
if (isset($_POST['action']) && $_POST['action'] === 'add_medication' && isset($_SESSION['user']) && in_array($_SESSION['user']['role'], ['admin', 'pharmacist'])) {
    $name = trim($_POST['name']);
    $info = trim($_POST['info']);
    $stock = trim($_POST['stock']);

    if (!empty($name) && !empty($info) && !empty($stock)) {
        $stmt = $pdo->prepare("INSERT INTO medications (name, info, stock) VALUES (?, ?, ?)");
        $stmt->execute([$name, $info, $stock]);
        $_SESSION['message'] = 'Gyógyszer sikeresen hozzáadva!';
    } else {
        $_SESSION['error'] = 'Minden mező kitöltése kötelező!';
    }
    header('Location: /');
    exit;
}

// Handle edit medication submission
if (isset($_POST['action']) && $_POST['action'] === 'edit_medication' && isset($_SESSION['user']) && in_array($_SESSION['user']['role'], ['admin', 'pharmacist'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $info = trim($_POST['info']);
    $stock = trim($_POST['stock']);

    if (!empty($id) && !empty($name) && !empty($info) && !empty($stock)) {
        $stmt = $pdo->prepare("UPDATE medications SET name = ?, info = ?, stock = ? WHERE id = ?");
        $stmt->execute([$name, $info, $stock, $id]);
        $_SESSION['message'] = 'Gyógyszer sikeresen módosítva!';
    } else {
        $_SESSION['error'] = 'Minden mező kitöltése kötelező!';
    }
    header('Location: /');
    exit;
}

// Handle delete medication
if (isset($_GET['action']) && $_GET['action'] === 'delete_medication' && isset($_SESSION['user']) && in_array($_SESSION['user']['role'], ['admin', 'pharmacist'])) {
    $id = $_GET['id'];
    if (!empty($id)) {
        $stmt = $pdo->prepare("DELETE FROM medications WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = 'Gyógyszer sikeresen törölve!';
    }
    header('Location: /');
    exit;
}

// Handle add patient submission
if (isset($_POST['action']) && $_POST['action'] === 'add_patient' && isset($_SESSION['user']) && in_array($_SESSION['user']['role'], ['admin', 'caregiver'])) {
    $name = trim($_POST['name']);
    $age = trim($_POST['age']);
    $diagnosis = trim($_POST['diagnosis']);

    if (!empty($name) && !empty($age) && !empty($diagnosis)) {
        $stmt = $pdo->prepare("INSERT INTO patients (name, age, diagnosis) VALUES (?, ?, ?)");
        $stmt->execute([$name, $age, $diagnosis]);
        $_SESSION['message'] = 'Beteg sikeresen hozzáadva!';
    } else {
        $_SESSION['error'] = 'Minden mező kitöltése kötelező!';
    }
    header('Location: /');
    exit;
}

// Handle edit patient submission
if (isset($_POST['action']) && $_POST['action'] === 'edit_patient' && isset($_SESSION['user']) && in_array($_SESSION['user']['role'], ['admin', 'caregiver'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $age = trim($_POST['age']);
    $diagnosis = trim($_POST['diagnosis']);

    if (!empty($id) && !empty($name) && !empty($age) && !empty($diagnosis)) {
        $stmt = $pdo->prepare("UPDATE patients SET name = ?, age = ?, diagnosis = ? WHERE id = ?");
        $stmt->execute([$name, $age, $diagnosis, $id]);
        $_SESSION['message'] = 'Beteg sikeresen módosítva!';
    } else {
        $_SESSION['error'] = 'Minden mező kitöltése kötelező!';
    }
    header('Location: /');
    exit;
}

// Handle delete patient
if (isset($_GET['action']) && $_GET['action'] === 'delete_patient' && isset($_SESSION['user']) && in_array($_SESSION['user']['role'], ['admin', 'caregiver'])) {
    $id = $_GET['id'];
    if (!empty($id)) {
        $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = 'Beteg sikeresen törölve!';
    }
    header('Location: /');
    exit;
}

// Handle edit therapy submission
if (isset($_POST['action']) && $_POST['action'] === 'edit_therapy' && isset($_SESSION['user']) && in_array($_SESSION['user']['role'], ['admin', 'caregiver'])) {
    $id = $_POST['id'];
    $patient = trim($_POST['patient']);
    $type = trim($_POST['type']);
    $status = trim($_POST['status']);

    if (!empty($id) && !empty($patient) && !empty($type) && !empty($status)) {
        $stmt = $pdo->prepare("UPDATE therapies SET patient = ?, type = ?, status = ? WHERE id = ?");
        $stmt->execute([$patient, $type, $status, $id]);
        $_SESSION['message'] = 'Terápia sikeresen módosítva!';
    } else {
        $_SESSION['error'] = 'Minden mező kitöltése kötelező!';
    }
    header('Location: /');
    exit;
}

// Handle delete therapy
if (isset($_GET['action']) && $_GET['action'] === 'delete_therapy' && isset($_SESSION['user']) && in_array($_SESSION['user']['role'], ['admin', 'caregiver'])) {
    $id = $_GET['id'];
    if (!empty($id)) {
        $stmt = $pdo->prepare("DELETE FROM therapies WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = 'Terápia sikeresen törölve!';
    }
    header('Location: /');
    exit;
}

// Handle add therapy submission
if (isset($_POST['action']) && $_POST['action'] === 'add_therapy' && isset($_SESSION['user']) && in_array($_SESSION['user']['role'], ['admin', 'caregiver'])) {
    $patient = trim($_POST['patient']);
    $type = trim($_POST['type']);
    $status = trim($_POST['status']);

    if (!empty($patient) && !empty($type) && !empty($status)) {
        $stmt = $pdo->prepare("INSERT INTO therapies (patient, type, status) VALUES (?, ?, ?)");
        $stmt->execute([$patient, $type, $status]);
        $_SESSION['message'] = 'Terápia sikeresen hozzáadva!';
    } else {
        $_SESSION['error'] = 'Minden mező kitöltése kötelező!';
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

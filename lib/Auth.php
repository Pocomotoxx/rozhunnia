<?php
class Auth {
    public static function apiKey() {
        $key = $_SERVER['HTTP_X_API_KEY'] ?? '';
        if ($key !== 'secret123') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'forbidden']);
            return false;
        }
        return true;
    }

    public static function role($allowed) {
        $role = strtolower($_SERVER['HTTP_X_ROLE'] ?? '');
        if (!in_array($role, $allowed)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'unauthorized']);
            return false;
        }
        return $role;
    }
}
?>

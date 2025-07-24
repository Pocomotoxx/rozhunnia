<?php

function get_db_connection() {
    $db_path = __DIR__ . '/../database.sqlite';
    $db_exists = file_exists($db_path);

    try {
        $pdo = new PDO('sqlite:' . $db_path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        // Handle connection error
        die("Database connection failed: " . $e->getMessage());
    }

    if (!$db_exists) {
        // Create tables if the database file is new
        $pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                role TEXT NOT NULL,
                password TEXT NOT NULL
            );

            CREATE TABLE therapies (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                patient_id INTEGER NOT NULL,
                type TEXT NOT NULL,
                status TEXT NOT NULL,
                FOREIGN KEY (patient_id) REFERENCES patients(id)
            );

            CREATE TABLE medications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                info TEXT NOT NULL,
                stock INTEGER NOT NULL
            );

            CREATE TABLE notifications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                urgent INTEGER NOT NULL,
                text TEXT NOT NULL,
                time TEXT NOT NULL
            );

            CREATE TABLE patients (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                age INTEGER NOT NULL,
                diagnosis TEXT NOT NULL,
                caregiver_id INTEGER,
                FOREIGN KEY (caregiver_id) REFERENCES users(id)
            );

            CREATE TABLE chat_messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                sender TEXT NOT NULL,
                text TEXT NOT NULL,
                time TEXT NOT NULL
            );
        ");

        // Insert mock data
        $pdo->exec("
            INSERT INTO users (name, role, password) VALUES ('Kiss Béla', 'admin', 'password');
            INSERT INTO users (name, role, password) VALUES ('Gondozó Mária', 'caregiver', 'password');
            INSERT INTO users (name, role, password) VALUES ('Dr. Patika', 'pharmacist', 'password');
            INSERT INTO users (name, role, password) VALUES ('Támogató Zrt.', 'sponsor', 'password');

            INSERT INTO patients (name, age, diagnosis, caregiver_id) VALUES ('Kovács János', 67, 'Stroke utáni rehabilitáció', 2);
            INSERT INTO patients (name, age, diagnosis, caregiver_id) VALUES ('Nagy Anna', 74, 'Csípőprotézis', 2);
            INSERT INTO patients (name, age, diagnosis) VALUES ('Tóth Mária', 80, 'Afázia');

            INSERT INTO therapies (patient_id, type, status) VALUES (1, 'Fizikoterápia', 'active');
            INSERT INTO therapies (patient, type, status) VALUES ('Nagy Anna', 'Gyógytorna', 'completed');
            INSERT INTO therapies (patient, type, status) VALUES ('Tóth Mária', 'Beszédterápia', 'pending');

            INSERT INTO medications (name, info, stock) VALUES ('Algopyrin', 'Fájdalomcsillapító', 10);
            INSERT INTO medications (name, info, stock) VALUES ('No-Spa', 'Görcsoldó', 5);
            INSERT INTO medications (name, info, stock) VALUES ('C-vitamin', 'Immunerősítő', 20);

            INSERT INTO notifications (urgent, text, time) VALUES (1, 'Gyógyszer beadás esedékes - Kovács János', '10:30');
            INSERT INTO notifications (urgent, text, time) VALUES (0, 'Terápiás esemény befejezve - Nagy Anna', '09:15');

            INSERT INTO patients (name, age, diagnosis) VALUES ('Kovács János', 67, 'Stroke utáni rehabilitáció');
            INSERT INTO patients (name, age, diagnosis) VALUES ('Nagy Anna', 74, 'Csípőprotézis');
            INSERT INTO patients (name, age, diagnosis) VALUES ('Tóth Mária', 80, 'Afázia');

            INSERT INTO chat_messages (sender, text, time) VALUES ('Dr. Szabó Péter', 'Jó reggelt! Hogyan érzi magát Kovács János ma?', '08:45');
            INSERT INTO chat_messages (sender, text, time) VALUES ('Gondozó Maria', 'Jó reggelt! Jól van, a gyógyszereket rendesen szedi.', '08:50');
        ");
    }

    return $pdo;
}
?>

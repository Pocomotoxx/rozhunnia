<?php
session_start();
require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/logger.php';

$pdo = get_db_connection();

if (!isset($_SESSION['user'])) {
    header('Location: /');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: /');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$_GET['id']]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    header('Location: /');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM therapies WHERE patient_id = ?");
$stmt->execute([$_GET['id']]);
$therapies = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM documents WHERE patient_id = ?");
$stmt->execute([$_GET['id']]);
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($patient['name']); ?> - Telemedicina Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-8"><?php echo htmlspecialchars($patient['name']); ?> adatlapja</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border">
                <h3 class="text-lg font-semibold mb-4">Alapvető adatok</h3>
                <p><strong>Életkor:</strong> <?php echo htmlspecialchars($patient['age']); ?> év</p>
                <p><strong>Diagnózis:</strong> <?php echo htmlspecialchars($patient['diagnosis']); ?></p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border">
                <h3 class="text-lg font-semibold mb-4">Terápiák</h3>
                <div class="space-y-3">
                    <?php foreach ($therapies as $therapy): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium"><?php echo htmlspecialchars($therapy['type']); ?></p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-medium status-<?php echo htmlspecialchars($therapy['status']); ?>">
                                <?php echo htmlspecialchars(ucfirst($therapy['status'])); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border mt-8">
            <h3 class="text-lg font-semibold mb-4">Dokumentumok</h3>
            <div class="space-y-3">
                <?php foreach ($documents as $document): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <a href="/uploads/<?php echo htmlspecialchars($document['filename']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800"><?php echo htmlspecialchars($document['filename']); ?></a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mt-8">
            <a href="/" class="text-blue-600 hover:text-blue-800">&larr; Vissza a főoldalra</a>
        </div>
    </div>
</body>
</html>

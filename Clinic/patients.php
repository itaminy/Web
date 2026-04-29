<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$config_file = '/home/u82382/www/Web/db_config.php';
if (!file_exists($config_file)) {
    die('Config error');
}
require_once $config_file;

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('DB error: ' . $e->getMessage());
}

$stmt = $pdo->query("SELECT * FROM patients ORDER BY id DESC");
$PATIENTS = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Пациенты';
include __DIR__ . '/includes/admin-header.php';
?>

<div class="card">
    <h2>База пациентов</h2>
    <p>Всего записей: <?= count($PATIENTS) ?></p>
    
    <table border="1" cellpadding="8" style="width:100%; border-collapse:collapse;">
        <thead>
            <tr>
                <th>ID</th>
                <th>ФИО</th>
                <th>Дата рождения</th>
                <th>Пол</th>
                <th>Телефон</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($PATIENTS as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= $p['birth'] ?></td>
                <td><?= $p['gender'] ?></td>
                <td><?= $p['phone'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>

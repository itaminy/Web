<?php
session_start();

$config_file = '/home/u82382/www/Web/db_config.php';
if (!file_exists($config_file)) {
    die('Ошибка конфигурации БД');
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
    die('Ошибка подключения к БД');
}

$stmt = $pdo->query("SELECT * FROM patients ORDER BY id DESC");
$PATIENTS = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Пациенты';
include __DIR__ . '/includes/admin-header.php';
?>

<div class="card reveal">
    <div class="card-head">
        <h2>База пациентов</h2>
        <div class="card-sub">Всего записей: <?= count($PATIENTS) ?></div>
    </div>

    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ФИО</th>
                    <th>Дата рождения</th>
                    <th>Пол</th>
                    <th>Телефон</th>
                    <th>Полис</th>
                    <th>Адрес</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($PATIENTS as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                    <td><?= date('d.m.Y', strtotime($p['birth'])) ?></td>
                    <td><?= $p['gender'] === 'муж.' ? '👨 Мужской' : '👩 Женский' ?></td>
                    <td><?= htmlspecialchars($p['phone']) ?></td>
                    <td><?= htmlspecialchars($p['policy']) ?></td>
                    <td><?= htmlspecialchars($p['address']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>

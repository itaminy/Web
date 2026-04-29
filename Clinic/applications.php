<?php
session_start();

// Подключение к БД
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

// Получаем заявки из БД
$stmt = $pdo->query("
    SELECT a.*, d.name as doctor_name 
    FROM applications a
    LEFT JOIN doctors d ON a.doctor_id = d.id
    ORDER BY a.id DESC
");
$APPLICATIONS = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Функция для статуса
function status_label($status) {
    return match($status) {
        'new' => 'Новая',
        'approved' => 'Одобрена',
        'rejected' => 'Отклонена',
        default => $status
    };
}

$page_title = 'Заявки';
include __DIR__ . '/includes/admin-header.php';
?>

<div class="card reveal">
    <div class="card-head">
        <div>
            <h2>Заявки с сайта</h2>
            <div class="card-sub">Всего заявок: <?= count($APPLICATIONS) ?></div>
        </div>
    </div>

    <div class="toolbar">
        <div class="search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" placeholder="Поиск по ФИО, телефону, симптомам..." data-search-target="#apps-table">
        </div>
        <select data-filter-target="#apps-table" data-filter-column="status">
            <option value="">Все статусы</option>
            <option value="new">Новая</option>
            <option value="approved">Одобрена</option>
            <option value="rejected">Отклонена</option>
        </select>
    </div>

    <div class="table-wrap">
        <table class="data" id="apps-table">
            <thead>
                <tr>
                    <th class="num-col">#</th>
                    <th>Желаемая дата</th>
                    <th>ФИО</th>
                    <th>Контакты</th>
                    <th>Желаемый врач</th>
                    <th>Симптомы</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($APPLICATIONS as $a): 
                    $cls = match($a['status']) {
                        'approved' => 'badge-success',
                        'rejected' => 'badge-danger',
                        default => 'badge-info'
                    };
                    $date_display = $a['desired_date'] ? date('d.m.Y', strtotime($a['desired_date'])) : '—';
                ?>
                <tr>
                    <td class="num-col"><?= (int)$a['id'] ?></td>
                    <td><?= htmlspecialchars($date_display) ?></td>
                    <td style="font-weight: 600;"><?= htmlspecialchars($a['name']) ?></td>
                    <td>
                        <div><?= htmlspecialchars($a['phone']) ?></div>
                        <div style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($a['email'] ?? '—') ?></div>
                    </td>
                    <td><?= htmlspecialchars($a['doctor_name'] ?? 'Не выбран') ?></td>
                    <td style="color: var(--text-muted); font-size: 13px; max-width: 280px;"><?= htmlspecialchars($a['symptoms']) ?></td>
                    <td data-col="status" data-value="<?= htmlspecialchars($a['status']) ?>">
                        <span class="badge <?= $cls ?>"><span class="dot"></span><?= status_label($a['status']) ?></span>
                    </td>
                    <td>
                        <div class="row-actions">
                            <button class="icon-btn" title="Принять в работу" onclick="changeStatus(<?= $a['id'] ?>, 'approved')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </button>
                            <button class="icon-btn" title="Отклонить" onclick="changeStatus(<?= $a['id'] ?>, 'rejected')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function changeStatus(id, status) {
    if (confirm('Изменить статус заявки?')) {
        fetch('/Web/Clinic/api.php?action=update_application_status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, status: status })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Ошибка: ' + data.error);
            }
        })
        .catch(err => alert('Ошибка: ' + err.message));
    }
}
</script>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>

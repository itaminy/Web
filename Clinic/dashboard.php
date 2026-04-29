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

// Статистика из БД
$doctors_count = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$patients_count = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$diseases_count = $pdo->query("SELECT COUNT(*) FROM diseases")->fetchColumn();
$visits_total = $pdo->query("SELECT COUNT(*) FROM visits")->fetchColumn();
$visits_today = $pdo->query("SELECT COUNT(*) FROM visits WHERE DATE(visit_date) = CURDATE()")->fetchColumn();
$apps_new = $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'new'")->fetchColumn();

$STATS = [
    'doctors' => $doctors_count,
    'patients' => $patients_count,
    'diseases' => $diseases_count,
    'visits_total' => $visits_total,
    'visits_today' => $visits_today,
    'apps_new' => $apps_new,
];

// Приёмы по врачам для графика
$VISITS_BY_DOCTOR = $pdo->query("
    SELECT d.name, COUNT(v.id) as count 
    FROM visits v 
    JOIN doctors d ON v.doctor_id = d.id 
    GROUP BY d.id 
    ORDER BY count DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Приёмы по болезням для графика
$VISITS_BY_DISEASE = $pdo->query("
    SELECT dis.name, COUNT(v.id) as count 
    FROM visits v 
    JOIN diseases dis ON v.disease_id = dis.id 
    GROUP BY dis.id 
    ORDER BY count DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Последние приёмы
$recent_visits = $pdo->query("
    SELECT v.*, p.name as patient_name, d.name as doctor_name, dis.name as disease_name
    FROM visits v
    LEFT JOIN patients p ON v.patient_id = p.id
    LEFT JOIN doctors d ON v.doctor_id = d.id
    LEFT JOIN diseases dis ON v.disease_id = dis.id
    ORDER BY v.visit_date DESC
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

$max_doctor = max(array_column($VISITS_BY_DOCTOR, 'count')) ?: 1;
$colors = ['#14b8a6', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444', '#10b981'];
$donut_data = [];
foreach ($VISITS_BY_DISEASE as $i => $d) {
    if ($d['count'] > 0) {
        $donut_data[] = ['value' => $d['count'], 'color' => $colors[$i % count($colors)], 'name' => $d['name']];
    }
}

function status_label($s) {
    return match($s) {
        'scheduled' => 'Запланирован',
        'completed' => 'Завершён',
        'cancelled' => 'Отменён',
        default => $s
    };
}

$page_title = 'Панель управления';
include __DIR__ . '/includes/admin-header.php';
?>

<div class="kpi-grid reveal-stagger">
    <div class="kpi kpi-warning">
        <div class="kpi-head">
            <span class="kpi-label">Новых заявок</span>
            <div class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
        </div>
        <div class="kpi-value" data-count="<?= (int)$STATS['apps_new'] ?>">0</div>
        <div class="kpi-sub">Требуют обработки</div>
    </div>
    <div class="kpi">
        <div class="kpi-head">
            <span class="kpi-label">Приёмов сегодня</span>
            <div class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
        </div>
        <div class="kpi-value" data-count="<?= (int)$STATS['visits_today'] ?>">0</div>
        <div class="kpi-sub">Всего: <?= (int)$STATS['visits_total'] ?></div>
    </div>
    <div class="kpi">
        <div class="kpi-head">
            <span class="kpi-label">Пациентов</span>
            <div class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
        </div>
        <div class="kpi-value" data-count="<?= (int)$STATS['patients'] ?>">0</div>
        <div class="kpi-sub">Зарегистрировано в системе</div>
    </div>
    <div class="kpi kpi-accent">
        <div class="kpi-head">
            <span class="kpi-label">Врачей</span>
            <div class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7Z"/></svg></div>
        </div>
        <div class="kpi-value" data-count="<?= (int)$STATS['doctors'] ?>">0</div>
        <div class="kpi-sub">В штате клиники</div>
    </div>
</div>

<div class="charts-row">
    <div class="card reveal">
        <div class="card-head">
            <div>
                <h2>Нагрузка по врачам</h2>
                <div class="card-sub">Количество приёмов за месяц</div>
            </div>
        </div>
        <div class="bars">
            <?php foreach ($VISITS_BY_DOCTOR as $r): $pct = round($r['count'] / $max_doctor * 100); ?>
            <div class="bar-row">
                <span class="label"><?= htmlspecialchars($r['name']) ?></span>
                <div class="track"><div class="fill" data-width="<?= $pct ?>%"></div></div>
                <span class="val"><?= (int)$r['count'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card reveal">
        <div class="card-head">
            <div>
                <h2>Структура заболеваний</h2>
                <div class="card-sub">Распределение приёмов по диагнозам</div>
            </div>
        </div>
        <div class="donut-wrap">
            <svg class="donut" viewBox="0 0 200 200" data-values='<?= htmlspecialchars(json_encode($donut_data), ENT_QUOTES) ?>'></svg>
            <div class="donut-legend">
                <?php foreach ($donut_data as $d): ?>
                <div class="item">
                    <span class="swatch" style="background: <?= htmlspecialchars($d['color']) ?>;"></span>
                    <span class="name"><?= htmlspecialchars($d['name']) ?></span>
                    <span class="num"><?= (int)$d['value'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="card reveal">
    <div class="card-head">
        <div>
            <h2>Недавние приёмы</h2>
            <div class="card-sub">Последние записи в журнале</div>
        </div>
        <a href="visits.php" class="btn btn-ghost" style="padding: 8px 16px;">Открыть журнал →</a>
    </div>
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Пациент</th>
                    <th>Врач</th>
                    <th>Диагноз</th>
                    <th>Статус</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_visits as $v):
                    $cls = match($v['status']) {
                        'completed' => 'badge-success',
                        'cancelled' => 'badge-danger',
                        default => 'badge-info'
                    };
                ?>
                <tr>
                    <td><?= date('d.m.Y H:i', strtotime($v['visit_date'])) ?></td>
                    <td><?= htmlspecialchars($v['patient_name'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($v['doctor_name'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($v['disease_name'] ?? '—') ?></td>
                    <td><span class="badge <?= $cls ?>"><?= status_label($v['status']) ?></span></table>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>

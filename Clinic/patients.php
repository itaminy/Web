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

// Получаем пациентов из БД
$stmt = $pdo->query("SELECT * FROM patients ORDER BY id DESC");
$PATIENTS = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Пациенты';
include __DIR__ . '/includes/admin-header.php';
?>

<div class="card reveal">
    <div class="card-head">
        <div>
            <h2>База пациентов</h2>
            <div class="card-sub">Всего записей: <?= count($PATIENTS) ?></div>
        </div>
        <button class="btn btn-primary" style="padding: 10px 18px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Добавить пациента
        </button>
    </div>

    <div class="toolbar">
        <div class="search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" placeholder="Поиск по ФИО, телефону, полису..." data-search-target="#patients-table">
        </div>
        <select data-filter-target="#patients-table" data-filter-column="gender">
            <option value="">Любой пол</option>
            <option value="муж.">Мужчины</option>
            <option value="жен.">Женщины</option>
        </select>
    </div>

    <div class="table-wrap">
        <table class="data" id="patients-table">
            <thead>
                <tr>
                    <th class="num-col">#</th>
                    <th>ФИО</th>
                    <th>Дата рождения</th>
                    <th>Пол</th>
                    <th>Телефон</th>
                    <th>Полис</th>
                    <th>Адрес</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($PATIENTS as $p):
                    $initials = mb_substr($p['name'], 0, 1) . (preg_match('/\s(\S)/u', $p['name'], $m) ? $m[1] : '');
                ?>
                <tr>
                    <td class="num-col"><?= (int)$p['id'] ?></td>
                    <td>
                        <div class="flex gap-12" style="align-items: center;">
                            <div class="avatar" style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); color: #fff; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; justify-content: center;"><?= htmlspecialchars(mb_strtoupper($initials)) ?></div>
                            <span style="font-weight: 600;"><?= htmlspecialchars($p['name']) ?></span>
                        </div>
                    </td>
                    <td><?= date('d.m.Y', strtotime($p['birth'])) ?></td>
                    <td data-col="gender" data-value="<?= htmlspecialchars($p['gender']) ?>">
                        <span class="badge <?= $p['gender'] === 'муж.' ? 'badge-info' : 'badge-primary' ?>"><?= htmlspecialchars($p['gender']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($p['phone']) ?></td>
                    <td style="font-family: monospace; font-size: 12.5px;"><?= htmlspecialchars($p['policy']) ?></td>
                    <td style="color: var(--text-muted); font-size: 13px;"><?= htmlspecialchars($p['address']) ?></td>
                    <td>
                        <div class="row-actions">
                            <button class="icon-btn" title="Карта пациента">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </button>
                            <button class="icon-btn" title="Редактировать">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                            </button>
                            <button class="icon-btn danger" title="Удалить" data-confirm="Удалить запись пациента?">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L5 6"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>

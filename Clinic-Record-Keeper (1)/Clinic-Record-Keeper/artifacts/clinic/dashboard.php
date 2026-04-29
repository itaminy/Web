<?php
require __DIR__ . '/includes/sample-data.php';
$page_title = 'Панель управления';
include __DIR__ . '/includes/admin-header.php';

$max_doctor = max(array_column($VISITS_BY_DOCTOR, 'count')) ?: 1;
$colors = ['#14b8a6', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444', '#10b981'];
$donut_data = [];
foreach ($VISITS_BY_DISEASE as $i => $d) {
    if ($d['count'] > 0) $donut_data[] = ['value' => $d['count'], 'color' => $colors[$i % count($colors)], 'name' => $d['name']];
}
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
                <?php foreach (array_slice($VISITS, 0, 6) as $v):
                    $cls = $v['status'] === 'completed' ? 'badge-success' : ($v['status'] === 'cancelled' ? 'badge-danger' : 'badge-info');
                ?>
                <tr>
                    <td><?= htmlspecialchars($v['date']) ?></td>
                    <td><?= htmlspecialchars($v['patient']) ?></td>
                    <td><?= htmlspecialchars($v['doctor']) ?></td>
                    <td><?= htmlspecialchars($v['disease']) ?></td>
                    <td><span class="badge <?= $cls ?>"><span class="dot"></span><?= status_label($v['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>

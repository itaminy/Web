<?php
require __DIR__ . '/includes/sample-data.php';
$page_title = 'Виды болезней';
include __DIR__ . '/includes/admin-header.php';
?>

<div class="card reveal">
    <div class="card-head">
        <div>
            <h2>Справочник заболеваний</h2>
            <div class="card-sub">Всего нозологий: <?= count($DISEASES) ?></div>
        </div>
        <button class="btn btn-primary" style="padding: 10px 18px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Добавить болезнь
        </button>
    </div>

    <div class="toolbar">
        <div class="search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" placeholder="Поиск по названию, коду МКБ..." data-search-target="#diseases-table">
        </div>
        <select data-filter-target="#diseases-table" data-filter-column="severity">
            <option value="">Все степени</option>
            <option value="mild">Лёгкая</option>
            <option value="moderate">Средняя</option>
            <option value="severe">Тяжёлая</option>
        </select>
    </div>

    <div class="table-wrap">
        <table class="data" id="diseases-table">
            <thead>
                <tr>
                    <th class="num-col">#</th>
                    <th>Название</th>
                    <th>Код МКБ-10</th>
                    <th>Описание</th>
                    <th>Степень тяжести</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($DISEASES as $d):
                    $sev = $d['severity'];
                    $sev_class = $sev === 'severe' ? 'badge-danger' : ($sev === 'moderate' ? 'badge-warning' : 'badge-success');
                ?>
                <tr>
                    <td class="num-col"><?= (int)$d['id'] ?></td>
                    <td style="font-weight: 600;"><?= htmlspecialchars($d['name']) ?></td>
                    <td style="font-family: monospace; color: var(--accent);"><?= htmlspecialchars($d['icd10']) ?></td>
                    <td style="color: var(--text-muted); font-size: 13px; max-width: 360px;"><?= htmlspecialchars($d['description']) ?></td>
                    <td data-col="severity" data-value="<?= htmlspecialchars($sev) ?>">
                        <span class="badge <?= $sev_class ?>"><span class="dot"></span><?= severity_label($sev) ?></span>
                    </td>
                    <td>
                        <div class="row-actions">
                            <button class="icon-btn" title="Редактировать">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                            </button>
                            <button class="icon-btn danger" title="Удалить" data-confirm="Удалить эту болезнь из справочника?">
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

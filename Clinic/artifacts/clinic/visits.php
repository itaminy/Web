<?php
require __DIR__ . '/includes/sample-data.php';
$page_title = 'Журнал приёмов';
include __DIR__ . '/includes/admin-header.php';
?>

<div class="card reveal">
    <div class="card-head">
        <div>
            <h2>Журнал приёмов</h2>
            <div class="card-sub">Всего записей: <?= count($VISITS) ?></div>
        </div>
        <button class="btn btn-primary" style="padding: 10px 18px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Новый приём
        </button>
    </div>

    <div class="toolbar">
        <div class="search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" placeholder="Поиск по пациенту, врачу, диагнозу..." data-search-target="#visits-table">
        </div>
        <select data-filter-target="#visits-table" data-filter-column="status">
            <option value="">Все статусы</option>
            <option value="completed">Завершён</option>
            <option value="scheduled">Запланирован</option>
            <option value="cancelled">Отменён</option>
        </select>
    </div>

    <div class="table-wrap">
        <table class="data" id="visits-table">
            <thead>
                <tr>
                    <th class="num-col">#</th>
                    <th>Дата / время</th>
                    <th>Пациент</th>
                    <th>Врач</th>
                    <th>Диагноз</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($VISITS as $v):
                    $cls = $v['status'] === 'completed' ? 'badge-success' : ($v['status'] === 'cancelled' ? 'badge-danger' : 'badge-info');
                ?>
                <tr>
                    <td class="num-col"><?= (int)$v['id'] ?></td>
                    <td>
                        <div style="font-weight: 600;"><?= date('d.m.Y', strtotime($v['date'])) ?></div>
                        <div style="font-size: 12px; color: var(--text-muted);"><?= date('H:i', strtotime($v['date'])) ?></div>
                    </td>
                    <td><?= htmlspecialchars($v['patient']) ?></td>
                    <td><?= htmlspecialchars($v['doctor']) ?></td>
                    <td><span class="badge badge-primary"><?= htmlspecialchars($v['disease']) ?></span></td>
                    <td data-col="status" data-value="<?= htmlspecialchars($v['status']) ?>">
                        <span class="badge <?= $cls ?>"><span class="dot"></span><?= status_label($v['status']) ?></span>
                    </td>
                    <td>
                        <div class="row-actions">
                            <button class="icon-btn" title="Детали">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            <button class="icon-btn" title="Редактировать">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                            </button>
                            <button class="icon-btn danger" title="Удалить" data-confirm="Удалить запись о приёме?">
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

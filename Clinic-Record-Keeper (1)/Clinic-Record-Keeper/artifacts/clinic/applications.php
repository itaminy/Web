<?php
require __DIR__ . '/includes/sample-data.php';
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
                    $cls = $a['status'] === 'approved' ? 'badge-success'
                         : ($a['status'] === 'rejected' ? 'badge-danger'
                         : 'badge-info');
                    $date_display = ($a['date'] && $a['date'] !== '—') ? date('d.m.Y', strtotime($a['date'])) : '—';
                ?>
                <tr>
                    <td class="num-col"><?= (int)$a['id'] ?></td>
                    <td>
                        <div style="font-weight: 600;"><?= htmlspecialchars($date_display) ?></div>
                    </td>
                    <td style="font-weight: 600;"><?= htmlspecialchars($a['name']) ?></td>
                    <td>
                        <div><?= htmlspecialchars($a['phone']) ?></div>
                        <div style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($a['email']) ?></div>
                    </td>
                    <td><?= htmlspecialchars($a['doctor']) ?></td>
                    <td style="color: var(--text-muted); font-size: 13px; max-width: 280px;"><?= htmlspecialchars($a['symptoms']) ?></td>
                    <td data-col="status" data-value="<?= htmlspecialchars($a['status']) ?>">
                        <span class="badge <?= $cls ?>"><span class="dot"></span><?= status_label($a['status']) ?></span>
                    </td>
                    <td>
                        <div class="row-actions">
                            <button class="icon-btn" title="Принять в работу">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </button>
                            <button class="icon-btn" title="Позвонить">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </button>
                            <button class="icon-btn danger" title="Отклонить" data-confirm="Отклонить заявку?">
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

<?php include __DIR__ . '/includes/admin-footer.php'; ?>

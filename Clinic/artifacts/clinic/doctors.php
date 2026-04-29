<?php
require __DIR__ . '/includes/sample-data.php';
$page_title = 'Врачи';
include __DIR__ . '/includes/admin-header.php';
?>

<div class="card reveal">
    <div class="card-head">
        <div>
            <h2>Список врачей</h2>
            <div class="card-sub">Всего специалистов: <?= count($DOCTORS) ?></div>
        </div>
        <button class="btn btn-primary" style="padding: 10px 18px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Добавить врача
        </button>
    </div>

    <div class="toolbar">
        <div class="search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" placeholder="Поиск по ФИО, специальности..." data-search-target="#doctors-table">
        </div>
        <select data-filter-target="#doctors-table" data-filter-column="specialty">
            <option value="">Все специальности</option>
            <?php foreach (array_unique(array_column($DOCTORS, 'specialty')) as $s): ?>
            <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="table-wrap">
        <table class="data" id="doctors-table">
            <thead>
                <tr>
                    <th class="num-col">#</th>
                    <th>ФИО</th>
                    <th>Специальность</th>
                    <th>Опыт</th>
                    <th>Каб.</th>
                    <th>Контакты</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($DOCTORS as $d): ?>
                <tr>
                    <td class="num-col"><?= (int)$d['id'] ?></td>
                    <td>
                        <div class="flex gap-12" style="align-items: center;">
                            <img src="<?= htmlspecialchars($d['photo']) ?>" alt="" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover;">
                            <div>
                                <div style="font-weight: 600;"><?= htmlspecialchars($d['name']) ?></div>
                                <div style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($d['email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td data-col="specialty" data-value="<?= htmlspecialchars($d['specialty']) ?>">
                        <span class="badge badge-primary"><?= htmlspecialchars($d['specialty']) ?></span>
                    </td>
                    <td><?= (int)$d['experience'] ?> лет</td>
                    <td><?= htmlspecialchars($d['room']) ?></td>
                    <td><?= htmlspecialchars($d['phone']) ?></td>
                    <td>
                        <div class="row-actions">
                            <button class="icon-btn" title="Просмотр">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            <button class="icon-btn" title="Редактировать">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                            </button>
                            <button class="icon-btn danger" title="Удалить" data-confirm="Удалить этого врача?">
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

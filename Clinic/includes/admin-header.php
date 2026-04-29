<?php
$current = basename($_SERVER['PHP_SELF']);
$page_title = $page_title ?? 'Панель управления';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> · Поликлиника «Здоровье»</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-logo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
            <div>
                <div class="admin-logo-title">Поликлиника</div>
                <div class="admin-logo-sub">Панель управления</div>
            </div>
        </div>

        <nav class="admin-nav">
            <a href="dashboard.php" class="<?= $current === 'dashboard.php' ? 'is-active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                Панель управления
            </a>
            <a href="doctors.php" class="<?= $current === 'doctors.php' ? 'is-active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7Z"/></svg>
                Врачи
            </a>
            <a href="patients.php" class="<?= $current === 'patients.php' ? 'is-active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Пациенты
            </a>
            <a href="diseases.php" class="<?= $current === 'diseases.php' ? 'is-active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                Виды болезней
            </a>
            <a href="visits.php" class="<?= $current === 'visits.php' ? 'is-active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Журнал приёмов
            </a>
            <a href="applications.php" class="<?= $current === 'applications.php' ? 'is-active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Заявки
            </a>
        </nav>

        <a href="index.php" class="back-to-site">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Вернуться на сайт
        </a>
    </aside>

    <button class="admin-burger" id="adminBurger" aria-label="Меню">
        <span></span><span></span><span></span>
    </button>

    <main class="admin-main">
        <header class="admin-topbar">
            <h1 class="admin-page-title"><?= htmlspecialchars($page_title) ?></h1>
            <div class="admin-user">
                <div class="avatar">АД</div>
                <div class="admin-user-info">
                    <div class="admin-user-name">Администратор</div>
                    <div class="admin-user-role">Регистратура</div>
                </div>
            </div>
        </header>
        <div class="admin-content">

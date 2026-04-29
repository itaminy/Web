<?php
$current = basename($_SERVER['PHP_SELF']);
$page_title = $page_title ?? 'Поликлиника «Здоровье»';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="public-body">
    <header class="site-header" id="siteHeader">
        <div class="container header-inner">
            <a href="index.php" class="logo">
                <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
                <span>Поликлиника <strong>«Здоровье»</strong></span>
            </a>

            <nav class="main-nav" id="mainNav">
                <a href="index.php" class="<?= $current === 'index.php' ? 'is-active' : '' ?>">Главная</a>
                <a href="index.php#services">Услуги</a>
                <a href="index.php#doctors">Врачи</a>
                <a href="index.php#about">О клинике</a>
                <a href="index.php#contact">Контакты</a>
                <a href="apply.php" class="<?= $current === 'apply.php' ? 'is-active' : '' ?>">Подать заявку</a>
                <a href="dashboard.php" class="nav-staff">Вход для персонала</a>
            </nav>

            <button class="burger" id="burgerBtn" aria-label="Меню">
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>

    <main>

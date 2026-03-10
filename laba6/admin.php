<?php
session_start();

// Подключение к БД
$config_file = '/home/u82382/config/laba3/db_config.php';
if (!file_exists($config_file)) {
    die('Ошибка конфигурации сервера');
}
require_once $config_file;

// HTTP-авторизация
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Требуется авторизация';
    exit;
}

// Проверка авторизации через БД
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin || !password_verify($_SERVER['PHP_AUTH_PW'], $admin['password_hash'])) {
        header('WWW-Authenticate: Basic realm="Admin Panel"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Неверный логин или пароль';
        exit;
    }
    
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    
} catch (PDOException $e) {
    die('Ошибка подключения к базе данных');
}

// Обработка действий
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            if ($_POST['action'] === 'delete' && isset($_POST['user_id'])) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$_POST['user_id']]);
                $message = "Пользователь успешно удален";
                $message_type = 'success';
            }
            
            if ($_POST['action'] === 'update' && isset($_POST['user_id'])) {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("
                    UPDATE users SET 
                        full_name = ?,
                        phone = ?,
                        email = ?,
                        birth_date = ?,
                        gender = ?,
                        biography = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $_POST['full_name'],
                    $_POST['phone'],
                    $_POST['email'],
                    $_POST['birth_date'],
                    $_POST['gender'],
                    $_POST['biography'],
                    $_POST['user_id']
                ]);
                
                if (isset($_POST['languages']) && is_array($_POST['languages'])) {
                    $pdo->prepare("DELETE FROM user_languages WHERE user_id = ?")->execute([$_POST['user_id']]);
                    
                    $lang_stmt = $pdo->prepare("
                        INSERT INTO user_languages (user_id, language_id) 
                        SELECT ?, id FROM programming_languages WHERE name = ?
                    ");
                    
                    foreach ($_POST['languages'] as $lang) {
                        $lang_stmt->execute([$_POST['user_id'], $lang]);
                    }
                }
                
                $pdo->commit();
                $message = "Данные пользователя обновлены";
                $message_type = 'success';
            }
            
        } catch (PDOException $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            $message = "Ошибка: " . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Получение данных
try {
    $stats = $pdo->query("
        SELECT 
            pl.name,
            COUNT(ul.user_id) as user_count
        FROM programming_languages pl
        LEFT JOIN user_languages ul ON pl.id = ul.language_id
        GROUP BY pl.id
        ORDER BY user_count DESC, pl.name
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $total_with_languages = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM user_languages")->fetchColumn();
    
    $users = $pdo->query("
        SELECT 
            u.*,
            GROUP_CONCAT(pl.name ORDER BY pl.name SEPARATOR ', ') as languages
        FROM users u
        LEFT JOIN user_languages ul ON u.id = ul.user_id
        LEFT JOIN programming_languages pl ON ul.language_id = pl.id
        GROUP BY u.id
        ORDER BY u.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die('Ошибка получения данных');
}

$edit_user = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($edit_user) {
        $lang_stmt = $pdo->prepare("
            SELECT pl.name FROM programming_languages pl
            JOIN user_languages ul ON pl.id = ul.language_id
            WHERE ul.user_id = ?
        ");
        $lang_stmt->execute([$_GET['edit']]);
        $edit_user['languages'] = $lang_stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

$all_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 
                 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель | Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* СБРОС И БАЗОВЫЕ СТИЛИ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }
        
        /* ОСНОВНОЙ КОНТЕЙНЕР */
        .dashboard {
            max-width: 1440px;
            margin: 0 auto;
            padding: 30px;
        }
        
        /* ХЕДЕР */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .header-left h1 {
            font-size: 28px;
            font-weight: 600;
            color: #1a2639;
            margin-bottom: 5px;
        }
        
        .header-left p {
            color: #5a6a7e;
            font-size: 14px;
        }
        
        .admin-badge {
            background: white;
            padding: 10px 20px;
            border-radius: 40px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .admin-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3a7bd5, #3a6073);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
        }
        
        .admin-info {
            line-height: 1.4;
        }
        
        .admin-name {
            font-weight: 600;
            color: #1a2639;
        }
        
        .admin-role {
            font-size: 12px;
            color: #5a6a7e;
        }
        
        /* СТАТИСТИКА */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .stat-icon.blue { background: #e8f0fe; color: #3a7bd5; }
        .stat-icon.green { background: #e3f7ec; color: #27ae60; }
        .stat-icon.purple { background: #f0e6ff; color: #9b59b6; }
        .stat-icon.orange { background: #fff0e0; color: #e67e22; }
        
        .stat-content {
            text-align: right;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #1a2639;
            line-height: 1.2;
        }
        
        .stat-label {
            color: #5a6a7e;
            font-size: 14px;
        }
        
        /* ЯЗЫКОВАЯ СТАТИСТИКА */
        .languages-panel {
            background: white;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        
        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .panel-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: #1a2639;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .panel-header span {
            background: #f0f2f5;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            color: #5a6a7e;
        }
        
        .language-bars {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .language-bar-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .lang-name {
            width: 100px;
            font-size: 14px;
            font-weight: 500;
            color: #1a2639;
        }
        
        .bar-container {
            flex: 1;
            height: 8px;
            background: #eef2f6;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #3a7bd5, #6c5ce7);
            border-radius: 4px;
            transition: width 0.3s;
        }
        
        .lang-count {
            min-width: 40px;
            font-size: 14px;
            font-weight: 600;
            color: #1a2639;
        }
        
        /* СООБЩЕНИЯ */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #155724;
        }
        
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #721c24;
        }
        
        .alert-icon {
            font-size: 20px;
        }
        
        /* ФОРМА РЕДАКТИРОВАНИЯ */
        .edit-section {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            border: 1px solid #eef2f6;
        }
        
        .edit-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a2639;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #eef2f6;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
            color: #5a6a7e;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #eef2f6;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.2s;
            background: white;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3a7bd5;
            box-shadow: 0 0 0 3px rgba(58, 123, 213, 0.1);
        }
        
        .form-group select[multiple] {
            height: 120px;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3a7bd5, #3a6073);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 16px rgba(58, 123, 213, 0.2);
        }
        
        .btn-secondary {
            background: #f0f2f5;
            color: #5a6a7e;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }
        
        .btn-secondary:hover {
            background: #e4e8ec;
            color: #1a2639;
        }
        
        /* ТАБЛИЦА */
        .table-section {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .table-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: #1a2639;
        }
        
        .table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid #eef2f6;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        th {
            background: #f8fafc;
            color: #5a6a7e;
            font-weight: 600;
            padding: 16px;
            text-align: left;
            border-bottom: 2px solid #eef2f6;
        }
        
        td {
            padding: 16px;
            border-bottom: 1px solid #eef2f6;
            color: #1a2639;
        }
        
        tr:hover td {
            background: #f8fafc;
        }
        
        /* БЕЙДЖИ */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-male {
            background: #e8f0fe;
            color: #3a7bd5;
        }
        
        .badge-female {
            background: #fde9f0;
            color: #e84393;
        }
        
        .badge-other {
            background: #f0e6ff;
            color: #9b59b6;
        }
        
        /* КНОПКИ ДЕЙСТВИЙ */
        .action-buttons {
            display: flex;
            gap: 6px;
        }
        
        .btn-icon {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .btn-edit {
            background: #e3f7ec;
            color: #27ae60;
        }
        
        .btn-edit:hover {
            background: #d4edda;
        }
        
        .btn-delete {
            background: #fde9e9;
            color: #e74c3c;
        }
        
        .btn-delete:hover {
            background: #fad7d7;
        }
        
        /* ПУСТОЕ СОСТОЯНИЕ */
        .empty-state {
            text-align: center;
            padding: 60px;
            color: #5a6a7e;
        }
        
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        /* АДАПТИВНОСТЬ */
        @media (max-width: 768px) {
            .dashboard {
                padding: 15px;
            }
            
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            td, th {
                padding: 12px 8px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Хедер -->
        <div class="dashboard-header">
            <div class="header-left">
                <h1>Dashboard</h1>
                <p>Управление пользователями и статистика</p>
            </div>
            <div class="admin-badge">
                <div class="admin-avatar">
                    <?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?>
                </div>
                <div class="admin-info">
                    <div class="admin-name"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></div>
                    <div class="admin-role">Администратор</div>
                </div>
            </div>
        </div>
        
        <!-- Сообщения -->
        <?php if ($message): ?>
            <div class="alert <?php echo $message_type; ?>">
                <span class="alert-icon"><?php echo $message_type === 'success' ? '✅' : '❌'; ?></span>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">👥</div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $total_users; ?></div>
                    <div class="stat-label">Всего пользователей</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">💬</div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $total_with_languages; ?></div>
                    <div class="stat-label">Выбрали языки</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">📊</div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo count($stats); ?></div>
                    <div class="stat-label">Всего языков</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">📅</div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo date('d.m.Y'); ?></div>
                    <div class="stat-label">Текущая дата</div>
                </div>
            </div>
        </div>
        
        <!-- Популярность языков -->
        <div class="languages-panel">
            <div class="panel-header">
                <h3>📊 Популярность языков программирования</h3>
                <span>выбрано раз</span>
            </div>
            <div class="language-bars">
                <?php 
                $max_count = max(array_column($stats, 'user_count')) ?: 1;
                foreach ($stats as $stat): 
                    $percent = ($stat['user_count'] / $max_count) * 100;
                ?>
                    <div class="language-bar-item">
                        <span class="lang-name"><?php echo htmlspecialchars($stat['name']); ?></span>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: <?php echo $percent; ?>%;"></div>
                        </div>
                        <span class="lang-count"><?php echo $stat['user_count']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Форма редактирования -->
        <?php if ($edit_user): ?>
            <div class="edit-section">
                <div class="edit-title">
                    ✏️ Редактирование профиля: <?php echo htmlspecialchars($edit_user['full_name']); ?>
                </div>
                <form action="admin.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>ФИО</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($edit_user['full_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Телефон</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($edit_user['phone']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($edit_user['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Дата рождения</label>
                            <input type="date" name="birth_date" value="<?php echo $edit_user['birth_date']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Пол</label>
                            <select name="gender">
                                <option value="male" <?php echo $edit_user['gender'] == 'male' ? 'selected' : ''; ?>>Мужской</option>
                                <option value="female" <?php echo $edit_user['gender'] == 'female' ? 'selected' : ''; ?>>Женский</option>
                                <option value="other" <?php echo $edit_user['gender'] == 'other' ? 'selected' : ''; ?>>Другой</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Языки</label>
                            <select name="languages[]" multiple>
                                <?php foreach ($all_languages as $lang): ?>
                                    <option value="<?php echo $lang; ?>" <?php echo in_array($lang, $edit_user['languages'] ?? []) ? 'selected' : ''; ?>>
                                        <?php echo $lang; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Биография</label>
                        <textarea name="biography"><?php echo htmlspecialchars($edit_user['biography'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn-primary">💾 Сохранить изменения</button>
                        <a href="admin.php" class="btn-secondary">❌ Отмена</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        
        <!-- Таблица пользователей -->
        <div class="table-section">
            <div class="table-header">
                <h3>📋 Список пользователей (<?php echo count($users); ?>)</h3>
            </div>
            
            <?php if (!empty($users)): ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Логин</th>
                                <th>ФИО</th>
                                <th>Email</th>
                                <th>Телефон</th>
                                <th>Дата рождения</th>
                                <th>Пол</th>
                                <th>Языки</th>
                                <th>Дата регистрации</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><strong>#<?php echo $user['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['login'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td><?php echo date('d.m.Y', strtotime($user['birth_date'])); ?></td>
                                    <td>
                                        <?php
                                        $gender_class = 'badge-' . ($user['gender'] ?? 'other');
                                        $gender_text = [
                                            'male' => 'Мужской',
                                            'female' => 'Женский',
                                            'other' => 'Другой'
                                        ][$user['gender']] ?? $user['gender'];
                                        ?>
                                        <span class="badge <?php echo $gender_class; ?>"><?php echo $gender_text; ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['languages'] ?? '-'); ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="?edit=<?php echo $user['id']; ?>" class="btn-icon btn-edit">✏️</a>
                                            <form action="admin.php" method="POST" style="display: inline;" onsubmit="return confirm('Удалить пользователя <?php echo htmlspecialchars($user['full_name']); ?>?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn-icon btn-delete">🗑️</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3>Нет пользователей</h3>
                    <p>Пока никто не зарегистрировался</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Навигация -->
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" style="color: #5a6a7e; text-decoration: none; font-size: 14px;">
                ← Вернуться на главную страницу
            </a>
        </div>
    </div>
</body>
</html>

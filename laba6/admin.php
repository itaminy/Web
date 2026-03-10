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
                $message = "Пользователь удален";
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
                $message = "Данные обновлены";
                $message_type = 'success';
            }
            
        } catch (PDOException $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            $message = "Ошибка";
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
    <title>Админ панель</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            background: #000000;
            color: #00ffff;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Заголовки */
        h1, h2, h3 {
            color: #00ffff;
            margin-bottom: 20px;
            font-weight: normal;
        }
        
        h1 {
            font-size: 32px;
            border-bottom: 2px solid #00ffff;
            padding-bottom: 10px;
        }
        
        /* Статистика */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-box {
            border: 2px solid #00ffff;
            padding: 20px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 48px;
            color: #00ffff;
            font-weight: bold;
        }
        
        .stat-label {
            color: #00ffff;
            font-size: 14px;
            margin-top: 10px;
        }
        
        /* Языковая статистика */
        .lang-stats {
            border: 2px solid #00ffff;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .lang-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #00ffff33;
        }
        
        .lang-item:last-child {
            border-bottom: none;
        }
        
        .lang-name {
            color: #00ffff;
        }
        
        .lang-count {
            color: #00ffff;
            font-weight: bold;
        }
        
        /* Сообщения */
        .message {
            border: 2px solid #00ffff;
            padding: 15px;
            margin-bottom: 20px;
            color: #00ffff;
        }
        
        .message.success {
            border-color: #00ffff;
        }
        
        .message.error {
            border-color: #ff0000;
            color: #ff0000;
        }
        
        /* Форма */
        .edit-form {
            border: 2px solid #00ffff;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            color: #00ffff;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 10px;
            background: #000000;
            border: 2px solid #00ffff;
            color: #00ffff;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            background: #001111;
        }
        
        select[multiple] {
            height: 120px;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        button, .btn {
            background: #000000;
            border: 2px solid #00ffff;
            color: #00ffff;
            padding: 10px 20px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        button:hover, .btn:hover {
            background: #00ffff;
            color: #000000;
        }
        
        /* Таблица */
        .table-section {
            border: 2px solid #00ffff;
            padding: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #00ffff;
            color: #00ffff;
            font-weight: normal;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #00ffff33;
            color: #00ffff;
        }
        
        tr:hover td {
            background: #001111;
        }
        
        /* Бейджи */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border: 1px solid #00ffff;
            font-size: 12px;
        }
        
        .badge-male {
            border-color: #00ffff;
        }
        
        .badge-female {
            border-color: #ff00ff;
            color: #ff00ff;
        }
        
        .badge-other {
            border-color: #ffff00;
            color: #ffff00;
        }
        
        /* Действия */
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .action-btn {
            background: none;
            border: 1px solid #00ffff;
            color: #00ffff;
            padding: 4px 8px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .action-btn:hover {
            background: #00ffff;
            color: #000000;
        }
        
        .action-btn.delete:hover {
            border-color: #ff0000;
            background: #ff0000;
            color: #000000;
        }
        
        /* Ссылка */
        .back-link {
            text-align: center;
            margin-top: 30px;
        }
        
        .back-link a {
            color: #00ffff;
            text-decoration: none;
            border-bottom: 1px dashed #00ffff;
        }
        
        .back-link a:hover {
            border-bottom: 1px solid #00ffff;
        }
        
        /* Адаптивность */
        @media (max-width: 768px) {
            .stats {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            table {
                font-size: 12px;
            }
            
            td, th {
                padding: 8px 4px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>АДМИН ПАНЕЛЬ</h1>
        
        <div style="text-align: right; margin-bottom: 20px; color: #00ffff;">
            [ <?php echo htmlspecialchars($_SESSION['admin_username']); ?> ]
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                > <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Статистика -->
        <div class="stats">
            <div class="stat-box">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">ПОЛЬЗОВАТЕЛЕЙ</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $total_with_languages; ?></div>
                <div class="stat-label">ВЫБРАЛИ ЯЗЫКИ</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo count($stats); ?></div>
                <div class="stat-label">ЯЗЫКОВ</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo date('d.m.Y'); ?></div>
                <div class="stat-label">ДАТА</div>
            </div>
        </div>
        
        <!-- Языки -->
        <div class="lang-stats">
            <h3>ЯЗЫКИ ПРОГРАММИРОВАНИЯ</h3>
            <?php foreach ($stats as $stat): ?>
                <div class="lang-item">
                    <span class="lang-name">> <?php echo str_pad(htmlspecialchars($stat['name']), 12, '.'); ?></span>
                    <span class="lang-count">[<?php echo $stat['user_count']; ?>]</span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Редактирование -->
        <?php if ($edit_user): ?>
            <div class="edit-form">
                <h3>РЕДАКТИРОВАНИЕ [ID: <?php echo $edit_user['id']; ?>]</h3>
                <form action="admin.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                    
                    <div class="form-row">
                        <div>
                            <label>ФИО</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($edit_user['full_name']); ?>">
                        </div>
                        <div>
                            <label>ТЕЛЕФОН</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($edit_user['phone']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div>
                            <label>EMAIL</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($edit_user['email']); ?>">
                        </div>
                        <div>
                            <label>ДАТА РОЖДЕНИЯ</label>
                            <input type="date" name="birth_date" value="<?php echo $edit_user['birth_date']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div>
                            <label>ПОЛ</label>
                            <select name="gender">
                                <option value="male" <?php echo $edit_user['gender'] == 'male' ? 'selected' : ''; ?>>МУЖСКОЙ</option>
                                <option value="female" <?php echo $edit_user['gender'] == 'female' ? 'selected' : ''; ?>>ЖЕНСКИЙ</option>
                                <option value="other" <?php echo $edit_user['gender'] == 'other' ? 'selected' : ''; ?>>ДРУГОЙ</option>
                            </select>
                        </div>
                        <div>
                            <label>ЯЗЫКИ</label>
                            <select name="languages[]" multiple>
                                <?php foreach ($all_languages as $lang): ?>
                                    <option value="<?php echo $lang; ?>" <?php echo in_array($lang, $edit_user['languages'] ?? []) ? 'selected' : ''; ?>>
                                        <?php echo $lang; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label>БИОГРАФИЯ</label>
                        <textarea name="biography"><?php echo htmlspecialchars($edit_user['biography'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit">[ СОХРАНИТЬ ]</button>
                        <a href="admin.php" class="btn">[ ОТМЕНА ]</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        
        <!-- Таблица пользователей -->
        <div class="table-section">
            <h3>СПИСОК ПОЛЬЗОВАТЕЛЕЙ [<?php echo count($users); ?>]</h3>
            
            <?php if (!empty($users)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ЛОГИН</th>
                            <th>ФИО</th>
                            <th>EMAIL</th>
                            <th>ТЕЛЕФОН</th>
                            <th>ПОЛ</th>
                            <th>ЯЗЫКИ</th>
                            <th>ДЕЙСТВИЯ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['login'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td>
                                    <?php
                                    $gender_class = 'badge-' . ($user['gender'] ?? 'other');
                                    $gender_text = [
                                        'male' => 'М',
                                        'female' => 'Ж',
                                        'other' => '?'
                                    ][$user['gender']] ?? '?';
                                    ?>
                                    <span class="badge <?php echo $gender_class; ?>"><?php echo $gender_text; ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($user['languages'] ?? '-'); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="?edit=<?php echo $user['id']; ?>" class="action-btn">[Р]</a>
                                        <form action="admin.php" method="POST" style="display: inline;" onsubmit="return confirm('Удалить?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="action-btn delete">[X]</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #00ffff;">
                    > НЕТ ПОЛЬЗОВАТЕЛЕЙ <
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Навигация -->
        <div class="back-link">
            <a href="index.php">← НА ГЛАВНУЮ</a>
        </div>
    </div>
</body>
</html>

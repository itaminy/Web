<?php
session_start();

// Заголовки безопасности
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

// Отключаем вывод ошибок
ini_set('display_errors', 0);

// Подключаем конфиг
$config_file = '/home/u82382/config/laba3/db_config.php';
if (!file_exists($config_file)) {
    die('Configuration error');
}
require_once $config_file;

// Функция экранирования
function e($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

// Функция для валидации ID
function validateId($id) {
    return filter_var($id, FILTER_VALIDATE_INT) !== false && $id > 0;
}

// HTTP-авторизация
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Требуется авторизация';
    exit;
}

// Проверка авторизации
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin || !password_verify($_SERVER['PHP_AUTH_PW'], $admin['password_hash'])) {
        header('WWW-Authenticate: Basic realm="Admin Panel"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Неверный логин или пароль';
        exit;
    }
    
    $_SESSION['admin_id'] = (int)$admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    
} catch (PDOException $e) {
    error_log('Admin DB error: ' . $e->getMessage());
    die('Ошибка подключения к БД');
}

// Генерация CSRF токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Обработка действий
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = 'Ошибка безопасности';
        $message_type = 'error';
    } elseif (isset($_POST['action'])) {
        try {
            if ($_POST['action'] === 'delete' && isset($_POST['user_id']) && validateId($_POST['user_id'])) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([(int)$_POST['user_id']]);
                $message = "Пользователь удален";
                $message_type = 'success';
            }
            
            if ($_POST['action'] === 'update' && isset($_POST['user_id']) && validateId($_POST['user_id'])) {
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
                    (int)$_POST['user_id']
                ]);
                
                if (isset($_POST['languages']) && is_array($_POST['languages'])) {
                    // Удаляем старые языки
                    $pdo->prepare("DELETE FROM user_languages WHERE user_id = ?")->execute([(int)$_POST['user_id']]);
                    
                    // Добавляем новые
                    $lang_stmt = $pdo->prepare("
                        INSERT INTO user_languages (user_id, language_id) 
                        SELECT ?, id FROM programming_languages WHERE name = ?
                    ");
                    
                    foreach ($_POST['languages'] as $lang) {
                        $lang_stmt->execute([(int)$_POST['user_id'], $lang]);
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
            error_log('Admin action error: ' . $e->getMessage());
            $message = "Ошибка базы данных";
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
    error_log('Admin data error: ' . $e->getMessage());
    die('Ошибка получения данных');
}

// Получение данных для редактирования
$edit_user = null;
if (isset($_GET['edit']) && validateId($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($edit_user) {
        $lang_stmt = $pdo->prepare("
            SELECT pl.name FROM programming_languages pl
            JOIN user_languages ul ON pl.id = ul.language_id
            WHERE ul.user_id = ?
        ");
        $lang_stmt->execute([(int)$_GET['edit']]);
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
            font-family: Arial, sans-serif;
            background: #ffffff;
            color: #333333;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333333;
        }
        
        h2 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #333333;
        }
        
        h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333333;
        }
        
        /* Статистика */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-box {
            background: #f5f5f5;
            padding: 15px;
            border: 1px solid #dddddd;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #333333;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666666;
            margin-top: 5px;
        }
        
        /* Языковая статистика */
        .lang-stats {
            background: #f5f5f5;
            padding: 15px;
            border: 1px solid #dddddd;
            margin-bottom: 30px;
        }
        
        .lang-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #dddddd;
        }
        
        .lang-item:last-child {
            border-bottom: none;
        }
        
        .lang-name {
            color: #333333;
        }
        
        .lang-count {
            font-weight: bold;
            color: #333333;
        }
        
        /* Сообщения */
        .message {
            padding: 10px 15px;
            margin-bottom: 20px;
            border: 1px solid #dddddd;
        }
        
        .message.success {
            background: #e8f5e9;
            border-color: #4caf50;
            color: #2e7d32;
        }
        
        .message.error {
            background: #ffebee;
            border-color: #f44336;
            color: #c62828;
        }
        
        /* Форма */
        .edit-form {
            background: #f5f5f5;
            padding: 20px;
            border: 1px solid #dddddd;
            margin-bottom: 30px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 14px;
            color: #333333;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #cccccc;
            background: #ffffff;
            font-size: 14px;
            color: #333333;
        }
        
        select[multiple] {
            height: 120px;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        button, .btn {
            background: #4a90e2;
            border: none;
            color: white;
            padding: 8px 16px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        button:hover, .btn:hover {
            background: #357abd;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        /* Таблица */
        .table-section {
            background: #ffffff;
            border: 1px solid #dddddd;
            padding: 15px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 10px;
            background: #f5f5f5;
            border-bottom: 2px solid #dddddd;
            font-weight: bold;
            color: #333333;
        }
        
        td {
            padding: 10px;
            border-bottom: 1px solid #eeeeee;
            color: #333333;
        }
        
        /* Действия */
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .action-btn {
            background: #4a90e2;
            color: white;
            padding: 4px 8px;
            font-size: 12px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        
        .action-btn.delete {
            background: #dc3545;
        }
        
        .action-btn:hover {
            opacity: 0.8;
        }
        
        /* Бейджи */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            background: #e9ecef;
            border-radius: 3px;
            font-size: 12px;
        }
        
        /* Ссылка назад */
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #4a90e2;
            text-decoration: none;
        }
        
        .back-link a:hover {
            text-decoration: underline;
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
                font-size: 13px;
            }
            
            td, th {
                padding: 8px 4px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Панель администратора</h1>
        
        <div style="text-align: right; margin-bottom: 20px;">
            Вы вошли как: <strong><?php echo e($_SESSION['admin_username']); ?></strong>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo e($message_type); ?>">
                <?php echo e($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Статистика -->
        <div class="stats">
            <div class="stat-box">
                <div class="stat-number"><?php echo (int)$total_users; ?></div>
                <div class="stat-label">Всего пользователей</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo (int)$total_with_languages; ?></div>
                <div class="stat-label">Выбрали языки</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo count($stats); ?></div>
                <div class="stat-label">Языков в списке</div>
            </div>
        </div>
        
        <!-- Языки -->
        <div class="lang-stats">
            <h3>Популярность языков</h3>
            <?php foreach ($stats as $stat): ?>
                <div class="lang-item">
                    <span class="lang-name"><?php echo e($stat['name']); ?></span>
                    <span class="lang-count"><?php echo (int)$stat['user_count']; ?> пользователей</span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Редактирование -->
        <?php if ($edit_user): ?>
            <div class="edit-form">
                <h3>Редактирование пользователя #<?php echo (int)$edit_user['id']; ?></h3>
                <form action="admin.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_id" value="<?php echo (int)$edit_user['id']; ?>">
                    
                    <div class="form-row">
                        <div>
                            <label>ФИО</label>
                            <input type="text" name="full_name" value="<?php echo e($edit_user['full_name']); ?>" required>
                        </div>
                        <div>
                            <label>Телефон</label>
                            <input type="text" name="phone" value="<?php echo e($edit_user['phone']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div>
                            <label>Email</label>
                            <input type="email" name="email" value="<?php echo e($edit_user['email']); ?>" required>
                        </div>
                        <div>
                            <label>Дата рождения</label>
                            <input type="date" name="birth_date" value="<?php echo e($edit_user['birth_date']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div>
                            <label>Пол</label>
                            <select name="gender">
                                <option value="male" <?php echo $edit_user['gender'] == 'male' ? 'selected' : ''; ?>>Мужской</option>
                                <option value="female" <?php echo $edit_user['gender'] == 'female' ? 'selected' : ''; ?>>Женский</option>
                                <option value="other" <?php echo $edit_user['gender'] == 'other' ? 'selected' : ''; ?>>Другой</option>
                            </select>
                        </div>
                        <div>
                            <label>Языки</label>
                            <select name="languages[]" multiple>
                                <?php foreach ($all_languages as $lang): ?>
                                    <option value="<?php echo e($lang); ?>" <?php echo in_array($lang, $edit_user['languages'] ?? []) ? 'selected' : ''; ?>>
                                        <?php echo e($lang); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label>Биография</label>
                        <textarea name="biography"><?php echo e($edit_user['biography'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit">Сохранить</button>
                        <a href="admin.php" class="btn btn-secondary">Отмена</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        
        <!-- Таблица пользователей -->
        <div class="table-section">
            <h3>Список пользователей (<?php echo count($users); ?>)</h3>
            
            <?php if (!empty($users)): ?>
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
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo (int)$user['id']; ?></td>
                                <td><?php echo e($user['login'] ?? '-'); ?></td>
                                <td><?php echo e($user['full_name']); ?></td>
                                <td><?php echo e($user['email']); ?></td>
                                <td><?php echo e($user['phone']); ?></td>
                                <td><?php echo e($user['birth_date']); ?></td>
                                <td>
                                    <?php
                                    $gender_text = [
                                        'male' => 'М',
                                        'female' => 'Ж',
                                        'other' => '?'
                                    ][$user['gender']] ?? '?';
                                    ?>
                                    <span class="badge"><?php echo e($gender_text); ?></span>
                                </td>
                                <td><?php echo e($user['languages'] ?? '-'); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="?edit=<?php echo (int)$user['id']; ?>" class="action-btn">Ред</a>
                                        <form action="admin.php" method="POST" style="display: inline;" onsubmit="return confirm('Удалить пользователя?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                            <button type="submit" class="action-btn delete">Удал</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; padding: 40px; color: #666;">Нет пользователей</p>
            <?php endif; ?>
        </div>
        
        <!-- Навигация -->
        <div class="back-link">
            <a href="index.php">← Вернуться на главную</a>
        </div>
    </div>
</body>
</html>

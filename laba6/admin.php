<?php
session_start();

// Подключение к БД
$config_file = '/home/u82382/config/laba3/db_config.php';
if (!file_exists($config_file)) {
    die('Файл конфигурации не найден!');
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
    
    // Поиск администратора в БД
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC));
    
    // Проверка пароля
    if (!$admin || !password_verify($_SERVER['PHP_AUTH_PW'], $admin['password_hash'])) {
        header('WWW-Authenticate: Basic realm="Admin Panel"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Неверный логин или пароль';
        exit;
    }
    
    // Сохраняем данные админа в сессию
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    
} catch (PDOException $e) {
    die('Ошибка базы данных: ' . $e->getMessage());
}

// Обработка действий
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            // Удаление пользователя
            if ($_POST['action'] === 'delete' && isset($_POST['user_id'])) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$_POST['user_id']]);
                $message = "Пользователь успешно удален";
                $message_type = 'success';
            }
            
            // Обновление пользователя
            if ($_POST['action'] === 'update' && isset($_POST['user_id'])) {
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
                
                // Обновление языков
                if (isset($_POST['languages']) && is_array($_POST['languages'])) {
                    // Удалить старые языки
                    $pdo->prepare("DELETE FROM user_languages WHERE user_id = ?")->execute([$_POST['user_id']]);
                    
                    // Добавить новые
                    $lang_stmt = $pdo->prepare("
                        INSERT INTO user_languages (user_id, language_id) 
                        SELECT ?, id FROM programming_languages WHERE name = ?
                    ");
                    
                    foreach ($_POST['languages'] as $lang) {
                        $lang_stmt->execute([$_POST['user_id'], $lang]);
                    }
                }
                
                $message = "Данные пользователя обновлены";
                $message_type = 'success';
            }
            
        } catch (PDOException $e) {
            $message = "Ошибка: " . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Получение данных для отображения
try {
    // Статистика по языкам
    $stats = $pdo->query("
        SELECT 
            pl.name,
            COUNT(ul.user_id) as user_count
        FROM programming_languages pl
        LEFT JOIN user_languages ul ON pl.id = ul.language_id
        GROUP BY pl.id
        ORDER BY user_count DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Общая статистика
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $total_with_languages = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM user_languages")->fetchColumn();
    
    // Все пользователи с их языками
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
    die('Ошибка получения данных: ' . $e->getMessage());
}

// Получение данных для редактирования
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

// Получение списка всех языков для формы
$all_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 
                 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .stat-card h3 { margin-top: 0; color: #333; }
        .stat-number { font-size: 2em; color: #667eea; font-weight: bold; }
        .language-stats { list-style: none; padding: 0; }
        .language-stats li { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .badge { background: #667eea; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.9em; }
        .action-buttons { white-space: nowrap; }
        .action-buttons a, .action-buttons button { display: inline-block; padding: 4px 8px; margin: 0 2px; border-radius: 4px; text-decoration: none; font-size: 0.9em; border: none; cursor: pointer; }
        .btn-edit { background: #28a745; color: white; }
        .btn-delete { background: #dc3545; color: white; }
        .message { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .message.success { background: #d4edda; color: #155724; border-left: 4px solid #155724; }
        .message.error { background: #f8d7da; color: #721c24; border-left: 4px solid #721c24; }
        .edit-form { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; background: white; border-collapse: collapse; }
        th { background: #667eea; color: white; padding: 12px; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f5f5f5; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .admin-info { background: #e3f2fd; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .btn-logout { background: #6c757d; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>👨‍💼 Панель администратора</h1>
            <div>
                <span class="admin-info">Вы вошли как: <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong></span>
                <a href="#" onclick="window.location.reload()" class="btn-logout">🔄 Обновить</a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Статистика -->
        <div class="stats-container">
            <div class="stat-card">
                <h3>📊 Общая статистика</h3>
                <p>Всего пользователей: <span class="stat-number"><?php echo $total_users; ?></span></p>
                <p>Выбрали языки: <span class="stat-number"><?php echo $total_with_languages; ?></span></p>
            </div>
            
            <div class="stat-card">
                <h3>💻 Популярность языков</h3>
                <ul class="language-stats">
                    <?php foreach ($stats as $stat): ?>
                        <li>
                            <span><?php echo htmlspecialchars($stat['name']); ?></span>
                            <span class="badge"><?php echo $stat['user_count']; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <!-- Форма редактирования -->
        <?php if ($edit_user): ?>
            <div class="edit-form">
                <h3>✏️ Редактирование пользователя #<?php echo $edit_user['id']; ?> (<?php echo htmlspecialchars($edit_user['full_name']); ?>)</h3>
                <form action="admin.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                    
                    <div class="form-group">
                        <label>ФИО:</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($edit_user['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Телефон:</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($edit_user['phone']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($edit_user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Дата рождения:</label>
                        <input type="date" name="birth_date" value="<?php echo $edit_user['birth_date']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Пол:</label>
                        <select name="gender">
                            <option value="male" <?php echo $edit_user['gender'] == 'male' ? 'selected' : ''; ?>>Мужской</option>
                            <option value="female" <?php echo $edit_user['gender'] == 'female' ? 'selected' : ''; ?>>Женский</option>
                            <option value="other" <?php echo $edit_user['gender'] == 'other' ? 'selected' : ''; ?>>Другой</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Языки (Ctrl+клик для выбора нескольких):</label>
                        <select name="languages[]" multiple size="6">
                            <?php foreach ($all_languages as $lang): ?>
                                <option value="<?php echo $lang; ?>" <?php echo in_array($lang, $edit_user['languages'] ?? []) ? 'selected' : ''; ?>><?php echo $lang; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Биография:</label>
                        <textarea name="biography"><?php echo htmlspecialchars($edit_user['biography'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn-submit">💾 Сохранить изменения</button>
                    <a href="admin.php" class="btn-submit" style="background: #6c757d;">❌ Отмена</a>
                </form>
            </div>
        <?php endif; ?>
        
        <!-- Таблица пользователей -->
        <h3>📋 Все пользователи (<?php echo count($users); ?>)</h3>
        <div class="table-responsive">
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
                            <td><?php echo $user['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($user['login'] ?? ''); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo $user['birth_date']; ?></td>
                            <td>
                                <?php
                                $gender_labels = ['male' => '👨 Мужской', 'female' => '👩 Женский', 'other' => '👤 Другой'];
                                echo $gender_labels[$user['gender']] ?? $user['gender'];
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($user['languages'] ?? '—'); ?></td>
                            <td><?php echo $user['created_at']; ?></td>
                            <td class="action-buttons">
                                <a href="?edit=<?php echo $user['id']; ?>" class="btn-edit">✏️ Ред.</a>
                                <form action="admin.php" method="POST" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите удалить пользователя <?php echo htmlspecialchars($user['full_name']); ?>?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn-delete">🗑️ Удал.</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (empty($users)): ?>
            <p style="text-align: center; padding: 20px; background: white; border-radius: 10px;">Нет зарегистрированных пользователей</p>
        <?php endif; ?>
    </div>
</body>
</html>

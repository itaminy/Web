# Поликлиника «Здоровье»

Чисто статический фронтенд для поликлиники, написанный на **HTML / CSS / JS / PHP** без какого-либо фреймворка. Бэкенд (база данных, API) пользователь будет реализовывать самостоятельно — все данные сейчас задаются массивами в `includes/sample-data.php`.

## Архитектура

- **Артефакт:** `artifacts/clinic` (`kind = web`, `previewPath = /`).
- **Сервер:** встроенный PHP 8.4 (`php -S 0.0.0.0:$PORT -t .`), запускается из директории артефакта.
- **Никакого Node.js, Express, Drizzle, React, Vite.** Все Node-артефакты и lib-пакеты были удалены.
- **БД:** существует `DATABASE_URL`, но таблицы удалены — пользователь сам определит схему.

## Структура файлов

```
artifacts/clinic/
├── index.php             # Главная: герой с видео, услуги, врачи, о клинике, отзывы, CTA, контакты
├── apply.php             # Публичная форма заявки на лечение
├── dashboard.php         # Админ: KPI + бар-чарт + донат + последние приёмы
├── doctors.php           # Админ: список врачей
├── patients.php          # Админ: пациенты
├── diseases.php          # Админ: справочник болезней
├── visits.php            # Админ: журнал приёмов
├── applications.php      # Админ: заявки с сайта
├── includes/
│   ├── sample-data.php   # Все данные ($DOCTORS, $PATIENTS, $DISEASES, $VISITS, $APPLICATIONS, $STATS, ...)
│   ├── header.php        # Публичная шапка/навигация
│   ├── footer.php        # Публичный подвал + плавающая кнопка
│   ├── admin-header.php  # Админ-сайдбар + топбар
│   └── admin-footer.php  # Закрытие админ-разметки
├── css/styles.css        # Полный набор стилей и анимаций
├── js/main.js            # Скролл-эффекты, reveal, count-up, бар/донат-чарты, поиск, фильтр, форма
├── images/               # Все фото в одной папке (doctor-1..6.png, reception.png, home-about.png и т.д.)
└── videos/hero-bg.mp4    # Видео для hero-секции
```

## Дизайн / UX

- Цвет бренда — бирюзовый `#14b8a6` + акцентный синий `#3b82f6`.
- Шрифт — Inter (Google Fonts).
- Все тексты — на русском, **без эмодзи** (все иконки — inline SVG).
- Hero с ken-burns-постером и зацикленным видео-фоном.
- Анимации: появление по скроллу (IntersectionObserver), счётчики (count-up), магнитный эффект на кнопках, прогресс-бары, рисованный SVG-донат, пульсация маяка, hover-эффекты на карточках и фотографиях врачей.

## Подключение реальных данных

Замените массивы в `includes/sample-data.php` на запросы к вашей БД:

```php
$DOCTORS    = $db->query("SELECT * FROM doctors")->fetchAll();
$PATIENTS   = $db->query("SELECT * FROM patients")->fetchAll();
// ... и т.д.
```

Названия полей, ожидаемые шаблонами:

- `$DOCTORS`: `id, name, specialty, experience, room, phone, email, photo, bio`
- `$PATIENTS`: `id, name, birth, gender ('муж.'|'жен.'), phone, address, policy`
- `$DISEASES`: `id, name, icd10, severity ('mild'|'moderate'|'severe'), description`
- `$VISITS`: `id, date (YYYY-MM-DD HH:MM), patient, doctor, disease, status ('scheduled'|'completed'|'cancelled')`
- `$APPLICATIONS`: `id, name, phone, email, date, doctor, symptoms, status ('new'|'approved'|'rejected')`
- `$STATS`: ассоциативный массив с числовыми KPI
- `$VISITS_BY_DOCTOR`, `$VISITS_BY_DISEASE`: массивы `[{ name, count }]` для графиков

Хелперы `severity_label($s)` и `status_label($s)` возвращают русские подписи для бэйджей.

## Запуск / разработка

Workflow `artifacts/clinic: web` запускает PHP-сервер. После правки PHP/CSS/JS обновите страницу — встроенный сервер изменения подхватывает мгновенно (рестартить workflow не нужно).

## Известные предостережения

- `pnpm-workspace.yaml` оставлен на месте, потому что glob `artifacts/*` без `package.json` безопасен.
- Реактовский интегрированный навык в `artifact.toml` оставлен (его нельзя удалить через скилл-обвязку), но фактически не используется — приложение работает на чистом PHP.

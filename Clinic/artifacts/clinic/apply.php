<?php
require __DIR__ . '/includes/sample-data.php';
$page_title = 'Подать заявку на лечение';
include __DIR__ . '/includes/header.php';
?>

<section class="apply-page">
    <div class="container">
        <div class="apply-grid">
            <aside class="apply-aside reveal">
                <span class="section-eyebrow">Запись на приём</span>
                <h2>Расскажите о себе — мы вам перезвоним</h2>
                <p>Заполните короткую форму, и наш администратор свяжется с вами в течение 30 минут, чтобы подобрать удобное время и врача.</p>

                <ul class="apply-info">
                    <li>
                        <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
                        <div><strong>Быстрая обработка</strong><span>Перезвоним в течение 30 минут в рабочее время</span></div>
                    </li>
                    <li>
                        <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3 8-8"/><path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9c1.79 0 3.45.52 4.85 1.42"/></svg></div>
                        <div><strong>Без обязательств</strong><span>Заявка не обязывает вас к посещению</span></div>
                    </li>
                    <li>
                        <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg></div>
                        <div><strong>Конфиденциально</strong><span>Ваши данные надёжно защищены</span></div>
                    </li>
                </ul>
            </aside>

            <div class="form-card reveal">
                <form id="applyForm" novalidate>
                    <div class="form-grid">
                        <div class="form-field form-row">
                            <label for="f-name">ФИО *</label>
                            <input type="text" id="f-name" name="name" required placeholder="Иванов Иван Иванович">
                        </div>
                        <div class="form-field">
                            <label for="f-phone">Телефон *</label>
                            <input type="tel" id="f-phone" name="phone" required placeholder="+7 (___) ___-__-__">
                        </div>
                        <div class="form-field">
                            <label for="f-email">Email</label>
                            <input type="email" id="f-email" name="email" placeholder="you@example.com">
                        </div>
                        <div class="form-field">
                            <label for="f-doctor">Желаемый врач</label>
                            <select id="f-doctor" name="doctor">
                                <option value="">Не имеет значения</option>
                                <?php foreach ($DOCTORS as $d): ?>
                                <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['name']) ?> — <?= htmlspecialchars($d['specialty']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-field">
                            <label for="f-date">Желаемая дата</label>
                            <input type="date" id="f-date" name="date">
                        </div>
                        <div class="form-field form-row">
                            <label for="f-symptoms">Симптомы или причина обращения *</label>
                            <textarea id="f-symptoms" name="symptoms" required placeholder="Опишите, что вас беспокоит..."></textarea>
                        </div>
                        <div class="form-field form-row">
                            <label class="flex gap-12" style="align-items: flex-start; font-weight: 500;">
                                <input type="checkbox" required style="margin-top: 4px; width: 18px; height: 18px;">
                                <span style="font-size: 14px; color: var(--text-muted);">Я согласен(на) с обработкой персональных данных в соответствии с политикой конфиденциальности.</span>
                            </label>
                        </div>
                        <div class="form-actions form-row">
                            <button type="submit" class="btn btn-primary btn-block btn-lg">Отправить заявку</button>
                        </div>
                    </div>
                </form>

                <div id="applySuccess" class="form-success" style="display:none;">
                    <div class="check-circle">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <h3>Заявка отправлена!</h3>
                    <p>Мы получили вашу заявку и свяжемся с вами в течение 30 минут.<br>Спасибо, что выбрали нашу клинику.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

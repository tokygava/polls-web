<?php
$isRegistration = $page === 'register';
$title = $isRegistration ? 'Регистрация' : 'Вход';
$escape = static function (string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
$documentTitle = $title . ' — Опросы';
$currentPage = $page;
require __DIR__ . '/layout-start.php';
?>
    <main class="page page-narrow" id="main" tabindex="-1">
        <section class="panel">
            <h1><?= $title ?></h1>
            <?php if ($errors !== []): ?>
                <ul class="notice notice-error" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $escape($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <form method="post" action="?page=<?= $page ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <?php if ($isRegistration): ?>
                    <p>
                        <label for="name">Имя</label>
                        <input id="name" name="name" type="text" maxlength="100"
                               value="<?= $escape($fields['name']) ?>" autocomplete="name" required>
                    </p>
                <?php endif; ?>
                <p>
                    <label for="email">Электронная почта</label>
                    <input id="email" name="email" type="email" maxlength="254"
                           value="<?= $escape($fields['email']) ?>" autocomplete="username" required>
                </p>
                <p>
                    <label for="password">Пароль</label>
                    <input id="password" name="password" type="password"
                           autocomplete="<?= $isRegistration ? 'new-password' : 'current-password' ?>"
                           <?= $isRegistration ? 'minlength="8" maxlength="72"' : '' ?> required>
                </p>
                <button class="button button-primary" type="submit"><?= $isRegistration ? 'Зарегистрироваться' : 'Войти' ?></button>
            </form>
            <?php if ($isRegistration): ?>
                <p><a href="?page=login">Уже есть аккаунт?</a></p>
            <?php else: ?>
                <p><a href="?page=register">Создать аккаунт</a></p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>

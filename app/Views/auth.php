<?php
$isRegistration = $page === 'register';
$title = $isRegistration ? 'Регистрация' : 'Вход';
$escape = static function (string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?> — Опросы</title>
</head>
<body>
    <main>
        <a href="./">На главную</a>
        <h1><?= $title ?></h1>
        <?php if ($errors !== []): ?>
            <ul role="alert">
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
            <button type="submit"><?= $isRegistration ? 'Зарегистрироваться' : 'Войти' ?></button>
        </form>
        <?php if ($isRegistration): ?>
            <p><a href="?page=login">Уже есть аккаунт?</a></p>
        <?php else: ?>
            <p><a href="?page=register">Создать аккаунт</a></p>
        <?php endif; ?>
    </main>
</body>
</html>

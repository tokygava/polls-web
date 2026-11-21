<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Опросы</title>
</head>
<body>
    <main>
        <h1>Опросы</h1>
        <?php if ($user === null): ?>
            <nav aria-label="Аккаунт">
                <a href="?page=register">Регистрация</a>
                <a href="?page=login">Войти</a>
            </nav>
        <?php else: ?>
            <p>Здравствуйте, <?= htmlspecialchars($user['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>!</p>
            <form method="post" action="?page=logout">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <button type="submit">Выйти</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>

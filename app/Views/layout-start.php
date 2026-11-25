<?php
$signedIn = isset($_SESSION['user']);
$navigationToken = $signedIn ? ($_SESSION['csrf_token'] ??= bin2hex(random_bytes(32))) : '';
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($documentTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></title>
    <link rel="stylesheet" href="styles.css">
    <?php if ($currentPage === 'create-poll'): ?>
        <script src="poll-create.js" defer></script>
    <?php endif; ?>
</head>
<body>
    <a class="skip-link" href="#main">К содержимому</a>
    <header class="site-header">
        <div class="site-bar">
            <a class="brand" href="./"<?= $currentPage === 'home' ? ' aria-current="page"' : '' ?>>
                <span class="brand-mark" aria-hidden="true"><i></i><i></i><i></i></span>
                Опросы
            </a>
            <nav class="site-nav" aria-label="Основное меню">
                <a href="?page=polls"<?= $currentPage === 'polls' ? ' aria-current="page"' : '' ?>>Все опросы</a>
                <?php if ($signedIn): ?>
                    <a href="?page=drafts"<?= $currentPage === 'drafts' ? ' aria-current="page"' : '' ?>>Мои черновики</a>
                    <a class="button button-primary" href="?page=create-poll"<?= $currentPage === 'create-poll' ? ' aria-current="page"' : '' ?>>Создать опрос</a>
                    <form method="post" action="?page=logout">
                        <input type="hidden" name="csrf_token" value="<?= $navigationToken ?>">
                        <button class="button button-secondary" type="submit">Выйти</button>
                    </form>
                <?php else: ?>
                    <a href="?page=login"<?= $currentPage === 'login' ? ' aria-current="page"' : '' ?>>Войти</a>
                    <a class="button button-primary" href="?page=register"<?= $currentPage === 'register' ? ' aria-current="page"' : '' ?>>Регистрация</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

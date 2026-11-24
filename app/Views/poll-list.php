<?php
$heading = $isDraftList ? 'Мои черновики' : 'Опубликованные опросы';
$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $heading ?> — Опросы</title>
</head>
<body>
    <main>
        <a href="./">На главную</a>
        <h1><?= $heading ?></h1>
        <?php if ($isDraftList): ?>
            <p><a href="?page=create-poll">Создать опрос</a></p>
        <?php endif; ?>

        <?php if ($polls === []): ?>
            <p><?= $isDraftList ? 'У тебя пока нет черновиков.' : 'Пока нет опубликованных опросов.' ?></p>
        <?php endif; ?>

        <?php foreach ($polls as $poll): ?>
            <article>
                <h2>
                    <?php if ($isDraftList): ?>
                        <?= $escape($poll['title']) ?>
                    <?php else: ?>
                        <a href="?page=poll&amp;id=<?= (int) $poll['id'] ?>"><?= $escape($poll['title']) ?></a>
                    <?php endif; ?>
                </h2>
                <?php if ($poll['description'] !== ''): ?>
                    <p><?= nl2br($escape($poll['description'])) ?></p>
                <?php endif; ?>
                <?php if ($isDraftList): ?>
                    <form method="post" action="?page=publish-poll">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="poll_id" value="<?= (int) $poll['id'] ?>">
                        <button type="submit">Опубликовать</button>
                    </form>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </main>
</body>
</html>

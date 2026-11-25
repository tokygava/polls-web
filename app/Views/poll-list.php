<?php
$heading = $isDraftList ? 'Мои черновики' : 'Опубликованные опросы';
$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$documentTitle = $heading . ' — Опросы';
$currentPage = $isDraftList ? 'drafts' : 'polls';
require __DIR__ . '/layout-start.php';
?>
    <main class="page" id="main" tabindex="-1">
        <h1><?= $heading ?></h1>
        <?php if ($polls === []): ?>
            <p class="panel muted"><?= $isDraftList ? 'У вас пока нет черновиков.' : 'Пока нет опубликованных опросов.' ?></p>
        <?php endif; ?>

        <div class="poll-grid">
            <?php foreach ($polls as $poll): ?>
                <article class="panel poll-card">
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
                            <button class="button button-primary" type="submit">Опубликовать</button>
                        </form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>

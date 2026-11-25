<?php
$documentTitle = 'Опросы';
$currentPage = 'home';
require __DIR__ . '/layout-start.php';
?>
    <main class="page" id="main" tabindex="-1">
        <section class="hero">
            <h1><?= $user === null ? 'Опросы' : 'Здравствуйте, ' . htmlspecialchars($user['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '!' ?></h1>
            <p>Создавайте свои опросы и голосуйте в опубликованных.</p>
            <div class="actions">
                <a class="button button-primary" href="?page=polls">Посмотреть опросы</a>
                <?php if ($user !== null): ?>
                    <a class="button button-secondary" href="?page=create-poll">Создать опрос</a>
                <?php endif; ?>
            </div>
        </section>
        <?php if ($draftSaved): ?>
            <p class="notice" role="status">Черновик опроса сохранён. <a href="?page=drafts">Открыть черновики</a></p>
        <?php endif; ?>
    </main>
</body>
</html>

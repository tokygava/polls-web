<?php
$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$documentTitle = $poll['title'] . ' — Опросы';
$currentPage = 'poll';
require __DIR__ . '/layout-start.php';
?>
    <main class="page page-form" id="main" tabindex="-1">
        <a class="back-link" href="?page=polls">К списку опросов</a>
        <h1><?= $escape($poll['title']) ?></h1>
        <?php if ($poll['description'] !== ''): ?>
            <p><?= nl2br($escape($poll['description'])) ?></p>
        <?php endif; ?>
        <?php if ($userId === $poll['author_id']): ?>
            <p><a href="?page=poll-results&amp;id=<?= $pollId ?>">Результаты опроса</a></p>
        <?php endif; ?>

        <?php if ($hasVoted): ?>
            <p class="notice" role="status">Ваш голос учтён.</p>
        <?php elseif (!$canVote): ?>
            <p>В этом опросе пока нельзя проголосовать.</p>
        <?php elseif ($userId === null): ?>
            <p><a class="button button-primary" href="?page=login">Войти, чтобы проголосовать</a></p>
        <?php else: ?>
            <?php if ($error !== null): ?>
                <p class="notice notice-error" role="alert"><?= $escape($error) ?></p>
            <?php endif; ?>
            <form class="panel" method="post" action="?page=poll&amp;id=<?= $pollId ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <?php foreach ($poll['questions'] as $questionId => $question): ?>
                    <fieldset>
                        <legend><?= $escape($question['prompt']) ?></legend>
                        <?php foreach ($question['options'] as $optionId => $label): ?>
                            <p>
                                <label class="choice">
                                    <input type="radio" name="answers[<?= (int) $questionId ?>]"
                                           value="<?= (int) $optionId ?>"
                                           <?= ($answers[$questionId] ?? null) === (int) $optionId ? 'checked' : '' ?> required>
                                    <?= $escape($label) ?>
                                </label>
                            </p>
                        <?php endforeach; ?>
                    </fieldset>
                <?php endforeach; ?>
                <button class="button button-primary" type="submit">Отправить ответы</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>

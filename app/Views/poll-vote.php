<?php
$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $escape($poll['title']) ?> — Опросы</title>
</head>
<body>
    <main>
        <a href="?page=polls">К списку опросов</a>
        <h1><?= $escape($poll['title']) ?></h1>
        <?php if ($poll['description'] !== ''): ?>
            <p><?= nl2br($escape($poll['description'])) ?></p>
        <?php endif; ?>

        <?php if ($hasVoted): ?>
            <p role="status">Твой голос учтён. Ты уже прошёл этот опрос.</p>
        <?php elseif (!$canVote): ?>
            <p>В этом опросе пока нельзя проголосовать.</p>
        <?php elseif ($userId === null): ?>
            <p><a href="?page=login">Войти, чтобы проголосовать</a></p>
        <?php else: ?>
            <?php if ($error !== null): ?>
                <p role="alert"><?= $escape($error) ?></p>
            <?php endif; ?>
            <form method="post" action="?page=poll&amp;id=<?= $pollId ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <?php foreach ($poll['questions'] as $questionId => $question): ?>
                    <fieldset>
                        <legend><?= $escape($question['prompt']) ?></legend>
                        <?php foreach ($question['options'] as $optionId => $label): ?>
                            <p>
                                <label>
                                    <input type="radio" name="answers[<?= (int) $questionId ?>]"
                                           value="<?= (int) $optionId ?>"
                                           <?= ($answers[$questionId] ?? null) === (int) $optionId ? 'checked' : '' ?> required>
                                    <?= $escape($label) ?>
                                </label>
                            </p>
                        <?php endforeach; ?>
                    </fieldset>
                <?php endforeach; ?>
                <button type="submit">Отправить ответы</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>

<?php
$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Результаты — <?= $escape($results['title']) ?></title>
</head>
<body>
    <main>
        <a href="?page=poll&amp;id=<?= $pollId ?>">К опросу</a>
        <h1>Результаты опроса «<?= $escape($results['title']) ?>»</h1>
        <p>Участников: <?= $results['participants'] ?></p>
        <?php if ($results['participants'] === 0): ?>
            <p>Пока никто не проголосовал.</p>
        <?php endif; ?>

        <?php foreach ($results['questions'] as $question): ?>
            <?php $totalVotes = array_sum(array_column($question['options'], 'votes')); ?>
            <table>
                <caption><?= $escape($question['prompt']) ?></caption>
                <thead>
                    <tr>
                        <th scope="col">Вариант ответа</th>
                        <th scope="col">Голоса</th>
                        <th scope="col">Доля</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($question['options'] as $option): ?>
                        <?php $share = $totalVotes > 0 ? 100 * $option['votes'] / $totalVotes : 0; ?>
                        <tr>
                            <th scope="row"><?= $escape($option['label']) ?></th>
                            <td><?= $option['votes'] ?></td>
                            <td><?= number_format($share, 1, ',', '') ?> %</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    </main>
</body>
</html>

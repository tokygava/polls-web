<?php
$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$documentTitle = 'Результаты — ' . $results['title'];
$currentPage = 'poll-results';
require __DIR__ . '/layout-start.php';
?>
    <main class="page page-form" id="main" tabindex="-1">
        <a class="back-link" href="?page=poll&amp;id=<?= $pollId ?>">К опросу</a>
        <h1>Результаты опроса «<?= $escape($results['title']) ?>»</h1>
        <p>Участников: <?= $results['participants'] ?></p>
        <?php if ($results['participants'] === 0): ?>
            <p class="notice">Пока никто не проголосовал.</p>
        <?php endif; ?>

        <?php foreach ($results['questions'] as $question): ?>
            <?php $totalVotes = array_sum(array_column($question['options'], 'votes')); ?>
            <table class="result-table">
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

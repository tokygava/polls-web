<?php
$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$documentTitle = 'Создать опрос — Опросы';
$currentPage = 'create-poll';
require __DIR__ . '/layout-start.php';
?>
    <main class="page page-form" id="main" tabindex="-1">
        <h1>Создать опрос</h1>
        <?php if ($errors !== []): ?>
            <ul class="notice notice-error" role="alert">
                <?php foreach ($errors as $error): ?>
                    <li><?= $escape($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form class="panel" method="post" action="?page=create-poll">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <p>
                <label for="title">Название</label>
                <input id="title" name="title" value="<?= $escape($title) ?>" maxlength="200" required>
            </p>
            <p>
                <label for="description">Описание</label>
                <textarea id="description" name="description" maxlength="2000" rows="4"><?= $escape($description) ?></textarea>
            </p>
            <div id="questions" data-max-questions="<?= $maxQuestions ?>" data-max-options="<?= $maxOptions ?>">
                <?php foreach ($questions as $questionIndex => $question): ?>
                    <fieldset data-question>
                        <legend>Вопрос <?= $questionIndex + 1 ?></legend>
                        <p>
                            <label>
                                Текст вопроса
                                <input name="questions[<?= $questionIndex ?>][prompt]"
                                       value="<?= $escape($question['prompt']) ?>" maxlength="500" required>
                            </label>
                        </p>
                        <div data-options>
                            <?php foreach ($question['options'] as $optionIndex => $option): ?>
                                <p data-option>
                                    <label>
                                        <span>Вариант <?= $optionIndex + 1 ?></span>
                                        <input name="questions[<?= $questionIndex ?>][options][]"
                                               value="<?= $escape($option) ?>" maxlength="200" required>
                                    </label>
                                    <button type="button" data-action="remove-option" hidden>Удалить вариант</button>
                                </p>
                            <?php endforeach; ?>
                        </div>
                        <div class="actions">
                            <button type="button" data-action="add-option" hidden>Добавить вариант</button>
                            <button type="button" data-action="remove-question" hidden>Удалить вопрос</button>
                        </div>
                    </fieldset>
                <?php endforeach; ?>
            </div>
            <div class="actions">
                <button class="button button-primary" type="submit">Сохранить черновик</button>
                <button type="button" id="add-question" hidden>Добавить вопрос</button>
            </div>
        </form>
    </main>
</body>
</html>

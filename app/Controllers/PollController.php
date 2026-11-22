<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/Database.php';
require_once dirname(__DIR__) . '/Models/Poll.php';

final class PollController
{
    private const MAX_QUESTIONS = 20;
    private const MAX_OPTIONS = 10;

    public function create(bool $submitted): void
    {
        $user = $_SESSION['user'] ?? null;
        if ($user === null) {
            header('Location: ?page=login', true, 303);
            return;
        }

        $csrfToken = $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
        $maxQuestions = self::MAX_QUESTIONS;
        $maxOptions = self::MAX_OPTIONS;
        $title = '';
        $description = '';
        $questions = [['prompt' => '', 'options' => ['', '']]];
        $errors = [];

        if ($submitted) {
            $providedToken = $_POST['csrf_token'] ?? null;
            if (!is_string($providedToken) || !hash_equals($csrfToken, $providedToken)) {
                http_response_code(403);
                exit('Срок действия формы истёк. Откройте её заново.');
            }

            $title = $this->readText($_POST['title'] ?? null);
            $submittedDescription = $_POST['description'] ?? '';
            $description = $this->readText($submittedDescription);
            if (!$this->validText($title, 1, 200)) {
                $errors[] = 'Введите название длиной от 1 до 200 символов.';
            }
            if (!is_string($submittedDescription) || !$this->validText($description, 0, 2000)) {
                $errors[] = 'Описание должно содержать до 2000 символов.';
            }

            $postedQuestions = $this->readQuestions($_POST['questions'] ?? null, $errors);
            if ($postedQuestions !== []) {
                $questions = $postedQuestions;
            }

            if ($errors === []) {
                $polls = new Poll(Database::connect());
                $polls->createDraft((int) $user['id'], $title, $description, $questions);
                $_SESSION['draft_saved'] = true;
                header('Location: ./', true, 303);
                return;
            }

            http_response_code(422);
        }

        require dirname(__DIR__) . '/Views/poll-create.php';
    }

    private function readQuestions($input, array &$errors): array
    {
        if (!is_array($input) || $input === [] || count($input) > self::MAX_QUESTIONS) {
            $errors[] = 'Добавьте от 1 до ' . self::MAX_QUESTIONS . ' вопросов.';
        }

        $questions = [];
        $rows = is_array($input) ? array_slice($input, 0, self::MAX_QUESTIONS) : [];
        foreach (array_values($rows) as $index => $row) {
            $number = $index + 1;
            $prompt = $this->readText($row['prompt'] ?? null);
            if (!$this->validText($prompt, 1, 500)) {
                $errors[] = "Вопрос $number должен содержать от 1 до 500 символов.";
            }

            $values = $row['options'] ?? null;
            $options = [];
            if (!is_array($values) || count($values) < 2 || count($values) > self::MAX_OPTIONS) {
                $errors[] = "Для вопроса $number нужно от 2 до " . self::MAX_OPTIONS . ' вариантов.';
            }

            if (is_array($values)) {
                foreach (array_slice($values, 0, self::MAX_OPTIONS) as $value) {
                    $options[] = $this->readText($value);
                }
            }

            foreach ($options as $optionIndex => $option) {
                if (!$this->validText($option, 1, 200)) {
                    $optionNumber = $optionIndex + 1;
                    $errors[] = "Вопрос $number, вариант $optionNumber. Введите от 1 до 200 символов.";
                }
            }

            $questions[] = ['prompt' => $prompt, 'options' => array_pad($options, 2, '')];
        }

        return $questions;
    }

    private function readText($value): string
    {
        return is_string($value) ? trim($value, " \t\n\r\v\f") : '';
    }

    private function validText(string $value, int $min, int $max): bool
    {
        return preg_match('/\A[^\x00]{' . $min . ',' . $max . '}\z/u', $value) === 1;
    }
}

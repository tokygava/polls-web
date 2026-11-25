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
        $authorId = $this->requireUserId();
        $csrfToken = $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
        $maxQuestions = self::MAX_QUESTIONS;
        $maxOptions = self::MAX_OPTIONS;
        $title = '';
        $description = '';
        $questions = [['prompt' => '', 'options' => ['', '']]];
        $errors = [];

        if ($submitted) {
            $this->checkCsrfToken();

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
                $polls->createDraft($authorId, $title, $description, $questions);
                $_SESSION['draft_saved'] = true;
                header('Location: ./', true, 303);
                return;
            }

            http_response_code(422);
        }

        require dirname(__DIR__) . '/Views/poll-create.php';
    }

    public function drafts(): void
    {
        $authorId = $this->requireUserId();
        $csrfToken = $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
        $polls = (new Poll(Database::connect()))->findDrafts($authorId);
        $isDraftList = true;

        require dirname(__DIR__) . '/Views/poll-list.php';
    }

    public function published(): void
    {
        $polls = (new Poll(Database::connect()))->findPublished();
        $isDraftList = false;

        require dirname(__DIR__) . '/Views/poll-list.php';
    }

    public function publish(): void
    {
        $authorId = $this->requireUserId();
        $this->checkCsrfToken();
        $pollId = $this->requirePollId($_POST['poll_id'] ?? null);

        $polls = new Poll(Database::connect());
        if (!$polls->publishDraft($authorId, $pollId)) {
            http_response_code(404);
            echo 'Черновик не найден.';
            return;
        }

        header('Location: ?page=polls', true, 303);
    }

    public function viewPoll(bool $submitted): void
    {
        $userId = isset($_SESSION['user']) ? (int) $_SESSION['user']['id'] : null;
        if ($submitted) {
            $userId = $this->requireUserId();
            $this->checkCsrfToken();
        }

        $pollId = $this->requirePollId($_GET['id'] ?? null);

        $polls = new Poll(Database::connect());
        $poll = $polls->findPublishedPoll($pollId);
        if ($poll === null) {
            http_response_code(404);
            echo 'Опрос не найден.';
            return;
        }

        $hasVoted = $userId !== null && $polls->hasSubmission($pollId, $userId);
        $csrfToken = $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
        $answers = [];
        $error = null;
        $canVote = $poll['questions'] !== [];
        foreach ($poll['questions'] as $question) {
            if (count($question['options']) < 2) {
                $canVote = false;
                break;
            }
        }

        if ($submitted) {
            if ($hasVoted || !$canVote) {
                http_response_code(409);
            } else {
                $rawAnswers = $_POST['answers'] ?? [];
                $answers = $this->readAnswers($rawAnswers, $poll['questions']);
                if (!is_array($rawAnswers) || count($answers) !== count($poll['questions'])
                    || count($answers) !== count($rawAnswers)) {
                    http_response_code(422);
                    $error = 'Выбери по одному варианту ответа для каждого вопроса.';
                } elseif ($polls->submitVote($pollId, $userId, $answers)) {
                    header('Location: ?page=poll&id=' . $pollId, true, 303);
                    return;
                } elseif ($polls->hasSubmission($pollId, $userId)) {
                    http_response_code(409);
                    $hasVoted = true;
                } else {
                    http_response_code(404);
                    echo 'Опрос не найден.';
                    return;
                }
            }
        }

        require dirname(__DIR__) . '/Views/poll-vote.php';
    }

    public function results(): void
    {
        $authorId = $this->requireUserId();
        $pollId = $this->requirePollId($_GET['id'] ?? null);
        $results = (new Poll(Database::connect()))->findResults($pollId, $authorId);
        if ($results === null) {
            http_response_code(404);
            echo 'Опрос не найден.';
            return;
        }

        require dirname(__DIR__) . '/Views/poll-results.php';
    }

    private function requirePollId($value): int
    {
        $pollId = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($pollId === false) {
            http_response_code(400);
            exit('Укажите корректный номер опроса.');
        }

        return $pollId;
    }

    private function readAnswers($input, array $questions): array
    {
        if (!is_array($input)) {
            return [];
        }

        $answers = [];
        foreach ($questions as $questionId => $question) {
            $optionId = filter_var($input[$questionId] ?? null, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);
            if ($optionId !== false && isset($question['options'][$optionId])) {
                $answers[$questionId] = $optionId;
            }
        }

        return $answers;
    }

    private function requireUserId(): int
    {
        $user = $_SESSION['user'] ?? null;
        if ($user === null) {
            header('Location: ?page=login', true, 303);
            exit;
        }

        return (int) $user['id'];
    }

    private function checkCsrfToken(): void
    {
        $expectedToken = $_SESSION['csrf_token'] ?? '';
        $providedToken = $_POST['csrf_token'] ?? null;

        if ($expectedToken === '' || !is_string($providedToken) || !hash_equals($expectedToken, $providedToken)) {
            http_response_code(403);
            exit('Срок действия формы истёк. Откройте её заново.');
        }
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

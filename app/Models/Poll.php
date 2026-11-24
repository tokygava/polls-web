<?php

declare(strict_types=1);

final class Poll
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function createDraft(int $authorId, string $title, string $description, array $questions): void
    {
        $this->connection->beginTransaction();

        try {
            $insertPoll = $this->connection->prepare(
                'INSERT INTO polls (author_id, title, description)
                 VALUES (:author_id, :title, :description) RETURNING id'
            );
            $insertPoll->execute(['author_id' => $authorId, 'title' => $title, 'description' => $description]);
            $pollId = $insertPoll->fetchColumn();

            $insertQuestion = $this->connection->prepare(
                'INSERT INTO poll_questions (poll_id, prompt, position)
                 VALUES (:poll_id, :prompt, :position) RETURNING id'
            );
            $insertOption = $this->connection->prepare(
                'INSERT INTO poll_options (question_id, label, position)
                 VALUES (:question_id, :label, :position)'
            );

            foreach ($questions as $questionIndex => $question) {
                $insertQuestion->execute([
                    'poll_id' => $pollId,
                    'prompt' => $question['prompt'],
                    'position' => $questionIndex + 1,
                ]);
                $questionId = $insertQuestion->fetchColumn();

                foreach ($question['options'] as $optionIndex => $option) {
                    $insertOption->execute([
                        'question_id' => $questionId,
                        'label' => $option,
                        'position' => $optionIndex + 1,
                    ]);
                }
            }

            $this->connection->commit();
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $exception;
        }
    }

    public function findDrafts(int $authorId): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, title, description
             FROM polls
             WHERE author_id = :author_id AND published_at IS NULL
             ORDER BY created_at DESC, id DESC'
        );
        $statement->execute(['author_id' => $authorId]);

        return $statement->fetchAll();
    }

    public function findPublished(): array
    {
        $statement = $this->connection->query(
            'SELECT id, title, description
             FROM polls
             WHERE published_at IS NOT NULL
             ORDER BY published_at DESC, id DESC'
        );

        return $statement->fetchAll();
    }

    public function publishDraft(int $authorId, int $pollId): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE polls
             SET published_at = CURRENT_TIMESTAMP
             WHERE id = :id AND author_id = :author_id AND published_at IS NULL'
        );
        $statement->execute(['id' => $pollId, 'author_id' => $authorId]);

        return $statement->rowCount() === 1;
    }

    public function findPublishedPoll(int $pollId): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT p.title, p.description, q.id AS question_id, q.prompt, o.id AS option_id, o.label
             FROM polls p
             LEFT JOIN poll_questions q ON q.poll_id = p.id
             LEFT JOIN poll_options o ON o.question_id = q.id
             WHERE p.id = :id AND p.published_at IS NOT NULL
             ORDER BY q.position, o.position'
        );
        $statement->execute(['id' => $pollId]);
        $rows = $statement->fetchAll();
        if ($rows === []) {
            return null;
        }

        $questions = [];
        foreach ($rows as $row) {
            $questionId = $row['question_id'];
            if ($questionId === null) {
                continue;
            }

            $questions[$questionId] ??= ['prompt' => $row['prompt'], 'options' => []];
            if ($row['option_id'] !== null) {
                $questions[$questionId]['options'][$row['option_id']] = $row['label'];
            }
        }

        return ['title' => $rows[0]['title'], 'description' => $rows[0]['description'], 'questions' => $questions];
    }

    public function hasSubmission(int $pollId, int $userId): bool
    {
        $statement = $this->connection->prepare(
            'SELECT 1 FROM poll_submissions WHERE poll_id = :poll_id AND user_id = :user_id'
        );
        $statement->execute(['poll_id' => $pollId, 'user_id' => $userId]);

        return $statement->fetchColumn() !== false;
    }

    public function submitVote(int $pollId, int $userId, array $answers): bool
    {
        $this->connection->beginTransaction();

        try {
            $insertSubmission = $this->connection->prepare(
                'INSERT INTO poll_submissions (poll_id, user_id)
                 SELECT id, :user_id FROM polls WHERE id = :poll_id AND published_at IS NOT NULL
                 ON CONFLICT (poll_id, user_id) DO NOTHING
                 RETURNING id'
            );
            $insertSubmission->execute(['poll_id' => $pollId, 'user_id' => $userId]);
            $submissionId = $insertSubmission->fetchColumn();
            if ($submissionId === false) {
                $this->connection->rollBack();
                return false;
            }

            $insertAnswer = $this->connection->prepare(
                'INSERT INTO poll_answers (submission_id, poll_id, question_id, option_id)
                 VALUES (:submission_id, :poll_id, :question_id, :option_id)'
            );
            foreach ($answers as $questionId => $optionId) {
                $insertAnswer->execute([
                    'submission_id' => $submissionId,
                    'poll_id' => $pollId,
                    'question_id' => $questionId,
                    'option_id' => $optionId,
                ]);
            }

            $this->connection->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $exception;
        }
    }
}

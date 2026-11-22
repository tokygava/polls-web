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
}

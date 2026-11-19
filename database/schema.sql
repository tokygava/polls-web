BEGIN;

CREATE TABLE users (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name VARCHAR(100) NOT NULL CHECK (btrim(name) <> ''),
    email VARCHAR(254) NOT NULL,
    password_hash TEXT NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX users_email_unique ON users (lower(email));

CREATE TABLE polls (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    author_id BIGINT NOT NULL REFERENCES users (id),
    title VARCHAR(200) NOT NULL CHECK (btrim(title) <> ''),
    description TEXT NOT NULL DEFAULT '',
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    published_at TIMESTAMPTZ
);

CREATE TABLE poll_questions (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    poll_id BIGINT NOT NULL REFERENCES polls (id) ON DELETE CASCADE,
    prompt TEXT NOT NULL CHECK (btrim(prompt) <> ''),
    position INTEGER NOT NULL CHECK (position > 0),
    UNIQUE (poll_id, position),
    UNIQUE (poll_id, id)
);

CREATE TABLE poll_options (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    question_id BIGINT NOT NULL REFERENCES poll_questions (id) ON DELETE CASCADE,
    label TEXT NOT NULL CHECK (btrim(label) <> ''),
    position INTEGER NOT NULL CHECK (position > 0),
    UNIQUE (question_id, position),
    UNIQUE (question_id, id)
);

CREATE TABLE poll_submissions (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    poll_id BIGINT NOT NULL REFERENCES polls (id) ON DELETE CASCADE,
    user_id BIGINT NOT NULL REFERENCES users (id),
    submitted_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (poll_id, user_id),
    UNIQUE (poll_id, id)
);

CREATE TABLE poll_answers (
    submission_id BIGINT NOT NULL,
    poll_id BIGINT NOT NULL,
    question_id BIGINT NOT NULL,
    option_id BIGINT NOT NULL,
    PRIMARY KEY (submission_id, question_id),
    FOREIGN KEY (poll_id, submission_id) REFERENCES poll_submissions (poll_id, id) ON DELETE CASCADE,
    FOREIGN KEY (poll_id, question_id) REFERENCES poll_questions (poll_id, id),
    FOREIGN KEY (question_id, option_id) REFERENCES poll_options (question_id, id)
);

CREATE INDEX poll_answers_question_option_idx ON poll_answers (question_id, option_id);

COMMIT;

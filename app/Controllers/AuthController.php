<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/Database.php';
require_once dirname(__DIR__) . '/Models/User.php';

final class AuthController
{
    public function register(bool $submitted): void
    {
        $this->redirectIfSignedIn();

        $fields = ['name' => '', 'email' => ''];
        $errors = [];

        if ($submitted) {
            $this->checkCsrfToken();

            $fields['name'] = trim($this->postString('name'));
            $fields['email'] = strtolower(trim($this->postString('email')));
            $password = $this->postString('password');

            if (preg_match('/\A[^\x00\p{C}]{1,100}\z/u', $fields['name']) !== 1) {
                $errors[] = 'Введите имя длиной от 1 до 100 символов.';
            }

            if (!$this->validEmail($fields['email'])) {
                $errors[] = 'Введите корректный адрес электронной почты.';
            }

            if (!$this->validPassword($password)) {
                $errors[] = 'Используйте минимум 8 символов и максимум 72 байта для пароля.';
            }

            if ($errors === []) {
                $users = new User(Database::connect());
                $user = $users->create($fields['name'], $fields['email'], password_hash($password, PASSWORD_BCRYPT));

                if ($user !== null) {
                    $this->signIn($user);
                }

                http_response_code(409);
                $errors[] = 'Аккаунт с таким адресом уже существует.';
            } else {
                http_response_code(422);
            }
        }

        $this->showForm('register', $fields, $errors);
    }

    public function login(bool $submitted): void
    {
        $this->redirectIfSignedIn();

        $fields = ['email' => ''];
        $errors = [];

        if ($submitted) {
            $this->checkCsrfToken();

            $fields['email'] = strtolower(trim($this->postString('email')));
            $password = $this->postString('password');

            if ($this->validEmail($fields['email']) && $this->validPassword($password)) {
                $users = new User(Database::connect());
                $user = $users->findByEmail($fields['email']);

                if ($user !== null && password_verify($password, $user['password_hash'])) {
                    $this->signIn($user);
                }
            }

            http_response_code(422);
            $errors[] = 'Неверный адрес электронной почты или пароль.';
        }

        $this->showForm('login', $fields, $errors);
    }

    public function logout(): void
    {
        $this->checkCsrfToken();
        $_SESSION = [];
        $this->renewSession();
        $this->redirectHome();
    }

    private function validEmail(string $email): bool
    {
        return strlen($email) <= 254 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validPassword(string $password): bool
    {
        return strlen($password) <= 72 && preg_match('/\A[^\x00]{8,}\z/u', $password) === 1;
    }

    private function postString(string $field): string
    {
        $value = $_POST[$field] ?? null;
        return is_string($value) ? $value : '';
    }

    private function checkCsrfToken(): void
    {
        $token = $_SESSION['csrf_token'] ?? '';

        if ($token === '' || !hash_equals($token, $this->postString('csrf_token'))) {
            http_response_code(403);
            exit('Срок действия формы истёк. Откройте её заново.');
        }
    }

    private function showForm(string $page, array $fields, array $errors): void
    {
        $csrfToken = $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
        require dirname(__DIR__) . '/Views/auth.php';
    }

    private function signIn(array $user): void
    {
        $this->renewSession();
        $_SESSION = ['user' => ['id' => $user['id'], 'name' => $user['name']]];
        $this->redirectHome();
    }

    private function renewSession(): void
    {
        if (!session_regenerate_id(true)) {
            http_response_code(500);
            exit('Не удалось обновить сессию.');
        }
    }

    private function redirectIfSignedIn(): void
    {
        if (isset($_SESSION['user'])) {
            $this->redirectHome();
        }
    }

    private function redirectHome(): void
    {
        header('Location: ./', true, 303);
        exit;
    }
}

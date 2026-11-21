<?php

declare(strict_types=1);

final class HomeController
{
    public function index(): void
    {
        $user = $_SESSION['user'] ?? null;
        $csrfToken = $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));

        require dirname(__DIR__) . '/Views/home.php';
    }
}

<?php

declare(strict_types=1);

final class HomeController
{
    public function index(): void
    {
        require dirname(__DIR__) . '/Views/home.php';
    }
}

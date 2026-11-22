<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Controllers/HomeController.php';
require_once dirname(__DIR__) . '/app/Controllers/AuthController.php';
require_once dirname(__DIR__) . '/app/Controllers/PollController.php';

$page = $_GET['page'] ?? 'home';
$method = $_SERVER['REQUEST_METHOD'];
$allowedMethods = [
    'home' => ['GET', 'HEAD'],
    'register' => ['GET', 'HEAD', 'POST'],
    'login' => ['GET', 'HEAD', 'POST'],
    'logout' => ['POST'],
    'create-poll' => ['GET', 'HEAD', 'POST'],
];

if (!is_string($page) || !isset($allowedMethods[$page])) {
    http_response_code(404);
    exit('Страница не найдена.');
}

if (!in_array($method, $allowedMethods[$page], true)) {
    header('Allow: ' . implode(', ', $allowedMethods[$page]));
    http_response_code(405);
    exit('Метод не поддерживается.');
}

header('Content-Type: text/html; charset=UTF-8');

$sessionStarted = session_start([
    'use_strict_mode' => 1,
    'use_only_cookies' => 1,
    'cookie_path' => '/',
    'cookie_httponly' => true,
    'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'cookie_samesite' => 'Lax',
]);

if (!$sessionStarted) {
    http_response_code(500);
    exit('Не удалось открыть сессию.');
}

if ($page === 'home') {
    (new HomeController())->index();
    return;
}

try {
    if ($page === 'create-poll') {
        (new PollController())->create($method === 'POST');
        return;
    }

    $controller = new AuthController();

    switch ($page) {
        case 'register':
            $controller->register($method === 'POST');
            break;
        case 'login':
            $controller->login($method === 'POST');
            break;
        case 'logout':
            $controller->logout();
            break;
    }
} catch (PDOException $exception) {
    error_log('Database error: ' . $exception->getCode());
    http_response_code(503);
    echo 'База данных временно недоступна.';
}

<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Controllers/HomeController.php';

$controller = new HomeController();
$controller->index();

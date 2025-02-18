<?php

declare(strict_types=1);

spl_autoload_register(function ($class) {
    require __DIR__ . "/src/$class.php";
});

set_error_handler('ErrorHandler::handleError');
set_exception_handler('ErrorHandler::handleException');

header('Content-type: application/json; charset=UTF-8');

$parts = explode('/', $_SERVER['REQUEST_URI']);

if ($parts[2] != 'investments') {
    http_response_code(404);

    exit;
}

$controller = new InvestmentController(include('src/config.php'));
$controller->processRequest($_SERVER['REQUEST_METHOD']);














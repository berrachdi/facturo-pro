<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $file = __DIR__ . '/../src/' . str_replace(['App\\', '\\'], ['', '/'], $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Core\Router;
use App\Core\Session;
use App\Controller\AuthController;
use App\Controller\DashboardController;
use App\Controller\ClientController;
use App\Controller\InvoiceController;
use App\Controller\PaymentController;

Session::start();

$router = new Router();

$router->get('/',       [DashboardController::class, 'index']);
$router->get('/login',  [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/clients',                [ClientController::class, 'list']);
$router->get('/clients/create',         [ClientController::class, 'createForm']);
$router->post('/clients/create',        [ClientController::class, 'create']);
$router->get('/clients/{id}/edit',      [ClientController::class, 'editForm']);
$router->post('/clients/{id}/edit',     [ClientController::class, 'edit']);
$router->post('/clients/{id}/archive',  [ClientController::class, 'archive']);

$router->get('/invoices',                 [InvoiceController::class, 'list']);
$router->get('/invoices/create',          [InvoiceController::class, 'createForm']);
$router->post('/invoices/create',         [InvoiceController::class, 'create']);
$router->get('/invoices/{id}',            [InvoiceController::class, 'show']);
$router->get('/invoices/{id}/edit',       [InvoiceController::class, 'editForm']);
$router->post('/invoices/{id}/edit',      [InvoiceController::class, 'edit']);
$router->post('/invoices/{id}/status',                    [InvoiceController::class,  'changeStatus']);
$router->post('/invoices/{id}/payments',                  [PaymentController::class,  'store']);
$router->post('/invoices/{id}/payments/{pid}/delete',     [PaymentController::class,  'destroy']);

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
);

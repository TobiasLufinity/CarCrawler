<?php

$loader = require '../vendor/autoload.php';

use App\Controller\Api\Cars;
use App\Controller\HomeController;
use App\Router;

$router = new Router();

// Define routes
$router->get('/', [HomeController::class, 'index']);
$router->post('/api/cars', [Cars::class, 'search']);

// Handle the current request
echo $router->resolve();
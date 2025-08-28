<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Controller\ApiController;
use App\Repository\AccountRepository;
use App\Service\TransactionService;

// Configuration
$dbFile = __DIR__ . '/database.sqlite';

// Dependency Injection Container (simple version)
$repository = new AccountRepository($dbFile);
$service = new TransactionService($repository);
$controller = new ApiController($repository, $service);

// Handle the incoming request
$controller->handleRequest();

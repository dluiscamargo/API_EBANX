<?php

namespace App\Controller;

use App\Repository\AccountRepository;
use App\Service\TransactionService;

class ApiController
{
    private AccountRepository $repository;
    private TransactionService $service;

    public function __construct(AccountRepository $repository, TransactionService $service)
    {
        $this->repository = $repository;
        $this->service = $service;
    }

    public function handleRequest(): void
    {
        header('Content-Type: application/json');

        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = parse_url($requestUri, PHP_URL_PATH);
        $path = rtrim($path, '/');

        try {
            switch (true) {
                case $requestMethod === 'POST' && $path === '/reset':
                    $this->resetAction();
                    break;
                
                case $requestMethod === 'GET' && $path === '/balance':
                    $this->balanceAction();
                    break;
                
                case $requestMethod === 'POST' && $path === '/event':
                    $this->eventAction();
                    break;

                default:
                    // A default response for unknown routes can be helpful
                    http_response_code(404);
                    echo json_encode(['error' => 'Not Found']);
                    break;
            }
        } catch (\Exception $e) {
            http_response_code(500);
            // Avoid exposing raw error messages in production
            echo json_encode(['error' => 'Internal Server Error']);
        }
    }

    private function resetAction(): void
    {
        $this->repository->reset();
        http_response_code(200);
        header('Content-Type: text/plain');
        echo 'OK';
    }

    private function balanceAction(): void
    {
        $accountId = $_GET['account_id'] ?? null;
        if (!$accountId) {
            http_response_code(400); // Bad Request is more appropriate
            echo json_encode(['error' => 'account_id is required']);
            return;
        }

        $balance = $this->service->getBalance($accountId);

        if ($balance === null) {
            http_response_code(404);
            header('Content-Type: text/plain');
            echo '0';
        } else {
            http_response_code(200);
            header('Content-Type: text/plain');
            echo (string)$balance;
        }
    }

    private function eventAction(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $type = $input['type'] ?? '';

        switch ($type) {
            case 'deposit':
                $result = $this->service->deposit($input['destination'], $input['amount']);
                http_response_code(201);
                echo json_encode(['destination' => $result]);
                break;

            case 'withdraw':
                // According to tests, a withdraw from a non-existing account is a 404.
                if ($this->service->getBalance($input['origin']) === null) {
                    http_response_code(404);
                    header('Content-Type: text/plain');
                    echo '0';
                    return;
                }
                
                $result = $this->service->withdraw($input['origin'], $input['amount']);
                http_response_code(201);
                echo json_encode(['origin' => $result]);
                break;

            case 'transfer':
                // According to tests, a transfer from a non-existing account is a 404.
                if ($this->service->getBalance($input['origin']) === null) {
                    http_response_code(404);
                    header('Content-Type: text/plain');
                    echo '0';
                    return;
                }

                $result = $this->service->transfer($input['origin'], $input['destination'], $input['amount']);
                http_response_code(201);
                echo json_encode($result);
                break;
            
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid event type']);
                break;
        }
    }
}

<?php
// EBANX API with Session-based Persistence
session_start();
header('Content-Type: application/json');

// Netlify Universal Router
$path = $_GET['path'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

class AccountManager {
    public static function reset() {
        $_SESSION['accounts'] = [];
    }
    
    public static function getBalance($accountId) {
        if (!isset($_SESSION['accounts'][$accountId])) {
            return null;
        }
        return $_SESSION['accounts'][$accountId];
    }
    
    public static function deposit($accountId, $amount) {
        if (!isset($_SESSION['accounts'][$accountId])) {
            $_SESSION['accounts'][$accountId] = 0;
        }
        $_SESSION['accounts'][$accountId] += $amount;
        return $_SESSION['accounts'][$accountId];
    }
    
    public static function withdraw($accountId, $amount) {
        if (!isset($_SESSION['accounts'][$accountId])) {
            return null;
        }
        
        if ($_SESSION['accounts'][$accountId] < $amount) {
            return false;
        }
        
        $_SESSION['accounts'][$accountId] -= $amount;
        return $_SESSION['accounts'][$accountId];
    }
    
    public static function transfer($from, $to, $amount) {
        if (!isset($_SESSION['accounts'][$from])) {
            return null;
        }
        
        if ($_SESSION['accounts'][$from] < $amount) {
            return false;
        }
        
        if (!isset($_SESSION['accounts'][$to])) {
            $_SESSION['accounts'][$to] = 0;
        }
        
        $_SESSION['accounts'][$from] -= $amount;
        $_SESSION['accounts'][$to] += $amount;
        
        return [
            'origin' => ['id' => $from, 'balance' => $_SESSION['accounts'][$from]],
            'destination' => ['id' => $to, 'balance' => $_SESSION['accounts'][$to]]
        ];
    }
}

try {
    if ($requestMethod === 'POST' && $path === '/reset') {
        AccountManager::reset();
        http_response_code(200);
        echo 'OK';
        exit();
    }
    
    if ($requestMethod === 'GET' && $path === '/balance') {
        $accountId = $_GET['account_id'] ?? null;
        $balance = AccountManager::getBalance($accountId);
        
        if ($balance === null) {
            http_response_code(404);
            echo '0';
        } else {
            http_response_code(200);
            echo (string)$balance;
        }
        exit();
    }
    
    if ($requestMethod === 'POST' && $path === '/event') {
        $input = json_decode(file_get_contents('php://input'), true);
        $type = $input['type'] ?? '';
        
        switch ($type) {
            case 'deposit':
                $balance = AccountManager::deposit($input['destination'], $input['amount']);
                http_response_code(201);
                echo json_encode(['destination' => ['id' => $input['destination'], 'balance' => $balance]]);
                break;
                
            case 'withdraw':
                $balance = AccountManager::withdraw($input['origin'], $input['amount']);
                if ($balance === null) {
                    http_response_code(404);
                    echo '0';
                } else {
                    http_response_code(201);
                    echo json_encode(['origin' => ['id' => $input['origin'], 'balance' => $balance]]);
                }
                break;
                
            case 'transfer':
                $result = AccountManager::transfer($input['origin'], $input['destination'], $input['amount']);
                if ($result === null) {
                    http_response_code(404);
                    echo '0';
                } else {
                    http_response_code(201);
                    echo json_encode($result);
                }
                break;
        }
        exit();
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?> 
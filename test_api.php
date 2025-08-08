<?php
// EBANX API with File-based Persistence
header('Content-Type: application/json');

// File for persistence
$storageFile = '/tmp/accounts.json';

// Universal routing
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($requestUri, PHP_URL_PATH);
$path = rtrim($path, '/');

class AccountManager {
    private static function readAccounts($file) {
        if (!file_exists($file)) {
            return [];
        }
        $json = file_get_contents($file);
        return json_decode($json, true) ?: [];
    }

    private static function writeAccounts($file, $accounts) {
        file_put_contents($file, json_encode($accounts));
    }

    public static function reset($file) {
        self::writeAccounts($file, []);
    }
    
    public static function getBalance($file, $accountId) {
        $accounts = self::readAccounts($file);
        if (!isset($accounts[$accountId])) {
            return null;
        }
        return $accounts[$accountId];
    }
    
    public static function deposit($file, $accountId, $amount) {
        $accounts = self::readAccounts($file);
        if (!isset($accounts[$accountId])) {
            $accounts[$accountId] = 0;
        }
        $accounts[$accountId] += $amount;
        self::writeAccounts($file, $accounts);
        return $accounts[$accountId];
    }
    
    public static function withdraw($file, $accountId, $amount) {
        $accounts = self::readAccounts($file);
        if (!isset($accounts[$accountId])) {
            return null;
        }
        
        if ($accounts[$accountId] < $amount) {
            return false;
        }
        
        $accounts[$accountId] -= $amount;
        self::writeAccounts($file, $accounts);
        return $accounts[$accountId];
    }
    
    public static function transfer($file, $from, $to, $amount) {
        $accounts = self::readAccounts($file);
        if (!isset($accounts[$from])) {
            return null;
        }
        
        if ($accounts[$from] < $amount) {
            return false;
        }
        
        if (!isset($accounts[$to])) {
            $accounts[$to] = 0;
        }
        
        $accounts[$from] -= $amount;
        $accounts[$to] += $amount;
        self::writeAccounts($file, $accounts);
        
        return [
            'origin' => ['id' => $from, 'balance' => $accounts[$from]],
            'destination' => ['id' => $to, 'balance' => $accounts[$to]]
        ];
    }
}

try {
    if ($requestMethod === 'POST' && $path === '/reset') {
        AccountManager::reset($storageFile);
        http_response_code(200);
        echo 'OK';
        exit();
    }
    
    if ($requestMethod === 'GET' && $path === '/balance') {
        $accountId = $_GET['account_id'] ?? null;
        $balance = AccountManager::getBalance($storageFile, $accountId);
        
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
                $balance = AccountManager::deposit($storageFile, $input['destination'], $input['amount']);
                http_response_code(201);
                echo json_encode(['destination' => ['id' => $input['destination'], 'balance' => $balance]]);
                break;
                
            case 'withdraw':
                $balance = AccountManager::withdraw($storageFile, $input['origin'], $input['amount']);
                if ($balance === null) {
                    http_response_code(404);
                    echo '0';
                } else {
                    http_response_code(201);
                    echo json_encode(['origin' => ['id' => $input['origin'], 'balance' => $balance]]);
                }
                break;
                
            case 'transfer':
                $result = AccountManager::transfer($storageFile, $input['origin'], $input['destination'], $input['amount']);
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
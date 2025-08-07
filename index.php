<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simple in-memory storage (no database required)
class AccountManager {
    private static $accounts = [];
    
    public static function getBalance($accountId) {
        return isset(self::$accounts[$accountId]) ? self::$accounts[$accountId] : 0;
    }
    
    public static function deposit($accountId, $amount) {
        if (!isset(self::$accounts[$accountId])) {
            self::$accounts[$accountId] = 0;
        }
        self::$accounts[$accountId] += $amount;
        return self::$accounts[$accountId];
    }
    
    public static function withdraw($accountId, $amount) {
        if (!isset(self::$accounts[$accountId])) {
            self::$accounts[$accountId] = 0;
        }
        
        if (self::$accounts[$accountId] < $amount) {
            return false; // Insufficient funds
        }
        
        self::$accounts[$accountId] -= $amount;
        return self::$accounts[$accountId];
    }
    
    public static function transfer($fromAccountId, $toAccountId, $amount) {
        if (!isset(self::$accounts[$fromAccountId])) {
            self::$accounts[$fromAccountId] = 0;
        }
        if (!isset(self::$accounts[$toAccountId])) {
            self::$accounts[$toAccountId] = 0;
        }
        
        if (self::$accounts[$fromAccountId] < $amount) {
            return false; // Insufficient funds
        }
        
        self::$accounts[$fromAccountId] -= $amount;
        self::$accounts[$toAccountId] += $amount;
        
        return [
            'origin' => ['id' => $fromAccountId, 'balance' => self::$accounts[$fromAccountId]],
            'destination' => ['id' => $toAccountId, 'balance' => self::$accounts[$toAccountId]]
        ];
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove trailing slash
$path = rtrim($path, '/');

try {
    if ($method === 'GET' && $path === '/balance') {
        $accountId = $_GET['account_id'] ?? null;
        
        if (!$accountId) {
            http_response_code(400);
            echo json_encode(['error' => 'account_id parameter is required']);
            exit();
        }
        
        $balance = AccountManager::getBalance($accountId);
        echo json_encode(['balance' => $balance]);
        
    } elseif ($method === 'POST' && $path === '/event') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON input']);
            exit();
        }
        
        $type = $input['type'] ?? '';
        $destination = $input['destination'] ?? null;
        $origin = $input['origin'] ?? null;
        $amount = $input['amount'] ?? null;
        
        if (!$amount || $amount <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Valid amount is required']);
            exit();
        }
        
        switch ($type) {
            case 'deposit':
                if (!$destination) {
                    http_response_code(400);
                    echo json_encode(['error' => 'destination is required for deposit']);
                    exit();
                }
                
                $balance = AccountManager::deposit($destination, $amount);
                echo json_encode(['destination' => ['id' => $destination, 'balance' => $balance]]);
                break;
                
            case 'withdraw':
                if (!$origin) {
                    http_response_code(400);
                    echo json_encode(['error' => 'origin is required for withdraw']);
                    exit();
                }
                
                $balance = AccountManager::withdraw($origin, $amount);
                if ($balance === false) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Insufficient funds']);
                    exit();
                }
                
                echo json_encode(['origin' => ['id' => $origin, 'balance' => $balance]]);
                break;
                
            case 'transfer':
                if (!$origin || !$destination) {
                    http_response_code(400);
                    echo json_encode(['error' => 'origin and destination are required for transfer']);
                    exit();
                }
                
                $result = AccountManager::transfer($origin, $destination, $amount);
                if ($result === false) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Insufficient funds']);
                    exit();
                }
                
                echo json_encode($result);
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid event type. Must be deposit, withdraw, or transfer']);
                exit();
        }
        
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?> 
<?php
// EBANX API with In-Memory SQLite Persistence
header('Content-Type: application/json');

// Universal routing
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($requestUri, PHP_URL_PATH);
$path = rtrim($path, '/');

class AccountManager {
    private static $pdo;

    private static function getDB() {
        if (self::$pdo) {
            return self::$pdo;
        }
        self::$pdo = new PDO('sqlite::memory:');
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$pdo->exec("CREATE TABLE IF NOT EXISTS accounts (id TEXT PRIMARY KEY, balance INTEGER)");
        return self::$pdo;
    }
    
    public static function reset() {
        $pdo = self::getDB();
        $pdo->exec("DELETE FROM accounts");
    }
    
    public static function getBalance($accountId) {
        $pdo = self::getDB();
        $stmt = $pdo->prepare("SELECT balance FROM accounts WHERE id = ?");
        $stmt->execute([$accountId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['balance'] : null;
    }
    
    public static function deposit($accountId, $amount) {
        $pdo = self::getDB();
        $balance = self::getBalance($accountId);
        if ($balance === null) {
            $stmt = $pdo->prepare("INSERT INTO accounts (id, balance) VALUES (?, ?)");
            $stmt->execute([$accountId, $amount]);
            return $amount;
        } else {
            $newBalance = $balance + $amount;
            $stmt = $pdo->prepare("UPDATE accounts SET balance = ? WHERE id = ?");
            $stmt->execute([$newBalance, $accountId]);
            return $newBalance;
        }
    }
    
    public static function withdraw($accountId, $amount) {
        $pdo = self::getDB();
        $balance = self::getBalance($accountId);
        if ($balance === null) return null;
        if ($balance < $amount) return false;
        
        $newBalance = $balance - $amount;
        $stmt = $pdo->prepare("UPDATE accounts SET balance = ? WHERE id = ?");
        $stmt->execute([$newBalance, $accountId]);
        return $newBalance;
    }
    
    public static function transfer($from, $to, $amount) {
        $pdo = self::getDB();
        $fromBalance = self::getBalance($from);
        if ($fromBalance === null) return null;
        if ($fromBalance < $amount) return false;
        
        self::withdraw($from, $amount);
        $toBalance = self::deposit($to, $amount);
        
        return [
            'origin' => ['id' => $from, 'balance' => self::getBalance($from)],
            'destination' => ['id' => $to, 'balance' => $toBalance]
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
    echo json_encode(['error' => 'Internal server error', 'message' => $e->getMessage()]);
}
?> 
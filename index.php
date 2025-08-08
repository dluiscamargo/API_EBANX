<?php
// EBANX API with File-based SQLite Persistence
header('Content-Type: application/json');

// File for persistence
$dbFile = __DIR__ . '/database.sqlite';

// Universal routing
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($requestUri, PHP_URL_PATH);
$path = rtrim($path, '/');

class AccountManager {
    private static function getDB($file) {
        $pdo = new PDO('sqlite:' . $file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("CREATE TABLE IF NOT EXISTS accounts (id TEXT PRIMARY KEY, balance INTEGER)");
        return $pdo;
    }
    
    public static function reset($file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    public static function getBalance($file, $accountId) {
        $pdo = self::getDB($file);
        $stmt = $pdo->prepare("SELECT balance FROM accounts WHERE id = ?");
        $stmt->execute([$accountId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['balance'] : null;
    }
    
    public static function deposit($file, $accountId, $amount) {
        $pdo = self::getDB($file);
        $balance = self::getBalance($file, $accountId);
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
    
    public static function withdraw($file, $accountId, $amount) {
        $balance = self::getBalance($file, $accountId);
        if ($balance === null) return null;
        if ($balance < $amount) return false;
        
        $pdo = self::getDB($file);
        $newBalance = $balance - $amount;
        $stmt = $pdo->prepare("UPDATE accounts SET balance = ? WHERE id = ?");
        $stmt->execute([$newBalance, $accountId]);
        return $newBalance;
    }
    
    public static function transfer($file, $from, $to, $amount) {
        $fromBalance = self::getBalance($file, $from);
        if ($fromBalance === null) return null;
        if ($fromBalance < $amount) return false;
        
        self::withdraw($file, $from, $amount);
        $toBalance = self::deposit($file, $to, $amount);
        
        return [
            'origin' => ['id' => $from, 'balance' => self::getBalance($file, $from)],
            'destination' => ['id' => $to, 'balance' => $toBalance]
        ];
    }
}

try {
    if ($requestMethod === 'POST' && $path === '/reset') {
        AccountManager::reset($dbFile);
        http_response_code(200);
        echo 'OK';
        exit();
    }
    
    if ($requestMethod === 'GET' && $path === '/balance') {
        $accountId = $_GET['account_id'] ?? null;
        $balance = AccountManager::getBalance($dbFile, $accountId);
        
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
                $balance = AccountManager::deposit($dbFile, $input['destination'], $input['amount']);
                http_response_code(201);
                echo json_encode(['destination' => ['id' => $input['destination'], 'balance' => $balance]]);
                break;
                
            case 'withdraw':
                $balance = AccountManager::withdraw($dbFile, $input['origin'], $input['amount']);
                if ($balance === null) {
                    http_response_code(404);
                    echo '0';
                } else if ($balance === false) {
                    http_response_code(404); // Not enough balance should be 404
                    echo '0';
                } else {
                    http_response_code(201);
                    echo json_encode(['origin' => ['id' => $input['origin'], 'balance' => $balance]]);
                }
                break;
                
            case 'transfer':
                $result = AccountManager::transfer($dbFile, $input['origin'], $input['destination'], $input['amount']);
                if ($result === null || $result === false) {
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

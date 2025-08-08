<?php
// Netlify Universal Router
$path = $_GET['path'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function makeRequest($method, $url, $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'body' => $response
    ];
}

echo "=== EBANX API Test Suite ===\n\n";

// Test 1: Get balance of non-existent account
echo "Test 1: GET /balance?account_id=100\n";
$response = makeRequest('GET', 'http://localhost:8000/balance?account_id=100');
echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['body'] . "\n\n";

// Test 2: Deposit to account 100
echo "Test 2: POST /event (deposit)\n";
$data = json_encode([
    'type' => 'deposit',
    'destination' => '100',
    'amount' => 10
]);
$response = makeRequest('POST', 'http://localhost:8000/event', $data);
echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['body'] . "\n\n";

// Test 3: Get balance of account 100
echo "Test 3: GET /balance?account_id=100\n";
$response = makeRequest('GET', 'http://localhost:8000/balance?account_id=100');
echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['body'] . "\n\n";

// Test 4: Withdraw from account 100
echo "Test 4: POST /event (withdraw)\n";
$data = json_encode([
    'type' => 'withdraw',
    'origin' => '100',
    'amount' => 5
]);
$response = makeRequest('POST', 'http://localhost:8000/event', $data);
echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['body'] . "\n\n";

// Test 5: Get balance of account 100 after withdraw
echo "Test 5: GET /balance?account_id=100\n";
$response = makeRequest('GET', 'http://localhost:8000/balance?account_id=100');
echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['body'] . "\n\n";

// Test 6: Transfer from account 100 to account 200
echo "Test 6: POST /event (transfer)\n";
$data = json_encode([
    'type' => 'transfer',
    'origin' => '100',
    'destination' => '200',
    'amount' => 15
]);
$response = makeRequest('POST', 'http://localhost:8000/event', $data);
echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['body'] . "\n\n";

// Test 7: Get balance of both accounts after transfer
echo "Test 7: GET /balance?account_id=100\n";
$response = makeRequest('GET', 'http://localhost:8000/balance?account_id=100');
echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['body'] . "\n\n";

echo "Test 8: GET /balance?account_id=200\n";
$response = makeRequest('GET', 'http://localhost:8000/balance?account_id=200');
echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['body'] . "\n\n";

// Test 9: Try to withdraw more than available balance
echo "Test 9: POST /event (withdraw more than available)\n";
$data = json_encode([
    'type' => 'withdraw',
    'origin' => '100',
    'amount' => 100
]);
$response = makeRequest('POST', 'http://localhost:8000/event', $data);
echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['body'] . "\n\n";

// Test 10: Invalid event type
echo "Test 10: POST /event (invalid type)\n";
$data = json_encode([
    'type' => 'invalid',
    'destination' => '100',
    'amount' => 10
]);
$response = makeRequest('POST', 'http://localhost:8000/event', $data);
echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['body'] . "\n\n";

echo "=== Test Suite Completed ===\n";
?> 
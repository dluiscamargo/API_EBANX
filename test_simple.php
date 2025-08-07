<?php
// Simple test script using file_get_contents
echo "=== EBANX API Simple Test ===\n\n";

// Test 1: Get balance of non-existent account
echo "Test 1: GET /balance?account_id=100\n";
$response = file_get_contents('http://localhost:8000/balance?account_id=100');
echo "Response: " . $response . "\n\n";

// Test 2: Deposit to account 100
echo "Test 2: POST /event (deposit)\n";
$data = json_encode([
    'type' => 'deposit',
    'destination' => '100',
    'amount' => 10
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $data
    ]
]);

$response = file_get_contents('http://localhost:8000/event', false, $context);
echo "Response: " . $response . "\n\n";

// Test 3: Get balance of account 100
echo "Test 3: GET /balance?account_id=100\n";
$response = file_get_contents('http://localhost:8000/balance?account_id=100');
echo "Response: " . $response . "\n\n";

// Test 4: Withdraw from account 100
echo "Test 4: POST /event (withdraw)\n";
$data = json_encode([
    'type' => 'withdraw',
    'origin' => '100',
    'amount' => 5
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $data
    ]
]);

$response = file_get_contents('http://localhost:8000/event', false, $context);
echo "Response: " . $response . "\n\n";

// Test 5: Get balance of account 100 after withdraw
echo "Test 5: GET /balance?account_id=100\n";
$response = file_get_contents('http://localhost:8000/balance?account_id=100');
echo "Response: " . $response . "\n\n";

// Test 6: Transfer from account 100 to account 200
echo "Test 6: POST /event (transfer)\n";
$data = json_encode([
    'type' => 'transfer',
    'origin' => '100',
    'destination' => '200',
    'amount' => 15
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $data
    ]
]);

$response = file_get_contents('http://localhost:8000/event', false, $context);
echo "Response: " . $response . "\n\n";

// Test 7: Get balance of both accounts
echo "Test 7: GET /balance?account_id=100\n";
$response = file_get_contents('http://localhost:8000/balance?account_id=100');
echo "Response: " . $response . "\n\n";

echo "Test 8: GET /balance?account_id=200\n";
$response = file_get_contents('http://localhost:8000/balance?account_id=200');
echo "Response: " . $response . "\n\n";

echo "=== Test Completed ===\n";
?> 
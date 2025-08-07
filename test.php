<?php
// Simple test script to verify API functionality
echo "Testing API EBANX...\n\n";

// Test 1: Get balance of non-existent account
echo "Test 1: Get balance of account 100 (should be 0)\n";
$response = file_get_contents('http://localhost/balance?account_id=100');
echo "Response: " . $response . "\n\n";

// Test 2: Deposit to account 100
echo "Test 2: Deposit 10 to account 100\n";
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

$response = file_get_contents('http://localhost/event', false, $context);
echo "Response: " . $response . "\n\n";

// Test 3: Get balance of account 100 (should be 10)
echo "Test 3: Get balance of account 100 (should be 10)\n";
$response = file_get_contents('http://localhost/balance?account_id=100');
echo "Response: " . $response . "\n\n";

// Test 4: Transfer from account 100 to account 200
echo "Test 4: Transfer 5 from account 100 to account 200\n";
$data = json_encode([
    'type' => 'transfer',
    'origin' => '100',
    'destination' => '200',
    'amount' => 5
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $data
    ]
]);

$response = file_get_contents('http://localhost/event', false, $context);
echo "Response: " . $response . "\n\n";

// Test 5: Get balance of both accounts
echo "Test 5: Get balance of account 100 (should be 5)\n";
$response = file_get_contents('http://localhost/balance?account_id=100');
echo "Response: " . $response . "\n\n";

echo "Test 6: Get balance of account 200 (should be 5)\n";
$response = file_get_contents('http://localhost/balance?account_id=200');
echo "Response: " . $response . "\n\n";

echo "Tests completed!\n";
?> 
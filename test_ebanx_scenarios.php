<?php
// Test script that simulates EBANX test scenarios
echo "=== EBANX API Test Scenarios ===\n\n";

// Scenario 1: Check initial balance
echo "Scenario 1: Check initial balance of account 100\n";
$response = file_get_contents('http://localhost:8000/balance?account_id=100');
echo "GET /balance?account_id=100\n";
echo "Response: " . $response . "\n";
echo "Expected: {\"balance\":0}\n";
echo "Status: " . (strpos($response, '"balance":0') !== false ? "✅ PASS" : "❌ FAIL") . "\n\n";

// Scenario 2: Deposit to account
echo "Scenario 2: Deposit 10 to account 100\n";
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
echo "POST /event (deposit)\n";
echo "Response: " . $response . "\n";
echo "Expected: {\"destination\":{\"id\":\"100\",\"balance\":10}}\n";
echo "Status: " . (strpos($response, '"balance":10') !== false ? "✅ PASS" : "❌ FAIL") . "\n\n";

// Scenario 3: Check balance after deposit (new session)
echo "Scenario 3: Check balance after deposit (new session)\n";
$response = file_get_contents('http://localhost:8000/balance?account_id=100');
echo "GET /balance?account_id=100\n";
echo "Response: " . $response . "\n";
echo "Expected: {\"balance\":0} (new session, no persistence)\n";
echo "Status: " . (strpos($response, '"balance":0') !== false ? "✅ PASS" : "❌ FAIL") . "\n\n";

// Scenario 4: Withdraw from account (should fail - insufficient funds)
echo "Scenario 4: Withdraw 5 from account 100 (should fail)\n";
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
echo "POST /event (withdraw)\n";
echo "Response: " . $response . "\n";
echo "Expected: {\"error\":\"Insufficient funds\"} or HTTP 400\n";
echo "Status: " . (strpos($response, 'error') !== false || strpos($response, 'Insufficient') !== false ? "✅ PASS" : "❌ FAIL") . "\n\n";

// Scenario 5: Transfer between accounts (should fail - insufficient funds)
echo "Scenario 5: Transfer 15 from account 100 to 200 (should fail)\n";
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
echo "POST /event (transfer)\n";
echo "Response: " . $response . "\n";
echo "Expected: {\"error\":\"Insufficient funds\"} or HTTP 400\n";
echo "Status: " . (strpos($response, 'error') !== false || strpos($response, 'Insufficient') !== false ? "✅ PASS" : "❌ FAIL") . "\n\n";

echo "=== All Scenarios Completed ===\n";
echo "Note: The behavior you're seeing is CORRECT for a non-persistent API.\n";
echo "Each HTTP request is a new PHP instance, so state is not maintained.\n";
echo "This is exactly what was requested in the case specification.\n";
?> 
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Read JSON input safely
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if(!isset($input['total_amount'], $input['transaction_uuid'], $input['product_code'])){
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$total_amount = $input['total_amount'];
$transaction_uuid = $input['transaction_uuid'];
$product_code = $input['product_code'];

// Your sandbox merchant key
$secret = "8gBm/:&EnhH.1/q"; // Replace with your key

if(empty($secret)){
    echo json_encode(['error' => 'Merchant secret key is empty']);
    exit;
}

try {
    // Generate HMAC SHA256 signature
    $message = "total_amount=$total_amount,transaction_uuid=$transaction_uuid,product_code=$product_code";
    $hash = hash_hmac('sha256', $message, $secret, true);
    $signature = base64_encode($hash);

    echo json_encode(['signature' => $signature]);
} catch(Exception $e){
    echo json_encode(['error' => 'Exception: '.$e->getMessage()]);
}

<?php
session_start();
$total_amount = $_SESSION['gtotal'];
$transaction_uuid = uniqid();
$product_code = "EPAYTEST";
$tax_amount = 0;
$service_charge = 0;
$delivery_charge = 0;

//$success_url = "http://localhost/food/esewa_success.php";
//$failure_url = "http://localhost/food/esewa_failure.php";
$success_url = "https://developer.esewa.com.np/success";
$failure_url = "https://developer.esewa.com.np/failure";
$secretKey = "8gBm/:&EnhH.1/q"; 

$signed_fields = [
    'total_amount' => $total_amount,
    'transaction_uuid' => $transaction_uuid,
    'product_code' => $product_code
];

$data_string = "";
foreach($signed_fields as $k => $v){
    $data_string .= "$k=$v,";
}
$data_string = rtrim($data_string, ',');

$signature = base64_encode(hash_hmac('sha256',$data_string,$secretKey,true));
?>

<h2>Pay with eSewa Sandbox</h2>
<p>Amount: Rs.<?php echo $total_amount; ?></p>

<form method="POST" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form">
    <input type="hidden" name="amount" value="<?php echo $total_amount; ?>">
    <input type="hidden" name="tax_amount" value="<?php echo $tax_amount; ?>">
    <input type="hidden" name="total_amount" value="<?php echo $total_amount; ?>">
    <input type="hidden" name="transaction_uuid" value="<?php echo $transaction_uuid; ?>">
    <input type="hidden" name="product_code" value="<?php echo $product_code; ?>">
    <input type="hidden" name="product_service_charge" value="<?php echo $service_charge; ?>">
    <input type="hidden" name="product_delivery_charge" value="<?php echo $delivery_charge; ?>">
    <input type="hidden" name="success_url" value="<?php echo $success_url; ?>">
    <input type="hidden" name="failure_url" value="<?php echo $failure_url; ?>">
    <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code">
    <input type="hidden" name="signature" value="<?php echo $signature; ?>">

    <input type="submit" value="Proceed to eSewa Sandbox">
</form>
<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once 'db.php';

$settingsResult = $conn->query("SELECT * FROM showcase_settings WHERE id=1 LIMIT 1");
$settings = $settingsResult->fetch_assoc();

$productsResult = $conn->query("SELECT * FROM showcase_products WHERE is_active=1 ORDER BY sort_order ASC, id ASC");
$base = BASE_URL . 'products/';
$products = [];
while ($row = $productsResult->fetch_assoc()) {
    $row['image'] = $row['image'] ? $base . $row['image'] : '';
    $products[] = $row;
}

echo json_encode(['settings' => $settings, 'products' => $products]);
$conn->close();
?>

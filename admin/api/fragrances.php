<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once 'db.php';

$catResult = $conn->query("SELECT * FROM fragrance_categories WHERE is_active=1 ORDER BY sort_order");
$categories = [];
while ($row = $catResult->fetch_assoc()) $categories[] = $row;

$prodResult = $conn->query("SELECT * FROM fragrances WHERE is_active=1 ORDER BY sort_order, name");
$products = [];
$base = BASE_URL . 'products/';
while ($row = $prodResult->fetch_assoc()) {
    $row['image'] = $row['image'] ? $base . $row['image'] : '';
    $products[] = $row;
}

echo json_encode(['categories' => $categories, 'products' => $products]);
$conn->close();
?>

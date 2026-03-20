<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once 'db.php';

$heroResult = $conn->query("SELECT * FROM collections_hero WHERE id=1 LIMIT 1");
$hero = $heroResult->fetch_assoc();

$base = BASE_URL . 'collections/';
if ($hero && $hero['video_file']) {
    $hero['video_file'] = $base . $hero['video_file'];
}

$productsResult = $conn->query("SELECT * FROM collections_products WHERE is_active=1 ORDER BY sort_order, id");
$products = [];
while ($row = $productsResult->fetch_assoc()) {
    $row['image'] = $row['image'] ? $base . $row['image'] : '';
    $products[] = $row;
}

echo json_encode(['hero' => $hero, 'products' => $products]);
$conn->close();
?>

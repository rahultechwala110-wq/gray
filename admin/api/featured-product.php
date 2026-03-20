<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once 'db.php';

$base = BASE_URL . 'products/';

$productsResult = $conn->query("SELECT * FROM featured_products WHERE is_active=1 ORDER BY sort_order, id");
$result = [];

while ($p = $productsResult->fetch_assoc()) {
    $p['image']  = $p['image']  ? $base . $p['image']  : '';
    $p['floral'] = $p['floral'] ? $base . $p['floral'] : '';

    $featStmt = $conn->prepare("SELECT * FROM featured_product_features WHERE product_id=? ORDER BY sort_order");
    $featStmt->bind_param("i", $p['id']);
    $featStmt->execute();
    $featResult = $featStmt->get_result();
    $features = [];
    while ($f = $featResult->fetch_assoc()) {
        $features[] = $f;
    }
    $featStmt->close();

    $p['features'] = $features;
    $result[] = $p;
}

echo json_encode($result);
$conn->close();
?>

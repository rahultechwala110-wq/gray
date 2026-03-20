<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once 'db.php';

$settingsResult = $conn->query("SELECT * FROM instagram_settings WHERE id=1 LIMIT 1");
$settings = $settingsResult->fetch_assoc();

$imagesResult = $conn->query("SELECT * FROM instagram_images WHERE is_active=1 ORDER BY sort_order ASC, id ASC");
$base = BASE_URL . 'instagram/';
$data = [];
while ($row = $imagesResult->fetch_assoc()) {
    $data[] = ['id' => $row['id'], 'image' => $base . $row['image']];
}

echo json_encode(['success' => true, 'data' => $data, 'settings' => $settings]);
$conn->close();
?>

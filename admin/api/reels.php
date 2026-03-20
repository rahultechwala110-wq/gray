<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once 'db.php';

$reelsResult = $conn->query("SELECT id, video FROM reels WHERE is_active=1 ORDER BY sort_order ASC, id ASC");
$base = BASE_URL . 'reels/';
$data = [];
while ($row = $reelsResult->fetch_assoc()) {
    $data[] = ['id' => $row['id'], 'video' => $base . $row['video']];
}

$settingsResult = $conn->query("SELECT * FROM reels_settings WHERE id=1 LIMIT 1");
$settings = $settingsResult->fetch_assoc() ?? [
    'marquee_text'    => 'GRAY',
    'marquee_color'   => '#000000',
    'marquee_opacity' => 20,
    'marquee_enabled' => 1,
];

echo json_encode(['success' => true, 'data' => $data, 'settings' => $settings]);
$conn->close();
?>

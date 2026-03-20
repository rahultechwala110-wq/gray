<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once 'db.php';

$result = $conn->query("SELECT * FROM parallax_video WHERE id=1 LIMIT 1");
$data = $result->fetch_assoc();

if ($data && $data['video_file']) {
    $data['video_file'] = BASE_URL . 'parallax/' . $data['video_file'];
}

echo json_encode($data);
$conn->close();
?>

<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once 'db.php';

$result = $conn->query("SELECT * FROM about_home WHERE id=1 LIMIT 1");
$data = $result->fetch_assoc();

if (!$data) {
    http_response_code(404);
    echo json_encode(["error" => "Not found"]);
    exit;
}

$base = BASE_URL . 'about/';
$data['small_image'] = $data['small_image'] ? $base . $data['small_image'] : '';
$data['large_image'] = $data['large_image'] ? $base . $data['large_image'] : '';

echo json_encode($data);
$conn->close();
?>

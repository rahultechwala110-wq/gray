<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once 'db.php';

$result = $conn->query("SELECT * FROM about_section WHERE id=1 LIMIT 1");
$data = $result->fetch_assoc();

if ($data) {
    $base = BASE_URL . 'about/';
    $data['image1'] = $data['image1'] ? $base . $data['image1'] : '';
    $data['image2'] = $data['image2'] ? $base . $data['image2'] : '';
}

echo json_encode($data);
$conn->close();
?>

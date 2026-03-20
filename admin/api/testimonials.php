<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once 'db.php';

$result = $conn->query("SELECT * FROM testimonials WHERE is_active=1 ORDER BY sort_order, id DESC");
$rows = [];
while ($row = $result->fetch_assoc()) $rows[] = $row;

echo json_encode($rows);
$conn->close();
?>

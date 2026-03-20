<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once 'db.php';

$result = $conn->query("SELECT * FROM hero_section WHERE id = 1");
$row = $result->fetch_assoc();
echo json_encode($row);
$conn->close();
?>

<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query("SELECT * FROM contact_info WHERE id=1 LIMIT 1");
    $row = $result->fetch_assoc();

    if ($row) {
        $row['hero_image'] = $row['hero_image']
            ? BASE_URL . 'contact/' . $row['hero_image']
            : '';
    }

    echo json_encode(['settings' => $row]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    $name    = $body['name']    ?? '';
    $email   = $body['email']   ?? '';
    $subject = $body['subject'] ?? '';
    $message = $body['message'] ?? '';

    if (!$name || !$email || !$message) {
        http_response_code(400);
        echo json_encode(['success' => false]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $subject, $message);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true]);
}

$conn->close();
?>

<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once 'db.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    http_response_code(400);
    echo json_encode(null);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM product_details WHERE slug = ? AND is_active = 1 LIMIT 1");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    http_response_code(404);
    echo json_encode(null);
    exit;
}

$base = BASE_URL . 'product-details/';
$data['image1']         = $data['image1']         ? $base . $data['image1']         : '';
$data['image2']         = $data['image2']         ? $base . $data['image2']         : '';
$data['image3']         = $data['image3']         ? $base . $data['image3']         : '';
$data['video1']         = $data['video1']         ? $base . $data['video1']         : '';
$data['video2']         = $data['video2']         ? $base . $data['video2']         : '';
$data['whisper1_image'] = $data['whisper1_image'] ? $base . $data['whisper1_image'] : '';

// key_notes string to array
$data['key_notes'] = $data['key_notes']
    ? array_filter(array_map('trim', explode(',', $data['key_notes'])))
    : [];

// Normalize newlines
$normalizeNewlines = fn($s) => $s ? str_replace(["\r\n", "\r"], "\n", $s) : '';
$data['full_description'] = $normalizeNewlines($data['full_description']);
$data['whisper1_content'] = $normalizeNewlines($data['whisper1_content']);
$data['whisper2_content'] = $normalizeNewlines($data['whisper2_content']);

echo json_encode($data);
$conn->close();
?>

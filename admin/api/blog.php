<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once 'db.php';

$settingsResult = $conn->query("SELECT * FROM blog_section_settings WHERE id=1 LIMIT 1");
$settings = $settingsResult->fetch_assoc();

$postsResult = $conn->query("
    SELECT p.id, p.title, p.slug, p.excerpt, p.image, p.created_at, c.name as category
    FROM blog_posts p
    LEFT JOIN blog_categories c ON p.category_id = c.id
    WHERE p.status = 'published'
    ORDER BY p.sort_order ASC, p.created_at DESC
    LIMIT 3
");

$base = BASE_URL . 'blog/';
$posts = [];
while ($row = $postsResult->fetch_assoc()) {
    $row['image'] = $row['image'] ? $base . $row['image'] : '';
    $row['slug']  = '/blog-details/' . $row['slug'];
    $posts[] = $row;
}

echo json_encode(['posts' => $posts, 'settings' => $settings]);
$conn->close();
?>

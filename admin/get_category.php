<?php
require_once '../session.php';
require_once '../db.php';
require_admin();

$category_id = (int)($_GET['id'] ?? 0);

if (!$category_id) {
    echo json_encode(['error' => 'Invalid category ID']);
    exit;
}

// Get category details
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();

if (!$category) {
    echo json_encode(['error' => 'Category not found']);
    exit;
}

echo json_encode($category);
?>

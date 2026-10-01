<?php
// READ — public browsing of available donations (no login required)
require_once __DIR__ . '/../config/db.php';

$sql = "SELECT d.id, d.item_name, d.quantity, d.condition_status, d.location, c.name AS category_name
        FROM donations d
        LEFT JOIN categories c ON c.id = d.category_id
        WHERE d.status = 'available'
        ORDER BY d.created_at DESC
        LIMIT 30";

$stmt = $pdo->query($sql);
header('Content-Type: application/json');
echo json_encode(['donations' => $stmt->fetchAll()]);

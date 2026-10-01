<?php
// READ — every donation this donor has ever listed, optionally filtered by status
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('donor');

$status = $_GET['status'] ?? null;
$sql = "SELECT d.*, c.name AS category_name
        FROM donations d
        LEFT JOIN categories c ON c.id = d.category_id
        WHERE d.donor_id = ?";
$params = [current_id()];

if ($status && in_array($status, ['available','claimed','completed','cancelled'], true)) {
    $sql .= " AND d.status = ?";
    $params[] = $status;
}
$sql .= " ORDER BY d.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

json_response(['donations' => $stmt->fetchAll()]);

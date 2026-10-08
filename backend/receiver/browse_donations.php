<?php
// READ — browse all available donations, with optional filters
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_approved_institution($pdo);

$categoryId = $_GET['category_id'] ?? null;
$level      = $_GET['education_level'] ?? null;
$search     = $_GET['q'] ?? null;

$sql = "SELECT d.*, c.name AS category_name, don.full_name AS donor_name, don.location AS donor_location
        FROM donations d
        JOIN donors don ON don.id = d.donor_id
        LEFT JOIN categories c ON c.id = d.category_id
        WHERE d.status = 'available'";
$params = [];

if ($categoryId) { $sql .= " AND d.category_id = ?"; $params[] = $categoryId; }
if ($level)      { $sql .= " AND d.education_level = ?"; $params[] = $level; }
if ($search)     { $sql .= " AND d.item_name LIKE ?"; $params[] = "%$search%"; }

$sql .= " ORDER BY d.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

json_response(['donations' => $stmt->fetchAll()]);

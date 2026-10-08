<?php
// DELETE — institution withdraws/cancels a requirement it posted
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('institution');

$reqId = (int) ($_POST['requirement_id'] ?? $_GET['requirement_id'] ?? 0);

$check = $pdo->prepare("SELECT institution_id FROM requirements WHERE id = ?");
$check->execute([$reqId]);
$row = $check->fetch();

if (!$row || (int) $row['institution_id'] !== current_id()) {
    json_response(['error' => 'Requirement not found.'], 404);
}

$stmt = $pdo->prepare("UPDATE requirements SET status = 'cancelled' WHERE id = ?");
$stmt->execute([$reqId]);

json_response(['success' => true, 'message' => 'Requirement cancelled.']);

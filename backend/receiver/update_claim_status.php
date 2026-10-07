<?php
// UPDATE — institution moves a claim forward: accepted → preparing → collected
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_approved_institution($pdo);

$claimId   = (int) ($_POST['claim_id'] ?? 0);
$newStatus = $_POST['status'] ?? '';   // 'preparing' | 'collected'

$allowed = ['preparing', 'collected'];
if (!in_array($newStatus, $allowed, true)) {
    json_response(['error' => 'Invalid status.'], 400);
}

// Ownership check — a receiver can only update their own claims
$check = $pdo->prepare("SELECT institution_id FROM claims WHERE id = ?");
$check->execute([$claimId]);
$claim = $check->fetch();

if (!$claim || (int) $claim['institution_id'] !== current_id()) {
    json_response(['error' => 'Claim not found.'], 404);
}

$notes = [
    'preparing' => 'Institution is preparing for collection.',
    'collected' => 'Item collected / delivered.',
];
log_tracking_event($pdo, $claimId, $newStatus, $notes[$newStatus]);

json_response(['success' => true, 'status' => $newStatus]);

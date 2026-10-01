<?php
// DELETE — donor cancels a donation they listed (only if it hasn't been claimed yet)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('donor');

$donationId = (int) ($_POST['donation_id'] ?? $_GET['donation_id'] ?? 0);

// Ownership check — a donor can only ever touch their own donation
$check = $pdo->prepare("SELECT donor_id, status FROM donations WHERE id = ?");
$check->execute([$donationId]);
$donation = $check->fetch();

if (!$donation || (int) $donation['donor_id'] !== current_id()) {
    json_response(['error' => 'Donation not found.'], 404);
}
if ($donation['status'] === 'completed') {
    json_response(['error' => 'A completed donation cannot be cancelled.'], 409);
}

$stmt = $pdo->prepare("UPDATE donations SET status = 'cancelled' WHERE id = ?");
$stmt->execute([$donationId]);

json_response(['success' => true, 'message' => 'Donation cancelled.']);

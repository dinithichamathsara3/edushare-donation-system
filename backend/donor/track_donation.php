<?php
// READ — donor-tracking.html: full timeline for one claim
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('donor');

$claimId = (int) ($_GET['claim_id'] ?? 0);

// Ownership check — a donor can only track their own claims
$claim = $pdo->prepare(
    "SELECT c.*, d.item_name, d.quantity AS donation_quantity, i.institution_name
     FROM claims c
     JOIN donations d ON d.id = c.donation_id
     JOIN institutions i ON i.id = c.institution_id
     WHERE c.id = ? AND c.donor_id = ?"
);
$claim->execute([$claimId, current_id()]);
$claim = $claim->fetch();

if (!$claim) {
    json_response(['error' => 'Claim not found.'], 404);
}

$events = $pdo->prepare(
    "SELECT status, note, created_at FROM tracking_events
     WHERE claim_id = ? ORDER BY created_at ASC"
);
$events->execute([$claimId]);

json_response([
    'claim'    => $claim,
    'timeline' => $events->fetchAll(),
]);

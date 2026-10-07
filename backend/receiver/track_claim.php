<?php
// READ — receiver-tracking.html: full timeline for one claim
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('institution');

$claimId = (int) ($_GET['claim_id'] ?? 0);

// Ownership check — an institution can only track its own claims
$claim = $pdo->prepare(
    "SELECT c.*, d.item_name, d.quantity AS donation_quantity, don.full_name AS donor_name
     FROM claims c
     JOIN donations d ON d.id = c.donation_id
     JOIN donors don ON don.id = c.donor_id
     WHERE c.id = ? AND c.institution_id = ?"
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

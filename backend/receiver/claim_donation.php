<?php
// CREATE — an institution claims an available donation
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_approved_institution($pdo);

$donationId    = (int) ($_POST['donation_id'] ?? 0);
$requirementId = (int) ($_POST['requirement_id'] ?? 0) ?: null;
$qty           = (int) ($_POST['quantity'] ?? 1);

$donation = $pdo->prepare("SELECT * FROM donations WHERE id = ? AND status = 'available'");
$donation->execute([$donationId]);
$donation = $donation->fetch();

if (!$donation) {
    json_response(['error' => 'This donation is no longer available.'], 409);
}

$pdo->beginTransaction();
try {
    $pdo->prepare(
        "INSERT INTO claims (donation_id, requirement_id, institution_id, donor_id, quantity_claimed, status)
         VALUES (?,?,?,?,?, 'in_progress')"
    )->execute([$donationId, $requirementId, current_id(), $donation['donor_id'], $qty]);

    $claimId = (int) $pdo->lastInsertId();
    log_tracking_event($pdo, $claimId, 'offered', 'Donor offered this donation.');
    log_tracking_event($pdo, $claimId, 'accepted', 'Accepted by the institution.');

    $pdo->prepare("UPDATE donations SET status = 'claimed' WHERE id = ?")->execute([$donationId]);

    if ($requirementId) {
        $pdo->prepare(
            "UPDATE requirements SET quantity_received = quantity_received + ? WHERE id = ?"
        )->execute([$qty, $requirementId]);
    }

    add_notification(
        $pdo, 'donor', (int) $donation['donor_id'],
        'Your donation was claimed',
        $donation['item_name'] . ' was claimed by an institution.'
    );

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    json_response(['error' => 'Could not complete the claim.'], 500);
}

json_response(['success' => true, 'message' => 'Donation claimed.']);

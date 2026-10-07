<?php
// UPDATE — admin marks a claim as completed; this is what triggers point awards
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('admin');

$claimId = (int) ($_POST['claim_id'] ?? 0);

$claim = $pdo->prepare(
    "SELECT c.*, r.urgency FROM claims c
     LEFT JOIN requirements r ON r.id = c.requirement_id
     WHERE c.id = ?"
);
$claim->execute([$claimId]);
$claim = $claim->fetch();

if (!$claim) {
    json_response(['error' => 'Claim not found.'], 404);
}

$pdo->beginTransaction();
try {
    $pdo->prepare("UPDATE claims SET status = 'completed', completed_at = NOW() WHERE id = ?")
        ->execute([$claimId]);
    log_tracking_event($pdo, $claimId, 'completed', 'Donation confirmed complete by admin.');

    $pdo->prepare("UPDATE donations SET status = 'completed' WHERE id = ?")
        ->execute([$claim['donation_id']]);

    if ($claim['requirement_id']) {
        $pdo->prepare("UPDATE requirements SET status = 'completed' WHERE id = ? AND quantity_received >= quantity_needed")
            ->execute([$claim['requirement_id']]);
    }

    // Points: 10 per item, +25 bonus if it fulfilled an urgent requirement.
    $points = 10 * (int) $claim['quantity_claimed'];
    $reason = 'Completed donation (' . $claim['quantity_claimed'] . ' item(s))';
    if ($claim['urgency'] === 'urgent') {
        $points += 25;
        $reason .= ' + urgent request bonus';
    }
    award_points($pdo, (int) $claim['donor_id'], $points, $reason, current_id());

    add_notification(
        $pdo, 'donor', (int) $claim['donor_id'],
        'Donation completed',
        "You earned {$points} points for a completed donation."
    );

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    json_response(['error' => 'Could not complete the claim.'], 500);
}

json_response(['success' => true, 'points_awarded' => $points]);

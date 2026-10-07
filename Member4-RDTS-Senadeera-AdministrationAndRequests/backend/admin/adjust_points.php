<?php
// UPDATE — admin manually adjusts a donor's points (dispute resolution, corrections)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('admin');

$donorId = (int) ($_POST['donor_id'] ?? 0);
$points  = (int) ($_POST['points'] ?? 0);
$reason  = clean($_POST['reason'] ?? 'Manual adjustment');

if (!$donorId || $points === 0) {
    json_response(['error' => 'Donor and a non-zero point value are required.'], 400);
}

award_points($pdo, $donorId, $points, $reason, current_id());

json_response(['success' => true, 'message' => 'Points adjusted.']);

<?php
// READ — donor dashboard: quick stats + recent activity
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('donor');
$donorId = current_id();

$stats = $pdo->prepare(
    "SELECT
        COUNT(*) AS total_donations,
        SUM(status = 'claimed')   AS accepted,
        SUM(status = 'completed') AS completed
     FROM donations WHERE donor_id = ?"
);
$stats->execute([$donorId]);
$summary = $stats->fetch();

$recent = $pdo->prepare(
    "SELECT id, item_name, quantity, status, created_at
     FROM donations WHERE donor_id = ? ORDER BY created_at DESC LIMIT 5"
);
$recent->execute([$donorId]);
$recentDonations = $recent->fetchAll();

$points = $pdo->prepare("SELECT points FROM donors WHERE id = ?");
$points->execute([$donorId]);
$myPoints = (int) $points->fetchColumn();

// From here, either render an HTML view with these variables, or return JSON for a fetch()-driven front end:
json_response([
    'name' => $_SESSION['user_name'],
    'summary' => $summary,
    'recent_donations' => $recentDonations,
    'points' => $myPoints,
]);

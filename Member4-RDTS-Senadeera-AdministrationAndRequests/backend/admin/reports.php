<?php
// READ — platform-wide dashboard stats and reports
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

$totals = [
    'total_donors'          => (int) $pdo->query("SELECT COUNT(*) FROM donors")->fetchColumn(),
    'verified_institutions' => (int) $pdo->query("SELECT COUNT(*) FROM institutions WHERE status='approved'")->fetchColumn(),
    'pending_verifications' => (int) $pdo->query("SELECT COUNT(*) FROM institutions WHERE status='pending'")->fetchColumn(),
    'available_donations'   => (int) $pdo->query("SELECT COUNT(*) FROM donations WHERE status='available'")->fetchColumn(),
    'pending_requirements'  => (int) $pdo->query("SELECT COUNT(*) FROM requirements WHERE status='pending'")->fetchColumn(),
    'urgent_requirements'   => (int) $pdo->query("SELECT COUNT(*) FROM requirements WHERE urgency='urgent' AND status != 'completed'")->fetchColumn(),
    'active_claims'         => (int) $pdo->query("SELECT COUNT(*) FROM claims WHERE status='in_progress'")->fetchColumn(),
    'completed_donations'   => (int) $pdo->query("SELECT COUNT(*) FROM donations WHERE status='completed'")->fetchColumn(),
];

$byMonth = $pdo->query(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total
     FROM donations WHERE status = 'completed'
     GROUP BY month ORDER BY month DESC LIMIT 6"
)->fetchAll();

$byCategory = $pdo->query(
    "SELECT c.name, COUNT(d.id) AS total
     FROM categories c LEFT JOIN donations d ON d.category_id = c.id
     GROUP BY c.id ORDER BY total DESC"
)->fetchAll();

json_response([
    'totals'      => $totals,
    'by_month'    => $byMonth,
    'by_category' => $byCategory,
]);

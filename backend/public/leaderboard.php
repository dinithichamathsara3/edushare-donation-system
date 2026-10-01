<?php
// READ — monthly best donor leaderboard (points earned this calendar month)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$stmt = $pdo->query(
    "SELECT d.id, d.full_name, d.location, COALESCE(SUM(pl.points), 0) AS points_this_month
     FROM donors d
     LEFT JOIN points_ledger pl
        ON pl.donor_id = d.id
        AND DATE_FORMAT(pl.created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
     GROUP BY d.id
     ORDER BY points_this_month DESC
     LIMIT 20"
);

json_response(['leaderboard' => $stmt->fetchAll()]);

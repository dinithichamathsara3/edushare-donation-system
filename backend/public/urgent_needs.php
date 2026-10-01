<?php
// READ — public list of urgent, unmet institution requirements
require_once __DIR__ . '/../config/db.php';

$stmt = $pdo->query(
    "SELECT r.id, r.item_name, r.quantity_needed, r.quantity_received, r.required_date,
            i.institution_name, i.city
     FROM requirements r
     JOIN institutions i ON i.id = r.institution_id
     WHERE r.urgency = 'urgent' AND r.status IN ('pending','active')
     ORDER BY r.required_date ASC
     LIMIT 20"
);

header('Content-Type: application/json');
echo json_encode(['urgent_needs' => $stmt->fetchAll()]);

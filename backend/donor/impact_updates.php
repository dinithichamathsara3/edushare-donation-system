<?php
// READ — a donor's impact updates (photos of how their donations were used)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('donor');

$stmt = $pdo->prepare(
    "SELECT iu.photo_path, iu.caption, iu.created_at,
            d.item_name, d.quantity, i.institution_name
     FROM impact_updates iu
     JOIN claims c ON c.id = iu.claim_id
     JOIN donations d ON d.id = c.donation_id
     JOIN institutions i ON i.id = c.institution_id
     WHERE c.donor_id = ?
     ORDER BY iu.created_at DESC"
);
$stmt->execute([current_id()]);

json_response(['impact_updates' => $stmt->fetchAll()]);

<?php
// UPDATE — admin approves, rejects, or requests more info for an institution
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('admin');

$institutionId = (int) ($_POST['institution_id'] ?? 0);
$decision       = $_POST['decision'] ?? '';               // 'approved' | 'rejected' | 'info_required'

$allowed = ['approved', 'rejected', 'info_required'];
if (!in_array($decision, $allowed, true) || !$institutionId) {
    json_response(['error' => 'Invalid request.'], 400);
}

$stmt = $pdo->prepare("UPDATE institutions SET status = ? WHERE id = ?");
$stmt->execute([$decision, $institutionId]);

$messages = [
    'approved'      => 'Your institution has been verified. You can now log in and post requirements.',
    'rejected'      => 'Your institution registration was not approved. Please contact support for details.',
    'info_required' => 'We need more information to verify your institution. Please check your registration details.',
];
add_notification($pdo, 'institution', $institutionId, 'Verification update', $messages[$decision]);

json_response(['success' => true, 'status' => $decision]);

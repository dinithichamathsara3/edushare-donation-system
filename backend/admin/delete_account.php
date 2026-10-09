<?php
// DELETE — admin removes a donor or institution account (e.g. fraud, spam, on request)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

$accountType = $_POST['account_type'] ?? '';   // 'donor' | 'institution'
$accountId   = (int) ($_POST['account_id'] ?? 0);

if (!in_array($accountType, ['donor', 'institution'], true) || !$accountId) {
    json_response(['error' => 'Invalid request.'], 400);
}

$table = $accountType === 'donor' ? 'donors' : 'institutions';

// Foreign keys are ON DELETE CASCADE, so related donations/requirements/claims are cleaned up too.
$stmt = $pdo->prepare("DELETE FROM {$table} WHERE id = ?");
$stmt->execute([$accountId]);

json_response(['success' => true, 'message' => ucfirst($accountType) . ' account removed.']);

<?php
/** General-purpose helpers used across modules. */

function clean(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void {
    header("Location: $path");
    exit;
}

/** Save an uploaded file safely and return its stored path, or null if no file given. */
function handle_upload(string $field, string $subfolder): ?string {
    if (empty($_FILES[$field]['name'])) {
        return null;
    }
    $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        throw new InvalidArgumentException('Unsupported file type: ' . $ext);
    }
    if ($_FILES[$field]['size'] > 5 * 1024 * 1024) { // 5MB limit
        throw new InvalidArgumentException('File is too large (max 5MB).');
    }
    $filename = uniqid($subfolder . '_', true) . '.' . $ext;
    $destDir  = __DIR__ . '/../uploads/' . $subfolder . '/';
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }
    move_uploaded_file($_FILES[$field]['tmp_name'], $destDir . $filename);
    return 'uploads/' . $subfolder . '/' . $filename;
}

function add_notification(PDO $pdo, string $userType, int $userId, string $title, string $message): void {
    $stmt = $pdo->prepare(
        "INSERT INTO notifications (user_type, user_id, title, message) VALUES (?,?,?,?)"
    );
    $stmt->execute([$userType, $userId, $title, $message]);
}

function award_points(PDO $pdo, int $donorId, int $points, string $reason, ?int $adminId = null): void {
    $pdo->prepare(
        "INSERT INTO points_ledger (donor_id, points, reason, awarded_by_admin_id) VALUES (?,?,?,?)"
    )->execute([$donorId, $points, $reason, $adminId]);

    $pdo->prepare("UPDATE donors SET points = points + ? WHERE id = ?")
        ->execute([$points, $donorId]);
}

/** Log one step in a claim's tracking timeline. */
function log_tracking_event(PDO $pdo, int $claimId, string $status, string $note = ''): void {
    $pdo->prepare(
        "INSERT INTO tracking_events (claim_id, status, note) VALUES (?,?,?)"
    )->execute([$claimId, $status, $note]);
}

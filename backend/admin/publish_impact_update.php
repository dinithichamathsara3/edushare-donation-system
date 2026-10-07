<?php
// CREATE — admin uploads a photo + caption showing a donor how their gift was used
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/impact_upload.html');
}

$claimId = (int) ($_POST['claim_id'] ?? 0);
$caption = clean($_POST['caption'] ?? '');

$claim = $pdo->prepare("SELECT donor_id FROM claims WHERE id = ? AND status = 'completed'");
$claim->execute([$claimId]);
$claim = $claim->fetch();

if (!$claim) {
    redirect('/admin/impact_upload.html?error=' . urlencode('Choose a completed claim to attach a photo to.'));
}

try {
    $photo = handle_upload('photo', 'impact_photos');
} catch (InvalidArgumentException $e) {
    redirect('/admin/impact_upload.html?error=' . urlencode($e->getMessage()));
}
if (!$photo) {
    redirect('/admin/impact_upload.html?error=' . urlencode('A photo is required.'));
}

$stmt = $pdo->prepare(
    "INSERT INTO impact_updates (claim_id, photo_path, caption, published_by_admin_id) VALUES (?,?,?,?)"
);
$stmt->execute([$claimId, $photo, $caption, current_id()]);

add_notification(
    $pdo, 'donor', (int) $claim['donor_id'],
    'New impact update',
    'See how your donation made a difference — a new photo update was just posted.'
);

redirect('/admin/impact_upload.html?success=' . urlencode('Impact update published.'));

<?php
// CREATE — admin adds a new donation category
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/categories.php');
}

$name = clean($_POST['name'] ?? '');
$desc = clean($_POST['description'] ?? '');

if ($name === '') {
    redirect('/admin/categories.php?error=' . urlencode('Category name is required.'));
}

$stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
try {
    $stmt->execute([$name, $desc]);
} catch (PDOException $e) {
    redirect('/admin/categories.php?error=' . urlencode('That category already exists.'));
}

redirect('/admin/categories.php?success=' . urlencode('Category added.'));

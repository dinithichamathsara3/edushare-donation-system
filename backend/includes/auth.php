<?php
/**
 * Session + authentication helpers, shared by every module.
 * Include this AFTER config/db.php on every protected page.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Log a user in by writing their id/role/name into the session. */
function login_session(string $role, int $id, string $name): void {
    $_SESSION['user_role'] = $role;   // 'donor' | 'institution' | 'admin'
    $_SESSION['user_id']   = $id;
    $_SESSION['user_name'] = $name;
}

function logout_session(): void {
    $_SESSION = [];
    session_destroy();
}

function current_role(): ?string {
    return $_SESSION['user_role'] ?? null;
}

function current_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function is_logged_in(): bool {
    return isset($_SESSION['user_role'], $_SESSION['user_id']);
}

/**
 * Call at the top of any page that requires a specific role.
 * Redirects to the matching login page if the check fails.
 */
function require_role(string $role): void {
    if (current_role() !== $role) {
        $login = $role === 'admin' ? '/edushare/frontend/admin-login.html' : '/edushare/frontend/login.html';
        header('Location: ' . $login . '?error=please_log_in');
        exit;
    }
}

/** Institutions must also be approved before using receiver-only actions. */
function require_approved_institution(PDO $pdo): void {
    require_role('institution');
    $stmt = $pdo->prepare("SELECT status FROM institutions WHERE id = ?");
    $stmt->execute([current_id()]);
    $status = $stmt->fetchColumn();
    if ($status !== 'approved') {
        header('Location: /edushare/frontend/login.html?error=Your+institution+is+not+approved+yet');
        exit;
    }
}

function json_response(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

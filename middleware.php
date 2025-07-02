<?php
require_once 'auth.php';

function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit();
    }
}

function require_role($role) {
    require_login();
    
    if (!has_role($role)) {
        log_action($_SESSION['user_id'], 'unauthorized_access', "Attempted to access {$_SERVER['REQUEST_URI']}", $_SERVER['REMOTE_ADDR']);
        http_response_code(403);
        die('Access denied. You do not have permission to access this page.');
    }
}

function require_admin() {
    require_role('admin');
}

function require_staff() {
    require_role('staff');
}

/**
 * Delete users who are inactive (no login for 3 days) or deactivated for more than 3 days.
 * Should be called at the start of admin pages or via a scheduled task.
 */
function delete_expired_users($pdo) {
    // Find users disabled for more than 12 hours
    $stmt = $pdo->query("SELECT id, username FROM users WHERE disabled = 1 AND disabled_at IS NOT NULL AND disabled_at < DATE_SUB(NOW(), INTERVAL 12 HOUR)");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as $user) {
        // Save audit record
        $audit = $pdo->prepare("INSERT INTO audit_deleted_users (user_id, username, deleted_at, reason) VALUES (?, ?, NOW(), ?)");
        $audit->execute([$user['id'], $user['username'], 'Deleted after 12 hours of being disabled']);

        // Delete logs for this user
        $del_logs = $pdo->prepare("DELETE FROM system_logs WHERE user_id = ?");
        $del_logs->execute([$user['id']]);

        // Delete the user
        $del_user = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $del_user->execute([$user['id']]);
    }
}

/**
 * Delete users who have not logged in for 3 days (inactive accounts).
 */
function delete_inactive_users($pdo) {
    // Find users who have not logged in for 3 days and are not disabled
    $stmt = $pdo->query("SELECT id, username FROM users WHERE (last_login IS NOT NULL AND last_login < DATE_SUB(NOW(), INTERVAL 3 DAY)) AND disabled = 0");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as $user) {
        // Save audit record
        $audit = $pdo->prepare("INSERT INTO audit_deleted_users (user_id, username, deleted_at, reason) VALUES (?, ?, NOW(), ?)");
        $audit->execute([$user['id'], $user['username'], 'Deleted after 3 days of inactivity']);

        // Delete logs for this user
        $del_logs = $pdo->prepare("DELETE FROM system_logs WHERE user_id = ?");
        $del_logs->execute([$user['id']]);

        // Delete the user
        $del_user = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $del_user->execute([$user['id']]);
    }
}

/**
 * Delete a user account by user ID.
 * @param PDO $pdo
 * @param int $user_id
 * @return bool
 */
function delete_user_by_id($pdo, $user_id) {
    // Prevent deleting yourself
    if (isset($_SESSION['user_id']) && $user_id == $_SESSION['user_id']) {
        return false;
    }
    // Delete logs first
    $stmt = $pdo->prepare("DELETE FROM system_logs WHERE user_id = ?");
    $stmt->execute([$user_id]);
    // Now delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    return $stmt->execute([$user_id]);
}

function has_permission($perm) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') return true;
    if (!isset($_SESSION['permissions'])) return false;
    return in_array($perm, $_SESSION['permissions']);
}
?>
<?php
require_once 'db.php';
session_start();

date_default_timezone_set('Asia/Manila'); // or your preferred timezone

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check if user has specific role
function has_role($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Login function
function login($username, $password, $two_factor_code = null) {
    global $pdo;

    // Fetch user by username
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        // Check if account is locked (60 seconds from last failed attempt)
        if (
            $user['login_attempts'] >= 3 &&
            !empty($user['last_failed_attempt']) &&
            strtotime($user['last_failed_attempt']) > time() - 60
        ) {
            log_action(null, 'login_attempt', 'Account locked due to too many failed attempts', $_SERVER['REMOTE_ADDR']);
            return ['success' => false, 'message' => 'Account locked for 1 minute due to too many failed attempts'];
        }

        // Verify password
        if (password_verify($password, $user['password'])) {
            // For admin, verify 2FA if enabled
            if ($user['role'] === 'admin' && $user['two_factor_secret']) {
                if ($two_factor_code === null) {
                    return ['success' => false, 'require_2fa' => true];
                }

                require_once 'TwoFactor.php';
                $twoFactor = new TwoFactor();
                if (!$twoFactor->verifyCode($user['two_factor_secret'], $two_factor_code)) {
                    increment_login_attempts($user['id']);
                    log_action($user['id'], 'login_failed', 'Invalid 2FA code', $_SERVER['REMOTE_ADDR']);
                    return ['success' => false, 'message' => 'Invalid 2FA code'];
                }
            }

            // Check if account is active
            if (!$user['is_active']) {
                log_action($user['id'], 'login_attempt', 'Attempt to login to inactive account', $_SERVER['REMOTE_ADDR']);
                return ['success' => false, 'message' => 'Account is inactive'];
            }

            // Reset login attempts and update last login or updated_at
            $updateQuery = "UPDATE users SET login_attempts = 0, last_failed_attempt = NULL";
            $columnCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'last_login'")->fetch();
            if ($columnCheck) {
                $updateQuery .= ", last_login = NOW()";
            } else {
                $updateQuery .= ", updated_at = NOW()";
            }
            $updateQuery .= " WHERE id = ?";
            $stmt = $pdo->prepare($updateQuery);
            $stmt->execute([$user['id']]);

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['permissions'] = $user['permissions'] ? explode(',', $user['permissions']) : [];
            log_action($user['id'], 'login_success', 'User logged in', $_SERVER['REMOTE_ADDR']);
            return ['success' => true];
        } else {
            // Increment failed attempts if user exists
            if ($user) {
                increment_login_attempts($user['id']);
            }

            // Fetch updated user data
            if ($user) {
                $user_id = $user['id'];
                $stmt = $pdo->prepare("SELECT login_attempts, last_failed_attempt FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user_attempts = $stmt->fetch();

                // Check if account should now be locked
                if (
                    $user_attempts && $user_attempts['login_attempts'] >= 3 &&
                    !empty($user_attempts['last_failed_attempt']) &&
                    strtotime($user_attempts['last_failed_attempt']) > time() - 60
                ) {
                    log_action($user_id, 'login_attempt', 'Account locked due to too many failed attempts', $_SERVER['REMOTE_ADDR']);
                    return ['success' => false, 'message' => 'Account locked for 1 minute due to too many failed attempts'];
                }
            }
        }
    }

    log_action($user ? $user['id'] : null, 'login_failed', 'Invalid credentials', $_SERVER['REMOTE_ADDR']);
    return ['success' => false, 'message' => 'Invalid username or password'];
}

function increment_login_attempts($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET login_attempts = login_attempts + 1, last_failed_attempt = NOW() WHERE id = ?");
    $stmt->execute([$user_id]);
}

// Logout function
function logout() {
    log_action($_SESSION['user_id'], 'logout', 'User logged out', $_SERVER['REMOTE_ADDR']);
    session_destroy();
    header('Location: login.php');
    exit();
}

// Check inactive accounts
function check_inactive_accounts() {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET is_active = FALSE WHERE last_login < DATE_SUB(NOW(), INTERVAL 3 DAY) AND is_active = TRUE");
    $stmt->execute();
}

// (Optional) Remove or fix attempt_login if not used elsewhere
?>
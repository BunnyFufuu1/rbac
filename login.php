<?php
require_once 'auth.php';
require_once 'db.php';

// Add this helper function near the top
function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ipList[0]);
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }
}

date_default_timezone_set('Asia/Manila'); // or your preferred timezone

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$require_2fa = false;
$username = '';
$lock_remaining = 0;
$max_attempts = 3;
$remaining_attempts = $max_attempts;

// Always check lockout based on the username being entered
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $two_factor_code = $_POST['two_factor_code'] ?? null;

    // Fetch user for lockout check
    $stmt = $pdo->prepare("SELECT login_attempts, last_failed_attempt FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Default attempts
    $remaining_attempts = $user ? max(0, $max_attempts - (int)$user['login_attempts']) : $max_attempts;

    // Only process login if not locked
    $is_locked = false;
    if ($user && $user['login_attempts'] >= $max_attempts && !empty($user['last_failed_attempt'])) {
        $lock_until = strtotime($user['last_failed_attempt']) + 60;
        $now = time();
        $lock_remaining = $lock_until - $now;
        if ($lock_remaining > 0) {
            $remaining_attempts = 0;
            $is_locked = true;
        } else {
            // Lock expired, allow login and reset attempts
            $lock_remaining = 0;
            $remaining_attempts = $max_attempts;
            $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0, last_failed_attempt = NULL WHERE username = ?");
            $stmt->execute([$username]);
            $user['login_attempts'] = 0;
            $user['last_failed_attempt'] = null;
            $error = '';
        }
    }

    if (!$is_locked) {
        $result = login($username, $password, $two_factor_code);

        // Fetch user ID for logging
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user_row = $stmt->fetch();
        $user_id = $user_row ? $user_row['id'] : null;

        // Log every login attempt (success or fail)
        if (isset($result['success']) && $result['success']) {
            // Successful login
            log_action($user_id, 'login_success', 'User logged in', get_client_ip());
        } else {
            // Failed login
            log_action($user_id, 'login_failed', 'Failed login attempt', get_client_ip());
        }

        // After login attempt, re-fetch user and check for lockout
        $stmt = $pdo->prepare("SELECT login_attempts, last_failed_attempt FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        $remaining_attempts = $user ? max(0, $max_attempts - (int)$user['login_attempts']) : $max_attempts;

        if ($user && $user['login_attempts'] >= $max_attempts && !empty($user['last_failed_attempt'])) {
            $lock_until = strtotime($user['last_failed_attempt']) + 60;
            $now = time();
            $lock_remaining = $lock_until - $now;
            if ($lock_remaining > 0) {
                $remaining_attempts = 0;
                $is_locked = true;
            } else {
                $lock_remaining = 0;
                $remaining_attempts = $max_attempts;
                $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0, last_failed_attempt = NULL WHERE username = ?");
                $stmt->execute([$username]);
                $user['login_attempts'] = 0;
                $user['last_failed_attempt'] = null;
                $error = '';
            }
        }

        if (!$is_locked && isset($result['success']) && $result['success']) {
            header('Location: dashboard.php');
            exit();
        } elseif (!$is_locked && isset($result['require_2fa'])) {
            $require_2fa = true;
        } elseif (!$is_locked) {
            $error = $result['message'];
        }
    }
} elseif (!empty($_GET['username'])) {
    // If username is passed via GET, show attempts for that user
    $username = $_GET['username'];
    $stmt = $pdo->prepare("SELECT login_attempts, last_failed_attempt FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    $remaining_attempts = $user ? max(0, $max_attempts - (int)$user['login_attempts']) : $max_attempts;
    if ($user && $user['login_attempts'] >= $max_attempts && !empty($user['last_failed_attempt'])) {
        $lock_until = strtotime($user['last_failed_attempt']) + 60;
        $now = time();
        $lock_remaining = $lock_until - $now;
        if ($lock_remaining < 0) $lock_remaining = 0;
        $remaining_attempts = 0;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Inventory System Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($lock_remaining > 0): ?>
                            <div class="alert alert-danger">
                                Account locked due to too many failed attempts.<br>
                                Please wait <span id="lock-timer"><?= $lock_remaining ?></span> seconds.
                            </div>
                            <script>
                            var lockTime = <?= $lock_remaining ?>;
                            var timer = setInterval(function() {
                                lockTime--;
                                document.getElementById('lock-timer').textContent = lockTime;
                                if (lockTime <= 0) {
                                    clearInterval(timer);
                                    location.reload();
                                }
                            }, 1000);
                            </script>
                        <?php else: ?>
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>
                            
                            <div class="alert alert-info">
                                You have <b><?= $remaining_attempts ?></b> login attempt(s) left before your account will be locked for 60 seconds.
                            </div>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                
                                <?php if ($require_2fa): ?>
                                <div class="mb-3">
                                    <label for="two_factor_code" class="form-label">Two-Factor Authentication Code</label>
                                    <input type="text" class="form-control" id="two_factor_code" name="two_factor_code" required>
                                    <small class="text-muted">Enter the 6-digit code from your authenticator app</small>
                                </div>
                                <?php endif; ?>
                                
                                <button type="submit" class="btn btn-primary w-100">Login</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
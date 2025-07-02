<?php
require_once 'middleware.php';
require_admin();
require_once 'TwoFactor.php';

// Handle 2FA setup
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['setup_2fa'])) {
        $twoFactor = new TwoFactor();

        // Generate new secret
        $secret = $twoFactor->createSecret();
        $secretUrl = $twoFactor->getSecretURL($_SESSION['username'], $secret);

        // Store temporarily in session until verified
        $_SESSION['2fa_temp_secret'] = $secret;
        $_SESSION['2fa_url'] = $secretUrl;

        log_action($_SESSION['user_id'], '2fa_init', 'Started 2FA setup (manual entry)', $_SERVER['REMOTE_ADDR']);
    } elseif (isset($_POST['verify_2fa'])) {
        if (!empty($_SESSION['2fa_temp_secret'])) {
            $twoFactor = new TwoFactor();

            if ($twoFactor->verifyCode($_SESSION['2fa_temp_secret'], $_POST['verification_code'])) {
                // Save to database
                $stmt = $pdo->prepare("UPDATE users SET two_factor_secret = ? WHERE id = ?");
                $stmt->execute([$_SESSION['2fa_temp_secret'], $_SESSION['user_id']]);

                unset($_SESSION['2fa_temp_secret']);
                unset($_SESSION['2fa_url']);

                log_action($_SESSION['user_id'], '2fa_enabled', '2FA setup completed', $_SERVER['REMOTE_ADDR']);
                $_SESSION['message'] = 'Two-Factor Authentication has been enabled successfully';
            } else {
                log_action($_SESSION['user_id'], '2fa_failed', 'Invalid 2FA verification code', $_SERVER['REMOTE_ADDR']);
                $_SESSION['error'] = 'Invalid verification code';
            }
        }
        header('Location: settings.php');
        exit();
    } elseif (isset($_POST['disable_2fa'])) {
        $stmt = $pdo->prepare("UPDATE users SET two_factor_secret = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);

        log_action($_SESSION['user_id'], '2fa_disabled', '2FA disabled', $_SERVER['REMOTE_ADDR']);
        $_SESSION['message'] = 'Two-Factor Authentication has been disabled';
        header('Location: settings.php');
        exit();
    }
}

// Check current 2FA status
$stmt = $pdo->prepare("SELECT two_factor_secret FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$has2fa = !empty($user['two_factor_secret']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>System Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'header.php'; ?>

<div class="container mt-4">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <h2>System Settings</h2>

    <div class="card mt-4">
        <div class="card-header">Two-Factor Authentication</div>
        <div class="card-body">
            <?php if ($has2fa): ?>
                <div class="alert alert-success">
                    Two-Factor Authentication is currently <strong>enabled</strong> for your account.
                </div>
                <form method="POST">
                    <button type="submit" name="disable_2fa" class="btn btn-danger">Disable 2FA</button>
                </form>
            <?php elseif (isset($_SESSION['2fa_temp_secret'])): ?>
                <div class="alert alert-info">
                    <h5>Complete 2FA Setup</h5>
                    <p>Add this secret key manually to your authenticator app:</p>
                    <div class="mb-3">
                        <code><?= $_SESSION['2fa_temp_secret'] ?></code>
                    </div>
                    <p>Then enter the current 6-digit verification code from your app:</p>
                    <form method="POST" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="verification_code" 
                                   placeholder="6-digit code" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" name="verify_2fa" class="btn btn-primary">Verify</button>
                            <a href="settings.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    Two-Factor Authentication is currently <strong>disabled</strong> for your account.
                </div>
                <form method="POST">
                    <button type="submit" name="setup_2fa" class="btn btn-primary">Enable 2FA</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">Security Settings</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Password Requirements</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="requireMixedCase" checked disabled>
                        <label class="form-check-label" for="requireMixedCase">Require upper and lower case letters</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="requireNumbers" checked disabled>
                        <label class="form-check-label" for="requireNumbers">Require at least one number</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="requireSpecialChars" checked disabled>
                        <label class="form-check-label" for="requireSpecialChars">Require special character</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="minPasswordLength" class="form-label">Minimum Password Length</label>
                    <select class="form-select" id="minPasswordLength" name="min_password_length" disabled>
                        <option value="8" selected>8 characters</option>
                        <option value="10">10 characters</option>
                        <option value="12">12 characters</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" disabled>Update Security Settings</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

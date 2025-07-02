<?php
require_once 'middleware.php';
require_admin();
delete_expired_users($pdo);
delete_inactive_users($pdo); // <-- Add this line

// Helper to get real IP address (force IPv4 if ::1)
function get_real_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }
    // Convert IPv6 localhost to IPv4
    return ($ip === '::1') ? '127.0.0.1' : $ip;
}

// Helper to get root address
function get_root_address() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $root = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    return $protocol . $host . $root;
}

// Get all system logs
$stmt = $pdo->query("SELECT l.*, u.username FROM system_logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC");
$logs = $stmt->fetchAll();

// Get all audit logs for deleted users
$audit_stmt = $pdo->query("SELECT * FROM audit_deleted_users ORDER BY deleted_at DESC");
$audit_logs = $audit_stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>System Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container mt-4">
        <h2>System Logs</h2>
        <p><strong>Root Address:</strong> <?= get_root_address(); ?></p>
        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action Type</th>
                            <th>Description</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= $log['created_at'] ?></td>
                            <td>
                                <?php
                                if ($log['username']) {
                                    echo htmlspecialchars($log['username']);
                                } elseif ($log['user_id']) {
                                    echo 'Deleted User (ID: ' . htmlspecialchars($log['user_id']) . ')';
                                } else {
                                    echo 'System';
                                }
                                ?>
                            </td>
                            <td><?= htmlspecialchars($log['action_type']) ?></td>
                            <td><?= htmlspecialchars($log['description']) ?></td>
                            <td>
                                <?php
                                // Show IPv4 if ::1
                                echo ($log['ip_address'] === '::1') ? '127.0.0.1' : htmlspecialchars($log['ip_address']);
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <!-- Show audit logs for deleted users -->
                        <?php foreach ($audit_logs as $audit): ?>
                        <tr>
                            <td><?= htmlspecialchars($audit['deleted_at']) ?></td>
                            <td><?= 'Deleted User (ID: ' . htmlspecialchars($audit['user_id']) . ')' ?></td>
                            <td>user_deleted</td>
                            <td><?= htmlspecialchars($audit['reason']) ?></td>
                            <td>system</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</html>
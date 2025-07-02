<?php
require_once 'middleware.php';
require_admin();

// Get stats
$stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
$user_count = $stmt->fetch()['user_count'];

$stmt = $pdo->query("SELECT COUNT(*) as item_count FROM inventory");
$item_count = $stmt->fetch()['item_count'];

$stmt = $pdo->query("SELECT COUNT(*) as active_users FROM users WHERE is_active = TRUE");
$active_users = $stmt->fetch()['active_users'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container mt-4">
        <h2>Admin Dashboard</h2>
        
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">Users</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= $user_count ?> Total Users</h5>
                        <p class="card-text"><?= $active_users ?> Active Users</p>
                        <a href="users.php" class="btn btn-light">Manage Users</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Inventory</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= $item_count ?> Items</h5>
                        <p class="card-text">In stock across all categories</p>
                        <a href="inventory.php" class="btn btn-light">Manage Inventory</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">System</div>
                    <div class="card-body">
                        <h5 class="card-title">System Status</h5>
                        <p class="card-text">All systems operational</p>
                        <a href="logs.php" class="btn btn-light">View Logs</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                Recent Activity
            </div>
            <div class="card-body">
                <?php
                $stmt = $pdo->query("SELECT l.*, u.username FROM system_logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT 10");
                $recent_logs = $stmt->fetchAll();
                ?>
                
                <ul class="list-group">
                    <?php foreach ($recent_logs as $log): ?>
                    <li class="list-group-item">
                        <strong><?= $log['username'] ? htmlspecialchars($log['username']) : 'System' ?></strong> - 
                        <?= htmlspecialchars($log['description']) ?>
                        <small class="text-muted float-end"><?= $log['created_at'] ?></small>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
require_once 'middleware.php';
require_staff();

// Get inventory stats
$stmt = $pdo->query("SELECT COUNT(*) as item_count FROM inventory");
$item_count = $stmt->fetch()['item_count'];

$stmt = $pdo->query("SELECT COUNT(DISTINCT category) as category_count FROM inventory");
$category_count = $stmt->fetch()['category_count'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container mt-4">
        <h2>Staff Dashboard</h2>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Inventory</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= $item_count ?> Items</h5>
                        <p class="card-text">Across <?= $category_count ?> categories</p>
                        <?php if (has_permission('view')): ?>
                            <a href="inventory.php" class="btn btn-light">Manage Inventory</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">Quick Actions</div>
                    <div class="card-body">
                        <h5 class="card-title">Inventory Management</h5>
                        <?php if (has_permission('add')): ?>
                            <a href="inventory.php?action=add" class="btn btn-light">Add New Item</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                Low Stock Items
            </div>
            <div class="card-body">
                <?php
                $stmt = $pdo->query("SELECT name, quantity FROM inventory WHERE quantity < 10 ORDER BY quantity ASC LIMIT 10");
                $low_stock = $stmt->fetchAll();
                ?>
                
                <?php if ($low_stock): ?>
                    <ul class="list-group">
                        <?php foreach ($low_stock as $item): ?>
                        <li class="list-group-item">
                            <?= htmlspecialchars($item['name']) ?>
                            <span class="badge bg-danger float-end"><?= $item['quantity'] ?> left</span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="alert alert-info">No low stock items</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
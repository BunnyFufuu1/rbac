<?php
// Check for inactive accounts on every page load
check_inactive_accounts();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Inventory System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="inventory.php">Inventory</a>
                    </li>
                    <?php if (has_role('admin')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">User Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logs.php">System Logs</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if (is_logged_in()): ?>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                                <?php if (has_role('admin')): ?>
                                    <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                                <?php endif; ?>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="login.php">Login</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4"></div>
<?php
require_once 'middleware.php';

// Redirect based on role
if (has_role('admin')) {
    $dashboard_content = 'admin_dashboard.php';
} else {
    $dashboard_content = 'staff_dashboard.php';
}

include $dashboard_content;
?>
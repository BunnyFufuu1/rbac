<?php
require_once 'auth.php';

// Only proceed if user is actually logged in
if (is_logged_in()) {
    // Log the logout action before destroying session
    log_action($_SESSION['user_id'], 'logout', 'User logged out', $_SERVER['REMOTE_ADDR']);
    
    // Completely destroy the session
    session_unset();
    session_destroy();
    session_write_close();
    
    // Expire the session cookie
    setcookie(session_name(), '', 0, '/');
}

// Redirect to login page
header('Location: login.php');
exit();
?>
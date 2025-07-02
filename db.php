<?php
$host = 'localhost';
$dbname = 'inventory_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

function log_action($user_id, $action_type, $description, $ip_address) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO system_logs (user_id, action_type, description, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $action_type, $description, $ip_address]);
}
?>
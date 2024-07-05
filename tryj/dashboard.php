<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'config.php';

// Fetch user information
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Willkommen, <?= htmlspecialchars($user['username']) ?></h1>
    <p>Email: <?= htmlspecialchars($user['email']) ?></p>
    <p>Rolle: <?= htmlspecialchars($user['role']) ?></p>
    <a href="file_explorer.php">Dateiexplorer</a> |
    <?php if ($user['role'] == 'admin'): ?>
    <a href="user_management.php">Benutzerverwaltung</a> |
    <?php endif; ?>
    <a href="logout.php">Logout</a>
</body>
</html>

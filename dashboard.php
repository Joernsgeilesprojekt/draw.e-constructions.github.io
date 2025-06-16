<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'config.php';

//  user information
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
    <div class="header">
        <div class="logo">Logo</div>
        <div class="texthead">
            <?= htmlspecialchars($user['username']) ?>
            <img src="img/user.png" alt="User Icon">
        </div>
    </div>

    <script>
        // Detect device type and adjust header layout
        function adjustHeaderForDevice() {
            const header = document.querySelector('.header');
            const width = window.innerWidth;

            if (width <= 480) {
                // Mobile adjustments
                header.style.fontSize = '16px';
            } else if (width <= 768) {
                // Tablet adjustments
                header.style.fontSize = '18px';
            } else {
                // Desktop adjustments
                header.style.fontSize = '24px';
            }
        }

        window.addEventListener('resize', adjustHeaderForDevice);
        window.addEventListener('DOMContentLoaded', adjustHeaderForDevice);
    </script>

<div class="container">
        <a href="file_explorer.php">Dateiexplorer</a> |
        <?php if ($user['role'] == 'admin'): ?>
        <a href="user_management.php">Benutzerverwaltung</a> |
        <a href="project_management.php">Projektverwaltung</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
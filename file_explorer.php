<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'config.php';

$directory = 'uploads/';
if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}

$projects = $conn->query("SELECT projects.id, projects.name FROM projects INNER JOIN project_users ON projects.id = project_users.project_id WHERE project_users.user_id = {$_SESSION['user_id']}");
$message = isset($_GET['message']) ? $_GET['message'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dateiexplorer</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="header">
    <div class="logo">Logo</div>
    <div>
        <?= htmlspecialchars($_SESSION['username']) ?>
        <img src="img/user.png" alt="User Icon">
    </div>
</div>

<div class="container">
    <h1>Dateiexplorer</h1>
    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <h2>Projekte</h2>
    <a href="/project_management.php" class="create-project-btn">Projekt erstellen</a>
    <ul>
        <?php while ($project = $projects->fetch_assoc()): ?>
            <li>
                <a href="project_files.php?project_id=<?= $project['id'] ?>"><?= htmlspecialchars($project['name']) ?></a>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    - <a href="manage_project_users.php?project_id=<?= $project['id'] ?>">Benutzer verwalten</a>
                <?php endif; ?>
            </li>
        <?php endwhile; ?>
    </ul>
    <a href="dashboard.php">Zurück zum Dashboard</a>
</div>

<script>
    // Adjust header size based on device type
    function adjustHeaderForDevice() {
        const header = document.querySelector('.header');
        const width = window.innerWidth;

        if (width <= 480) {
            header.style.fontSize = '16px';
        } else if (width <= 768) {
            header.style.fontSize = '18px';
        } else {
            header.style.fontSize = '24px';
        }
    }

    window.addEventListener('resize', adjustHeaderForDevice);
    window.addEventListener('DOMContentLoaded', adjustHeaderForDevice);
</script>
</body>
</html>

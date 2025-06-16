<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'config.php';

$project_id = $_GET['project_id'];

$link = "http://localhost.com/join_project.php?project_id=" . $project_id;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link generieren</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h1>Link zur gemeinsamen Nutzung</h1>
    <p>Teilen Sie den folgenden Link mit anderen Benutzern, damit sie dem Projekt beitreten können:</p>
    <input type="text" value="<?= $link ?>" readonly onclick="this.select()">
    <a href="project_files.php?project_id=<?= $project_id ?>">Zurück zu den Projektdateien</a>
</div>
</body>
</html>

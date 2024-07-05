<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'config.php';

$project_id = $_GET['project_id'];

$directory = 'uploads/' . $project_id . '/';
if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}

$files = array_diff(scandir($directory), array('..', '.'));
$message = isset($_GET['message']) ? $_GET['message'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projektdateien</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h1>Projektdateien</h1>
    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <?php if ($_SESSION['role'] == 'admin'): ?>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="project_id" value="<?= $project_id ?>">
        <label for="fileToUpload">Wählen Sie eine Datei zum Hochladen:</label>
        <input type="file" name="fileToUpload" id="fileToUpload">
        <input type="submit" value="Datei hochladen" name="submit">
    </form>
    <?php endif; ?>
    <table>
        <tr>
            <th>Dateiname</th>
            <th>Aktionen</th>
        </tr>
        <?php foreach ($files as $file): ?>
        <tr>
            <td><?= htmlspecialchars($file) ?></td>
            <td><a href="uploads/<?= $project_id ?>/<?= htmlspecialchars($file) ?>" download>Download</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <a href="file_explorer.php">Zurück zum Dateiexplorer</a>
</div>
</body>
</html>

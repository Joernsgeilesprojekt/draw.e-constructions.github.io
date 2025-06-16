<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'config.php';

//  ab dafuer Variablen
$message = '';
$project_data = [];
$designs_result = null;

$project_id = $_GET['project_id'];
$project_stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
$project_stmt->bind_param("i", $project_id);
$project_stmt->execute();
$project_result = $project_stmt->get_result();
if ($project_result->num_rows > 0) {
    $project_data = $project_result->fetch_assoc();
} else {
    die('Projekt nicht gefunden.');
}

$designs_stmt = $conn->prepare("SELECT id, name FROM designs WHERE project_id = ?");
$designs_stmt->bind_param("i", $project_id);
$designs_stmt->execute();
$designs_result = $designs_stmt->get_result();

$directory = 'uploads/' . $project_id . '/';
if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}
$files = array_diff(scandir($directory), array('..', '.'));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projekt bearbeiten: <?= htmlspecialchars($project_data['name']) ?></title>
    <link rel="stylesheet" href="styles.css">
    <script>
        const project_id = <?= $project_id ?>;
    </script>
    <script src="canvas.js"></script>
</head>
<body>
<div class="container">
    <h1>Projekt bearbeiten: <?= htmlspecialchars($project_data['name']) ?></h1>
    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="project_id" value="<?= $project_id ?>">
        <label for="fileToUpload">Schaltplan hochladen:</label>
        <input type="file" name="fileToUpload" id="fileToUpload">
        <input type="submit" value="Schaltplan hochladen" name="submit">
    </form>
    <h2>Schaltpläne</h2>
    <table>
        <tr>
            <th>Dateiname</th>
            <th>Aktionen</th>
        </tr>
        <?php while ($file = $designs_result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($file['name']) ?></td>
            <td>
                <a href="view_design.php?design_id=<?= $file['id'] ?>">Anzeigen</a>
                <a href="download_design.php?design_id=<?= $file['id'] ?>">Download</a>
                <a href="edit_design.php?design_id=<?= $file['id'] ?>">Bearbeiten</a>
                <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'owner' || $_SESSION['role'] == 'project_admin'): ?>
                    <a href="delete_design.php?design_id=<?= $file['id'] ?>&project_id=<?= $project_id ?>">Löschen</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <h2>Schaltplan erstellen/bearbeiten</h2>
    <canvas id="circuitCanvas" width="800" height="600"></canvas>
    <br>
    <button onclick="saveCanvas()">Schaltplan speichern</button>
    <a href="project_files.php?project_id=<?= $project_id ?>">Zurück zu den Projektdateien</a>
</div>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

require 'config.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];

    // Neues Projekt hinzufügen
    $stmt = $conn->prepare("INSERT INTO projects (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $description);
    if ($stmt->execute()) {
        $project_id = $stmt->insert_id;
        // Administrator zum Projekt hinzufügen
        $stmt = $conn->prepare("INSERT INTO project_users (project_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $project_id, $_SESSION['user_id']);
        $stmt->execute();
        $error_message = "Projekt hinzugefügt.";
    } else {
        $error_message = "Fehler beim Hinzufügen des Projekts: " . $stmt->error;
    }
}

$projects = $conn->query("SELECT * FROM projects");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projektverwaltung</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
<h1>Projektverwaltung</h1>
<?php if ($error_message): ?>
    <p><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>
<form method="POST" action="project_management.php">
    <input type="text" name="name" placeholder="Projektname" required>
    <textarea name="description" placeholder="Projektbeschreibung" required></textarea>
    <button type="submit">Projekt hinzufügen</button>
</form>
<h2>Bestehende Projekte</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Beschreibung</th>
        <th>Aktionen</th>
    </tr>
    <?php while ($row = $projects->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['id']) ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['description']) ?></td>
        <td><a href="manage_project_users.php?project_id=<?= $row['id'] ?>">Benutzer verwalten</a></td>
    </tr>
    <?php endwhile; ?>
</table>
<a href="dashboard.php">Zurück zum Dashboard</a>
</div>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

require 'config.php';

$project_id = $_GET['project_id'];
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];

    // Benutzer zum Projekt hinzufügen
    $stmt = $conn->prepare("INSERT INTO project_users (project_id, user_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $project_id, $user_id);
    if ($stmt->execute()) {
        $error_message = "Benutzer hinzugefügt.";
    } else {
        $error_message = "Fehler beim Hinzufügen des Benutzers: " . $stmt->error;
    }
}

$users = $conn->query("SELECT * FROM users WHERE id NOT IN (SELECT user_id FROM project_users WHERE project_id = $project_id)");
$project_users = $conn->query("SELECT users.id, users.username FROM users INNER JOIN project_users ON users.id = project_users.user_id WHERE project_users.project_id = $project_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projektbenutzer verwalten</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
<h1>Projektbenutzer verwalten</h1>
<?php if ($error_message): ?>
    <p><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>
<form method="POST" action="manage_project_users.php?project_id=<?= $project_id ?>">
    <select name="user_id">
        <?php while ($user = $users->fetch_assoc()): ?>
            <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
        <?php endwhile; ?>
    </select>
    <button type="submit">Benutzer hinzufügen</button>
</form>
<h2>Benutzer im Projekt</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Benutzername</th>
    </tr>
    <?php while ($project_user = $project_users->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($project_user['id']) ?></td>
        <td><?= htmlspecialchars($project_user['username']) ?></td>
    </tr>
    <?php endwhile; ?>
</table>
<a href="project_management.php">Zurück zur Projektverwaltung</a>
</div>
</body>
</html>

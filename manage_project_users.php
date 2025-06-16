<?php
session_start();
require 'csrf.php';
csrf_verify();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

require 'config.php';

$project_id = $_GET['project_id'];
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $role = $_POST['role'];

    // Benutzer zum Projekt hinzufügen
    $stmt = $conn->prepare("INSERT INTO project_users (project_id, user_id, role) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $project_id, $user_id, $role);
    if ($stmt->execute()) {
        $error_message = "Benutzer hinzugefügt.";
    } else {
        $error_message = "Fehler beim Hinzufügen des Benutzers: " . $stmt->error;
    }
}

$users_stmt = $conn->prepare("SELECT * FROM users WHERE id NOT IN (SELECT user_id FROM project_users WHERE project_id = ?)");
$users_stmt->bind_param("i", $project_id);
$users_stmt->execute();
$users = $users_stmt->get_result();

$project_users_stmt = $conn->prepare("SELECT users.id, users.username, project_users.role FROM users INNER JOIN project_users ON users.id = project_users.user_id WHERE project_users.project_id = ?");
$project_users_stmt->bind_param("i", $project_id);
$project_users_stmt->execute();
$project_users = $project_users_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projektbenutzer verwalten</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
<h1>Projektbenutzer verwalten</h1>
<?php if ($error_message): ?>
    <p><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>
<form method="POST" action="manage_project_users.php?project_id=<?= $project_id ?>">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <select name="user_id">
        <?php while ($user = $users->fetch_assoc()): ?>
            <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
        <?php endwhile; ?>
    </select>
    <select name="role">
        <option value="editor">Editor</option>
        <option value="project_admin">Projekt-Admin</option>
    </select>
    <button type="submit">Benutzer hinzufügen</button>
</form>
<h2>Benutzer im Projekt</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Benutzername</th>
        <th>Rolle</th>
        <th>Aktionen</th>
    </tr>
    <?php while ($project_user = $project_users->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($project_user['id']) ?></td>
        <td><?= htmlspecialchars($project_user['username']) ?></td>
        <td><?= htmlspecialchars($project_user['role']) ?></td>
        <td><a href="remove_project_user.php?project_id=<?= $project_id ?>&user_id=<?= $project_user['id'] ?>">Entfernen</a></td>
    </tr>
    <?php endwhile; ?>
</table>
<a href="project_management.php">Zurück zur Projektverwaltung</a>
</div>
<div class="texthead">
    <a href="impressum.php">Impressum</a> |
    <a href="datenschutz.php">Datenschutz</a>
</div>
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('service-worker.js').catch(() => {});
}
</script>
<script src="cookie.js"></script>
</body>
</html>

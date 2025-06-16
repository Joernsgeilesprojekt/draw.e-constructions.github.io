<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

require 'config.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Überprüfen, ob der Benutzername bereits existiert
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error_message = "Benutzername existiert bereits.";
    } else {
        // Neuen Benutzer hinzufügen
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $password, $role);
        if ($stmt->execute()) {
            $error_message = "Benutzer hinzugefügt.";
        } else {
            $error_message = "Fehler beim Hinzufügen des Benutzers: " . $stmt->error;
        }
    }
}

$result = $conn->query("SELECT * FROM users");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benutzerverwaltung</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
<h1>Benutzerverwaltung</h1>
<?php if ($error_message): ?>
    <p><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>
<form method="POST" action="user_management.php">
    <input type="text" name="username" placeholder="Benutzername" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Passwort" required>
    <select name="role">
        <option value="user">User</option>
        <option value="admin">Admin</option>
        <option value="project_admin">Projekt-Admin</option>
    </select>
    <button type="submit">Benutzer hinzufügen</button>
</form>
<h2>Bestehende Benutzer</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
        <th>Created At</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['id']) ?></td>
        <td><?= htmlspecialchars($row['username']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['role']) ?></td>
        <td><?= htmlspecialchars($row['created_at']) ?></td>
    </tr>
    <?php endwhile; ?>
</table>
<a href="dashboard.php">Zurück zum Dashboard</a>
</div>
</body>
</html>

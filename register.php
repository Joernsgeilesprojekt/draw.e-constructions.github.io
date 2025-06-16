<?php
session_start();
require 'config.php';
require 'csrf.php';
csrf_verify();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        header('Location: login.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
<form method="POST" action="register.php">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Register</button>
</form>

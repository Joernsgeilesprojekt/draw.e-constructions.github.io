<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'config.php';

$project_id = $_GET['project_id'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO project_users (project_id, user_id) VALUES (?, ?)");
$stmt->bind_param("ii", $project_id, $user_id);
if ($stmt->execute()) {
    $message = "Sie haben dem Projekt erfolgreich beigetreten.";
} else {
    $message = "Fehler beim Beitreten zum Projekt: " . $stmt->error;
}
header('Location: file_explorer.php?message=' . urlencode($message));
?>

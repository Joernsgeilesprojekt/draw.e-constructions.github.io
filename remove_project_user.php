<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

require 'config.php';

$project_id = $_GET['project_id'];
$user_id = $_GET['user_id'];

// Benutzer aus dem Projekt entfernen
$stmt = $conn->prepare("DELETE FROM project_users WHERE project_id = ? AND user_id = ?");
$stmt->bind_param("ii", $project_id, $user_id);
$stmt->execute();

header('Location: manage_project_users.php?project_id=' . $project_id);
?>

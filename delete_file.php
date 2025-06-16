<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'owner', 'project_admin'])) {
    header('Location: login.php');
    exit;
}

require 'config.php';

$project_id = $_GET['project_id'];
$file = $_GET['file'];
$file_path = "uploads/" . $project_id . "/" . $file;

if (file_exists($file_path)) {
    unlink($file_path);
    $message = "Datei erfolgreich gelöscht.";
} else {
    $message = "Datei nicht gefunden.";
}

header('Location: edit_project.php?project_id=' . $project_id . '&message=' . urlencode($message));
?>

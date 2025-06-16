<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'owner', 'project_admin'])) {
    header('Location: login.php');
    exit;
}

require 'config.php';

$design_id = $_GET['design_id'];
$project_id = $_GET['project_id'];

$stmt = $conn->prepare("DELETE FROM designs WHERE id = ?");
$stmt->bind_param("i", $design_id);
$stmt->execute();

header('Location: edit_project.php?project_id=' . $project_id);
?>

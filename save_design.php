<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'config.php';

$image_data = $_POST['image_data'];
$project_id = $_POST['project_id'];
$design_id = isset($_POST['design_id']) ? $_POST['design_id'] : null;

// Image data cleaning
$image_data = str_replace('data:image/png;base64,', '', $image_data);
$image_data = str_replace(' ', '+', $image_data);
$data = base64_decode($image_data);

if ($design_id) {
    // Update existing design
    $stmt = $conn->prepare("UPDATE designs SET image_data = ? WHERE id = ?");
    $stmt->bind_param("si", $data, $design_id);
} else {
    // Insert new design
    $name = "Schaltplan_" . time();
    $stmt = $conn->prepare("INSERT INTO designs (project_id, name, image_data) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $project_id, $name, $data);
}

if ($stmt->execute()) {
    echo "Schaltplan erfolgreich gespeichert!";
} else {
    echo "Fehler: " . $stmt->error;
}
?>

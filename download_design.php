<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'config.php';

$design_id = $_GET['design_id'];

$stmt = $conn->prepare("SELECT name, image_data FROM designs WHERE id = ?");
$stmt->bind_param("i", $design_id);
$stmt->execute();
$stmt->bind_result($name, $image_data);
$stmt->fetch();
$stmt->close();

header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="' . $name . '.png"');
echo $image_data;
?>

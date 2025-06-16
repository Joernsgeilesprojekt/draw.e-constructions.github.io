<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require 'config.php';
$version_id = filter_input(INPUT_GET, 'version_id', FILTER_VALIDATE_INT);
$stmt = $conn->prepare("SELECT image_data FROM design_versions WHERE id = ?");
$stmt->bind_param("i", $version_id);
$stmt->execute();
$stmt->bind_result($img);
$stmt->fetch();
header('Content-Type: image/png');
echo $img;

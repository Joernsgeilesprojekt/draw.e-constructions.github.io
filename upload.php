<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'config.php';

$project_id = $_POST['project_id'];
$target_dir = "uploads/" . $project_id . "/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
$error_message = '';

// Überprüfen, ob Datei tatsächlich hochgeladen wurde
if (isset($_POST["submit"])) {
    // Datei existiert bereits
    if (file_exists($target_file)) {
        $error_message = "Datei existiert bereits.";
        $uploadOk = 0;
    }
    // Dateityp überprüfen
    if ($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg" && $fileType != "gif" && $fileType != "pdf") {
        $error_message = "Nur JPG, JPEG, PNG, GIF und PDF Dateien sind erlaubt.";
        $uploadOk = 0;
    }
    // Hochladen der Datei
    if ($uploadOk == 0) {
        $error_message = "Ihre Datei wurde nicht hochgeladen.";
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            $error_message = "Die Datei ". htmlspecialchars(basename($_FILES["fileToUpload"]["name"])). " wurde hochgeladen.";
        } else {
            $error_message = "Beim Hochladen der Datei ist ein Fehler aufgetreten.";
        }
    }
}
header('Location: project_files.php?project_id=' . $project_id . '&message=' . urlencode($error_message));
?>
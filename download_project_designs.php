<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'config.php';

$project_id = filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT);
if (!$project_id) {
    die('Ungültige Projekt-ID');
}

$stmt = $conn->prepare("SELECT name, image_data FROM designs WHERE project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();

$zip = new ZipArchive();
$tempFile = tempnam(sys_get_temp_dir(), 'designs_') . '.zip';
if ($zip->open($tempFile, ZipArchive::CREATE) !== TRUE) {
    die('Zip konnte nicht erstellt werden');
}

while ($row = $result->fetch_assoc()) {
    $filename = $row['name'] . '.png';
    $zip->addFromString($filename, $row['image_data']);
}
$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="project_' . $project_id . '_designs.zip"');
readfile($tempFile);
unlink($tempFile);
?>

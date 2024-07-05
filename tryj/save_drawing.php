<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $drawing = $_POST['drawing'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO drawings (user_id, drawing_data) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $drawing);

    if ($stmt->execute()) {
        echo "Zeichnung gespeichert";
    } else {
        echo "Fehler: " . $stmt->error;
    }
} else {
    echo "Nicht autorisiert oder ungültige Anfrage";
}
?>

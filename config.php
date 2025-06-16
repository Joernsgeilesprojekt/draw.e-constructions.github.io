<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "schaltkreis_planer";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}
?>

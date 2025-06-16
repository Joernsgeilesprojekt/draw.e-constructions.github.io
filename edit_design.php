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

$image_data = base64_encode($image_data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schaltplan bearbeiten: <?= htmlspecialchars($name) ?></title>
    <link rel="stylesheet" href="styles.css">
    <script>
        const project_id = <?= $_GET['project_id'] ?>;
        const design_id = <?= $design_id ?>;
        const image_data = "data:image/png;base64,<?= $image_data ?>";
    </script>
    <script src="canvas.js"></script>
    <script>
        window.onload = function() {
            const canvas = document.getElementById('circuitCanvas');
            const ctx = canvas.getContext('2d');
            let drawing = false;

            const img = new Image();
            img.src = image_data;
            img.onload = function() {
                ctx.drawImage(img, 0, 0);
            }

            canvas.addEventListener('mousedown', function(e) {
                drawing = true;
                ctx.beginPath();
                ctx.moveTo(e.offsetX, e.offsetY);
            });

            canvas.addEventListener('mousemove', function(e) {
                if (drawing) {
                    ctx.lineTo(e.offsetX, e.offsetY);
                    ctx.stroke();
                }
            });

            canvas.addEventListener('mouseup', function() {
                drawing = false;
            });

            canvas.addEventListener('mouseout', function() {
                drawing = false;
            });
        }

        function saveCanvas() {
            const canvas = document.getElementById('circuitCanvas');
            const dataURL = canvas.toDataURL('image/png');

            // AJAX request to save image data
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'save_design.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Schaltplan gespeichert!');
                    location.href = 'edit_project.php?project_id=' + project_id;  // Zurück zur Projektseite
                } else {
                    alert('Fehler beim Speichern des Schaltplans.');
                }
            };
            xhr.send('image_data=' + encodeURIComponent(dataURL) + '&design_id=' + design_id);
        }
    </script>
</head>
<body>
<div class="container">
    <h1>Schaltplan bearbeiten: <?= htmlspecialchars($name) ?></h1>
    <canvas id="circuitCanvas" width="800" height="600"></canvas>
    <br>
    <button onclick="saveCanvas()">Schaltplan speichern</button>
    <a href="edit_project.php?project_id=<?= $_GET['project_id'] ?>">Zurück zu den Projektdateien</a>
</div>
</body>
</html>

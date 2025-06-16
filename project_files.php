<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'config.php';

$project_id = filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT);
if (!$project_id) {
    header('Location: login.php');
    exit;
}

$message = isset($_GET['message']) ? $_GET['message'] : '';

try {
    $designs_stmt = $conn->prepare("SELECT id, name FROM designs WHERE project_id = ?");
    $designs_stmt->bind_param("i", $project_id);
    $designs_stmt->execute();
    $designs_result = $designs_stmt->get_result();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
    exit;
}
?>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projektdateien</title>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'config.php';

$project_id = filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT);
if (!$project_id) {
    header('Location: login.php');
    exit;
}

$message = isset($_GET['message']) ? $_GET['message'] : '';

try {
    $designs_stmt = $conn->prepare("SELECT id, name FROM designs WHERE project_id = ?");
    $designs_stmt->bind_param("i", $project_id);
    $designs_stmt->execute();
    $designs_result = $designs_stmt->get_result();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projektdateien</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="tools.css">
</head>
<body>
<?php include 'header.php'; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="tools.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <h1>Projektdateien</h1>
    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'owner' || $_SESSION['role'] == 'project_admin'): ?>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="project_id" value="<?= $project_id ?>">
            <label for="fileToUpload">Schaltplan hochladen:</label>
            <input type="file" name="fileToUpload" id="fileToUpload">
            <input type="submit" value="Schaltplan hochladen" name="submit">
        </form>
    <?php endif; ?>
    <h2>Schaltpläne</h2>
    <table>
        <tr>
            <th>Dateiname</th>
            <th>Aktionen</th>
        </tr>
        <?php while ($design = $designs_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($design['name']) ?></td>
                <td>
                    <a href="view_design.php?design_id=<?= $design['id'] ?>">Anzeigen</a>
                    <a href="download_design.php?design_id=<?= $design['id'] ?>">Download</a>
                    <a href="edit_design.php?design_id=<?= $design['id'] ?>&project_id=<?= $project_id ?>">Bearbeiten</a>
                    <a href="design_history.php?design_id=<?= $design['id'] ?>">Versionen</a>
                    <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'owner' || $_SESSION['role'] == 'project_admin'): ?>
                        <a href="delete_design.php?design_id=<?= $design['id'] ?>&project_id=<?= $project_id ?>">Löschen</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <div class="project-layout">
        <div class="files-section">
            <a href="download_project_designs.php?project_id=<?= $project_id ?>">Alle Schaltpläne als ZIP herunterladen</a>
            <a href="file_explorer.php">Zurück zum Dateiexplorer</a>
        </div>
        <div class="draw-section">
            <div class="toolbox">
                <button id="lineTool">Leitung</button>
                <button id="circleTool">Kreis</button>
                <button id="freehandTool">Freihand</button>
                <button id="switchTool">Schalter</button>
                <button id="lampTool">Lampe</button>
                <button id="andGateTool">AND-Gatter</button>
                <button id="powerSourceTool">Spannungsquelle</button>
                <input type="color" id="lineColor" value="#000000">
                <input type="number" id="lineWidth" value="2" min="1" max="10">
            </div>
            <canvas id="circuitCanvas" width="1000" height="800"></canvas>
            <div id="coordinates"></div>
            <button class="run-simulation" id="runSimulation">Simulation</button>
            <button id="undoBtn">Undo</button>
            <button id="redoBtn">Redo</button>
            <button id="exportJson">Export JSON</button>
            <button id="importJson">Import JSON</button>
            <input type="file" id="importFile" style="display:none" accept="application/json">
        </div>
    </div>
</div>

<script>
    const canvas = document.getElementById('circuitCanvas');
    const ctx = canvas.getContext('2d');
    let drawingMode = 'select';
    let currentTool = 'select';
    let freehandPath = [];
const components = [];
const undoStack = [];
const redoStack = [];

    canvas.addEventListener('mousemove', e => {
        document.getElementById('coordinates').textContent = `x:${e.offsetX} y:${e.offsetY}`;
    });

    document.getElementById('lineTool').addEventListener('click', () => drawingMode = 'line');
    document.getElementById('circleTool').addEventListener('click', () => drawingMode = 'circle');
    document.getElementById('freehandTool').addEventListener('click', () => drawingMode = 'freehand');
    document.getElementById('switchTool').addEventListener('click', () => drawingMode = 'switch');
    document.getElementById('lampTool').addEventListener('click', () => drawingMode = 'lamp');
    document.getElementById('andGateTool').addEventListener('click', () => drawingMode = 'andGate');
    document.getElementById('powerSourceTool').addEventListener('click', () => drawingMode = 'powerSource');

    function saveState() {
        undoStack.push(JSON.stringify(components));
        if (undoStack.length > 50) undoStack.shift();
        redoStack.length = 0;
    }

    function restoreState(state) {
        components.length = 0;
        JSON.parse(state).forEach(saved => {
            let component;
            switch (saved.type) {
                case 'line':
                    component = new Line(saved.x, saved.y, saved.endX, saved.endY, saved.color, saved.width);
                    break;
                case 'circle':
                    component = new Circle(saved.x, saved.y, saved.radius, saved.color, saved.width);
                    break;
                case 'freehand':
                    component = new Freehand(saved.points, saved.color, saved.width);
                    break;
                case 'switch':
                    component = new Switch(saved.x, saved.y, saved.state);
                    break;
                case 'lamp':
                    component = new Lamp(saved.x, saved.y, saved.on);
                    break;
                case 'andGate':
                    component = new ANDGate(saved.x, saved.y);
                    break;
                case 'powerSource':
                    component = new PowerSource(saved.x, saved.y);
                    break;
            }
            components.push(component);
        });
    }

</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <h1>Projektdateien</h1>
    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'owner' || $_SESSION['role'] == 'project_admin'): ?>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="project_id" value="<?= $project_id ?>">
            <label for="fileToUpload">Schaltplan hochladen:</label>
            <input type="file" name="fileToUpload" id="fileToUpload">
            <input type="submit" value="Schaltplan hochladen" name="submit">
        </form>
    <?php endif; ?>
    <h2>Schaltpläne</h2>
    <table>
        <tr>
            <th>Dateiname</th>
            <th>Aktionen</th>
        </tr>
        <?php while ($design = $designs_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($design['name']) ?></td>
                <td>
                    <a href="view_design.php?design_id=<?= $design['id'] ?>">Anzeigen</a>
                    <a href="download_design.php?design_id=<?= $design['id'] ?>">Download</a>
                    <a href="edit_design.php?design_id=<?= $design['id'] ?>&project_id=<?= $project_id ?>">Bearbeiten</a>
                    <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'owner' || $_SESSION['role'] == 'project_admin'): ?>
                        <a href="delete_design.php?design_id=<?= $design['id'] ?>&project_id=<?= $project_id ?>">Löschen</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <a href="download_project_designs.php?project_id=<?= $project_id ?>">Alle Schaltpläne als ZIP herunterladen</a>
    <a href="file_explorer.php">Zurück zum Dateiexplorer</a>
    <a href="edit_project.php?project_id=<?= $project_id ?>">Schaltplan erstellen/bearbeiten</a>

    <!-- Toolbox for Circuit Components -->
    <div class="toolbox">
        <button id="lineTool">Power Line</button>
        <button id="switchTool">Switch</button>
        <button id="lampTool">Lamp</button>
        <button id="andGateTool">AND Gate</button>
        <button id="powerSourceTool">Power Source</button>

 main
        <?php while ($design = $designs_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($design['name']) ?></td>
                <td>
                    <a href="view_design.php?design_id=<?= $design['id'] ?>">Anzeigen</a>
                    <a href="download_design.php?design_id=<?= $design['id'] ?>">Download</a>
                    <a href="edit_design.php?design_id=<?= $design['id'] ?>&project_id=<?= $project_id ?>">Bearbeiten</a>
                    <a href="design_history.php?design_id=<?= $design['id'] ?>">Versionen</a>

                    <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'owner' || $_SESSION['role'] == 'project_admin'): ?>
                        <a href="delete_design.php?design_id=<?= $design['id'] ?>&project_id=<?= $project_id ?>">Löschen</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <div class="project-layout">
        <div class="files-section">
            <a href="download_project_designs.php?project_id=<?= $project_id ?>">Alle Schaltpläne als ZIP herunterladen</a>
            <a href="file_explorer.php">Zurück zum Dateiexplorer</a>
        </div>
        <div class="draw-section">
            <div class="toolbox">
                <button id="lineTool">Leitung</button>
                <button id="circleTool">Kreis</button>
                <button id="freehandTool">Freihand</button>
                <button id="switchTool">Schalter</button>
                <button id="lampTool">Lampe</button>
                <button id="andGateTool">AND-Gatter</button>
                <button id="powerSourceTool">Spannungsquelle</button>
                <input type="color" id="lineColor" value="#000000">
                <input type="number" id="lineWidth" value="2" min="1" max="10">
            </div>
            <canvas id="circuitCanvas" width="1000" height="800"></canvas>
            <div id="coordinates"></div>
            <button class="run-simulation" id="runSimulation">Simulation</button>
            <button id="undoBtn">Undo</button>
            <button id="redoBtn">Redo</button>
            <button id="exportJson">Export JSON</button>
            <button id="importJson">Import JSON</button>
            <input type="file" id="importFile" style="display:none" accept="application/json">
        </div>
    </div>
</div>

<script>


<script>
    const canvas = document.getElementById('circuitCanvas');
    const ctx = canvas.getContext('2d');
    let drawingMode = 'select';
    let currentTool = 'select';
    let freehandPath = [];
const components = [];
const undoStack = [];
const redoStack = [];

    canvas.addEventListener('mousemove', e => {
        document.getElementById('coordinates').textContent = `x:${e.offsetX} y:${e.offsetY}`;
    });

    document.getElementById('lineTool').addEventListener('click', () => drawingMode = 'line');
    document.getElementById('circleTool').addEventListener('click', () => drawingMode = 'circle');
    document.getElementById('freehandTool').addEventListener('click', () => drawingMode = 'freehand');
    document.getElementById('switchTool').addEventListener('click', () => drawingMode = 'switch');
    document.getElementById('lampTool').addEventListener('click', () => drawingMode = 'lamp');
    document.getElementById('andGateTool').addEventListener('click', () => drawingMode = 'andGate');
    document.getElementById('powerSourceTool').addEventListener('click', () => drawingMode = 'powerSource');

    function saveState() {
        undoStack.push(JSON.stringify(components));
        if (undoStack.length > 50) undoStack.shift();
        redoStack.length = 0;
    }

    // Load saved components from localStorage
    let savedComponents = JSON.parse(localStorage.getItem('components')) || [];
    savedComponents.forEach(saved => {
        let component;
        switch (saved.type) {
            case 'line':
                component = new Line(saved.x, saved.y, saved.endX, saved.endY, saved.color, saved.width);
                break;
            case 'circle':
                component = new Circle(saved.x, saved.y, saved.radius, saved.color, saved.width);
                break;
            case 'freehand':
                component = new Freehand(saved.points, saved.color, saved.width);
                break;
            case 'switch':
                component = new Switch(saved.x, saved.y, saved.state);
                break;
            case 'lamp':
                component = new Lamp(saved.x, saved.y, saved.on);
                break;
            case 'andGate':
                component = new ANDGate(saved.x, saved.y);
                break;
            case 'powerSource':
                component = new PowerSource(saved.x, saved.y);
                break;
        }
        components.push(component);
    });

    function restoreState(state) {
        components.length = 0;
        JSON.parse(state).forEach(saved => {
            let component;
            switch (saved.type) {
                case 'line':
                    component = new Line(saved.x, saved.y, saved.endX, saved.endY, saved.color, saved.width);
                    break;
                case 'circle':
                    component = new Circle(saved.x, saved.y, saved.radius, saved.color, saved.width);
                    break;
                case 'freehand':
                    component = new Freehand(saved.points, saved.color, saved.width);
                    break;
                case 'switch':
                    component = new Switch(saved.x, saved.y, saved.state);
                    break;
                case 'lamp':
                    component = new Lamp(saved.x, saved.y, saved.on);
                    break;
                case 'andGate':
                    component = new ANDGate(saved.x, saved.y);
                    break;
                case 'powerSource':
                    component = new PowerSource(saved.x, saved.y);
                    break;
            }
            components.push(component);
        });
    }
    let savedComponents = JSON.parse(localStorage.getItem('components')) || [];
    savedComponents.forEach(saved => {
        let component;
        switch (saved.type) {
            case 'line':
                component = new Line(saved.x, saved.y, saved.endX, saved.endY, saved.color, saved.width);
                break;
            case 'circle':
                component = new Circle(saved.x, saved.y, saved.radius, saved.color, saved.width);
                break;
            case 'freehand':
                component = new Freehand(saved.points, saved.color, saved.width);
                break;
            case 'switch':
                component = new Switch(saved.x, saved.y, saved.state);
                break;
            case 'lamp':
                component = new Lamp(saved.x, saved.y, saved.on);
                break;
            case 'andGate':
                component = new ANDGate(saved.x, saved.y);
                break;
            case 'powerSource':
                component = new PowerSource(saved.x, saved.y);
                break;
        }
        components.push(component);
    });

    // Draw grid on the canvas
    function drawGrid() {
        ctx.strokeStyle = '#ddd';
        for (let x = 0; x < canvas.width; x += 40) {
            ctx.beginPath();
            ctx.moveTo(x, 0);
            ctx.lineTo(x, canvas.height);
            ctx.stroke();
        }
        for (let y = 0; y < canvas.height; y += 40) {
            ctx.beginPath();
            ctx.moveTo(0, y);
            ctx.lineTo(canvas.width, y);
            ctx.stroke();
        }
    }

    // Draw all components on the canvas
    function drawComponents() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        drawGrid();
        components.forEach(comp => comp.draw());
    }

    // Component classes with input/output connections
    class Line {
        constructor(x, y, endX = x, endY = y, color = '#000', width = 2) {
            this.x = x;
            this.y = y;
            this.endX = endX;
            this.endY = endY;
            this.color = color;
            this.width = width;
            this.type = 'line';
        }

        draw() {
            ctx.strokeStyle = this.color;
            ctx.lineWidth = this.width;
            ctx.beginPath();
            ctx.moveTo(this.x, this.y);
            ctx.lineTo(this.endX, this.endY);
            ctx.stroke();
        }



    // Draw grid on the canvas
    function drawGrid() {
        ctx.strokeStyle = '#ddd';
        for (let x = 0; x < canvas.width; x += 40) {
            ctx.beginPath();
            ctx.moveTo(x, 0);
            ctx.lineTo(x, canvas.height);
            ctx.stroke();
        }
        for (let y = 0; y < canvas.height; y += 40) {
            ctx.beginPath();
            ctx.moveTo(0, y);
            ctx.lineTo(canvas.width, y);
            ctx.stroke();
        }
    }

    // Draw all components on the canvas
    function drawComponents() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        drawGrid();
        components.forEach(comp => comp.draw());
    }


   main
    class Line {
        constructor(x, y, endX = x, endY = y, color = '#000', width = 2) {
            this.x = x;
            this.y = y;
            this.endX = endX;
            this.endY = endY;
            this.color = color;
            this.width = width;
            this.type = 'line';
        }

        draw() {
            ctx.strokeStyle = this.color;
            ctx.lineWidth = this.width;
            ctx.beginPath();
            ctx.moveTo(this.x, this.y);
            ctx.lineTo(this.endX, this.endY);
            ctx.stroke();
        }

        setEnd(x, y) {
            if (Math.abs(this.x - x) < Math.abs(this.y - y)) {
                this.endX = this.x; // vertical line
                this.endY = y;
            } else {
                this.endX = x; // horizontal line
                this.endY = this.y;
            }
        }

        isClicked(x, y) {
            // Check if click is near the line for erasing
            return Math.abs(this.x - x) < 10 && Math.abs(this.y - y) < 10 || Math.abs(this.endX - x) < 10 && Math.abs(this.endY - y) < 10;
        }
    }

    class Switch {
        constructor(x, y, state = false) {
            this.x = x;
            this.y = y;
            this.type = 'switch';
            this.state = state;
        }

        draw() {
            ctx.fillStyle = this.state ? 'green' : 'red';
            ctx.fillRect(this.x - 10, this.y - 10, 20, 20);
            ctx.strokeText('SW', this.x - 15, this.y - 20);
            ctx.strokeStyle = 'black';
            // Draw connection points
            ctx.beginPath();
            ctx.arc(this.x - 20, this.y, 5, 0, Math.PI * 2); // input
            ctx.arc(this.x + 20, this.y, 5, 0, Math.PI * 2); // output
            ctx.stroke();
        }

        toggle() {
            this.state = !this.state;
        }

        isClicked(x, y) {
            return x > this.x - 10 && x < this.x + 10 && y > this.y - 10 && y < this.y + 10;
        }
    }



        setEnd(x, y) {
            if (Math.abs(this.x - x) < Math.abs(this.y - y)) {
                this.endX = this.x; // vertical line
                this.endY = y;
            } else {
                this.endX = x; // horizontal line
                this.endY = this.y;
            }
        }

        isClicked(x, y) {
            // Check if click is near the line for erasing
            return Math.abs(this.x - x) < 10 && Math.abs(this.y - y) < 10 || Math.abs(this.endX - x) < 10 && Math.abs(this.endY - y) < 10;
        }
    }

    class Switch {
        constructor(x, y, state = false) {
            this.x = x;
            this.y = y;
            this.type = 'switch';
            this.state = state;
        }

        draw() {
            ctx.fillStyle = this.state ? 'green' : 'red';
            ctx.fillRect(this.x - 10, this.y - 10, 20, 20);
            ctx.strokeText('SW', this.x - 15, this.y - 20);
            ctx.strokeStyle = 'black';
            // Draw connection points
            ctx.beginPath();
            ctx.arc(this.x - 20, this.y, 5, 0, Math.PI * 2); // input
            ctx.arc(this.x + 20, this.y, 5, 0, Math.PI * 2); // output
            ctx.stroke();
        }

        toggle() {
            this.state = !this.state;
        }

        isClicked(x, y) {
            return x > this.x - 10 && x < this.x + 10 && y > this.y - 10 && y < this.y + 10;
        }
    }

    class Lamp {
        constructor(x, y, on = false) {
            this.x = x;
            this.y = y;
            this.type = 'lamp';
            this.on = on;
        }

 main
    class Lamp {
        constructor(x, y, on = false) {
            this.x = x;
            this.y = y;
            this.type = 'lamp';
            this.on = on;
        }

        draw() {
            ctx.fillStyle = this.on ? 'yellow' : 'gray';
            ctx.beginPath();
            ctx.arc(this.x, this.y, 15, 0, Math.PI * 2);
            ctx.fill();
            ctx.strokeText('L', this.x - 5, this.y - 20);
            ctx.strokeStyle = 'black';
            // Draw connection points
            ctx.beginPath();
            ctx.arc(this.x - 20, this.y, 5, 0, Math.PI * 2); // input
            ctx.stroke();
        }

        isClicked(x, y) {
            return Math.abs(this.x - x) < 20 && Math.abs(this.y - y) < 20;
        }
    }

    class Circle {
        constructor(x, y, radius = 20, color = '#000', width = 2) {
            this.x = x;
            this.y = y;
            this.radius = radius;
            this.color = color;
            this.width = width;
            this.type = 'circle';
        }

        draw() {
            ctx.strokeStyle = this.color;
            ctx.lineWidth = this.width;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            ctx.stroke();
        }

        isClicked(x, y) {
            return Math.hypot(this.x - x, this.y - y) <= this.radius + 5;
        }
    }

    class Freehand {
        constructor(points = [], color = '#000', width = 2) {
            this.points = points;
            this.color = color;
            this.width = width;
            this.type = 'freehand';
        }

        draw() {
            if (this.points.length < 2) return;
            ctx.strokeStyle = this.color;
            ctx.lineWidth = this.width;
            ctx.beginPath();
            ctx.moveTo(this.points[0].x, this.points[0].y);
            for (let i = 1; i < this.points.length; i++) {
                ctx.lineTo(this.points[i].x, this.points[i].y);
            }
            ctx.stroke();
        }

        isClicked(x, y) {
            return false;
        }
    }



        draw() {
            ctx.fillStyle = this.on ? 'yellow' : 'gray';
            ctx.beginPath();
            ctx.arc(this.x, this.y, 15, 0, Math.PI * 2);
            ctx.fill();
            ctx.strokeText('L', this.x - 5, this.y - 20);
            ctx.strokeStyle = 'black';
            // Draw connection points
            ctx.beginPath();
            ctx.arc(this.x - 20, this.y, 5, 0, Math.PI * 2); // input
            ctx.stroke();
        }

 main
        isClicked(x, y) {
            return Math.abs(this.x - x) < 20 && Math.abs(this.y - y) < 20;
        }
    }

    class Circle {
        constructor(x, y, radius = 20, color = '#000', width = 2) {
            this.x = x;
            this.y = y;
            this.radius = radius;
            this.color = color;
            this.width = width;
            this.type = 'circle';
        }

        draw() {
            ctx.strokeStyle = this.color;
            ctx.lineWidth = this.width;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            ctx.stroke();
        }

        isClicked(x, y) {
            return Math.hypot(this.x - x, this.y - y) <= this.radius + 5;
        }
    }

    class Freehand {
        constructor(points = [], color = '#000', width = 2) {
            this.points = points;
            this.color = color;
            this.width = width;
            this.type = 'freehand';
        }

        draw() {
            if (this.points.length < 2) return;
            ctx.strokeStyle = this.color;
            ctx.lineWidth = this.width;
            ctx.beginPath();
            ctx.moveTo(this.points[0].x, this.points[0].y);
            for (let i = 1; i < this.points.length; i++) {
                ctx.lineTo(this.points[i].x, this.points[i].y);
            }
            ctx.stroke();
        }

        isClicked(x, y) {
            return false;
        }
    }

    class ANDGate {
        constructor(x, y) {
            this.x = x;
            this.y = y;
            this.type = 'andGate';
        }

        draw() {
            ctx.fillStyle = 'blue';
            ctx.beginPath();
            ctx.moveTo(this.x - 20, this.y - 20);
            ctx.lineTo(this.x + 20, this.y - 20);
            ctx.lineTo(this.x + 20, this.y + 20);
            ctx.lineTo(this.x - 20, this.y + 20);
            ctx.closePath();
            ctx.fill();
            ctx.strokeText('AND', this.x - 25, this.y - 25);
            ctx.strokeStyle = 'black';
            // Draw connection points
            ctx.beginPath();
            ctx.arc(this.x - 30, this.y, 5, 0, Math.PI * 2); // input 1
            ctx.arc(this.x - 30, this.y + 20, 5, 0, Math.PI * 2); // input 2
            ctx.arc(this.x + 30, this.y, 5, 0, Math.PI * 2); // output
            ctx.stroke();
        }

        isClicked(x, y) {
            return Math.abs(this.x - x) < 20 && Math.abs(this.y - y) < 20;
        }
    }

    class PowerSource {
        constructor(x, y) {
            this.x = x;
            this.y = y;
            this.type = 'powerSource';
        }

        draw() {
            ctx.fillStyle = 'orange';
            ctx.fillRect(this.x - 15, this.y - 15, 30, 30);
            ctx.strokeText('+', this.x - 25, this.y - 25);
            ctx.strokeText('-', this.x + 10, this.y + 25);
            ctx.strokeStyle = 'black';
            // Draw positive and negative connection points
            ctx.beginPath();
            ctx.arc(this.x - 25, this.y, 5, 0, Math.PI * 2); // positive
            ctx.arc(this.x + 25, this.y, 5, 0, Math.PI * 2); // negative
            ctx.stroke();
        }

        isClicked(x, y) {
            return Math.abs(this.x - x) < 20 && Math.abs(this.y - y) < 20;
        }
    }

    // Click event to handle component actions
    canvas.addEventListener('click', (e) => {
        const x = Math.round(e.offsetX / 40) * 40;
        const y = Math.round(e.offsetY / 40) * 40;

        // If using eraser, remove component at click location
        if (currentTool === 'eraser') {
            const index = components.findIndex(comp => comp.isClicked(x, y));
            if (index > -1) {
                saveState();
                components.splice(index, 1);
                saveComponents();
                drawComponents();
            }
            return;
        }

        let component;
        const color = document.getElementById('lineColor').value;
        const width = parseInt(document.getElementById('lineWidth').value,10);
        switch (drawingMode) {
            case 'line':
                component = new Line(x, y, x, y, color, width);
                canvas.addEventListener('mousemove', mouseMoveHandler);
                canvas.addEventListener('mouseup', mouseUpHandler);
                break;
            case 'circle':
                component = new Circle(x, y, 20, color, width);
                break;
            case 'freehand':
                freehandPath = [{x,y}];
                component = new Freehand(freehandPath, color, width);
                canvas.addEventListener('mousemove', freehandMove);
                canvas.addEventListener('mouseup', freehandUp);
                break;
            case 'switch':
                component = new Switch(x, y);
                break;



    class ANDGate {
        constructor(x, y) {
            this.x = x;
            this.y = y;
            this.type = 'andGate';
        }

        draw() {
            ctx.fillStyle = 'blue';
            ctx.beginPath();
            ctx.moveTo(this.x - 20, this.y - 20);
            ctx.lineTo(this.x + 20, this.y - 20);
            ctx.lineTo(this.x + 20, this.y + 20);
            ctx.lineTo(this.x - 20, this.y + 20);
            ctx.closePath();
            ctx.fill();
            ctx.strokeText('AND', this.x - 25, this.y - 25);
            ctx.strokeStyle = 'black';
            // Draw connection points
            ctx.beginPath();
            ctx.arc(this.x - 30, this.y, 5, 0, Math.PI * 2); // input 1
            ctx.arc(this.x - 30, this.y + 20, 5, 0, Math.PI * 2); // input 2
            ctx.arc(this.x + 30, this.y, 5, 0, Math.PI * 2); // output
            ctx.stroke();
        }

        isClicked(x, y) {
            return Math.abs(this.x - x) < 20 && Math.abs(this.y - y) < 20;
        }
    }

    class PowerSource {
        constructor(x, y) {
            this.x = x;
            this.y = y;
            this.type = 'powerSource';
        }

        draw() {
            ctx.fillStyle = 'orange';
            ctx.fillRect(this.x - 15, this.y - 15, 30, 30);
            ctx.strokeText('+', this.x - 25, this.y - 25);
            ctx.strokeText('-', this.x + 10, this.y + 25);
            ctx.strokeStyle = 'black';
            // Draw positive and negative connection points
            ctx.beginPath();
            ctx.arc(this.x - 25, this.y, 5, 0, Math.PI * 2); // positive
            ctx.arc(this.x + 25, this.y, 5, 0, Math.PI * 2); // negative
            ctx.stroke();
        }

        isClicked(x, y) {
            return Math.abs(this.x - x) < 20 && Math.abs(this.y - y) < 20;
        }
    }

    // Click event to handle component actions
    canvas.addEventListener('click', (e) => {
        const x = Math.round(e.offsetX / 40) * 40;
        const y = Math.round(e.offsetY / 40) * 40;

        // If using eraser, remove component at click location
 main
        if (currentTool === 'eraser') {
            const index = components.findIndex(comp => comp.isClicked(x, y));
            if (index > -1) {
                saveState();
                components.splice(index, 1);
                saveComponents();
                drawComponents();
            }
            return;
        }



 main
        let component;
        const color = document.getElementById('lineColor').value;
        const width = parseInt(document.getElementById('lineWidth').value,10);
        switch (drawingMode) {
            case 'line':
                component = new Line(x, y, x, y, color, width);
                canvas.addEventListener('mousemove', mouseMoveHandler);
                canvas.addEventListener('mouseup', mouseUpHandler);
                break;
            case 'circle':
                component = new Circle(x, y, 20, color, width);
                break;
            case 'freehand':
                freehandPath = [{x,y}];
                component = new Freehand(freehandPath, color, width);
                canvas.addEventListener('mousemove', freehandMove);
                canvas.addEventListener('mouseup', freehandUp);
                break;
            case 'switch':
                component = new Switch(x, y);
                break;
 
            case 'lamp':
                component = new Lamp(x, y);
                break;
            case 'andGate':
                component = new ANDGate(x, y);
                break;
            case 'powerSource':
                component = new PowerSource(x, y);
                break;
            default:
                // Toggle switch if clicked on
                const clickedSwitch = components.find(comp => comp.type === 'switch' && comp.isClicked(x, y));
                if (clickedSwitch) {
                    saveState();
                    clickedSwitch.toggle();
                    drawComponents();
                    saveComponents();
                    return;
                }
                return;
        }

        if (component) {
            // Snap component to nearest connection point if possible
            snapToClosestConnection(component);
            saveState();
            components.push(component);
            saveComponents();
            drawComponents();
        }


            case 'lamp':
                component = new Lamp(x, y);
                break;
            case 'andGate':
                component = new ANDGate(x, y);
                break;
            case 'powerSource':
                component = new PowerSource(x, y);
                break;
            default:
                // Toggle switch if clicked on
 main
                const clickedSwitch = components.find(comp => comp.type === 'switch' && comp.isClicked(x, y));
                if (clickedSwitch) {
                    saveState();
                    clickedSwitch.toggle();
                    drawComponents();
                    saveComponents();
                    return;
                }
                return;
        }

        if (component) {
            // Snap component to nearest connection point if possible


        if (component) {
            // Snap component to nearest connection point if possible
 main
            snapToClosestConnection(component);
            saveState();
            components.push(component);
            saveComponents();
            drawComponents();
        }
    });

    // Function to handle snapping of components and lines
    function snapToClosestConnection(component) {
        components.forEach(existingComponent => {
            if (existingComponent !== component) {
                // Check proximity of connection points (simplified for demo purposes)
                if (Math.abs(existingComponent.x - component.x) < 20 &&
                    Math.abs(existingComponent.y - component.y) < 20) {
                    component.x = existingComponent.x;
                    component.y = existingComponent.y;
                }
            }
        });
    }

    // Save components to localStorage
    function saveComponents() {
        localStorage.setItem('components', JSON.stringify(components));
    }

    // Handle line drawinfunction mouseMoveHandler(e) {
function mouseMoveHandler(e) {
        const lastComponent = components[components.length - 1];
        if (lastComponent && lastComponent.type === 'line') {
            const x = Math.round(e.offsetX / 40) * 40;
            const y = Math.round(e.offsetY / 40) * 40;
            lastComponent.setEnd(x, y);
            snapToClosestConnection(lastComponent); // Snap line end to nearest component
            drawComponents();

    });

    // Function to handle snapping of components and lines
    function snapToClosestConnection(component) {
        components.forEach(existingComponent => {
            if (existingComponent !== component) {
                // Check proximity of connection points (simplified for demo purposes)
                if (Math.abs(existingComponent.x - component.x) < 20 &&
                    Math.abs(existingComponent.y - component.y) < 20) {
                    component.x = existingComponent.x;
                    component.y = existingComponent.y;
                }
            }
        });
    }

    // Save components to localStorage
    function saveComponents() {
        localStorage.setItem('components', JSON.stringify(components));
    }

    // Handle line drawing
function mouseMoveHandler(e) {
        const lastComponent = components[components.length - 1];
        if (lastComponent && lastComponent.type === 'line') {
            const x = Math.round(e.offsetX / 40) * 40;
            const y = Math.round(e.offsetY / 40) * 40;
            lastComponent.setEnd(x, y);
            snapToClosestConnection(lastComponent); // Snap line end to nearest component
            drawComponents();
}

    function freehandMove(e) {
        const x = e.offsetX;
        const y = e.offsetY;
        const lastComponent = components[components.length - 1];
        if (lastComponent && lastComponent.type === 'freehand') {
            lastComponent.points.push({x,y});
            drawComponents();
        }
    }

    function freehandUp() {
        canvas.removeEventListener('mousemove', freehandMove);
        canvas.removeEventListener('mouseup', freehandUp);
        saveComponents();
        drawComponents();
    }
 main
}

    function freehandMove(e) {
        const x = e.offsetX;
        const y = e.offsetY;
        const lastComponent = components[components.length - 1];
        if (lastComponent && lastComponent.type === 'freehand') {
            lastComponent.points.push({x,y});
            drawComponents();
        }
    }

    function freehandUp() {
        canvas.removeEventListener('mousemove', freehandMove);
        canvas.removeEventListener('mouseup', freehandUp);
        saveComponents();
        drawComponents();
    }
    }

    function mouseUpHandler() {
        canvas.removeEventListener('mousemove', mouseMoveHandler);
        canvas.removeEventListener('mouseup', mouseUpHandler);
        saveComponents();
        drawComponents();
    }


    }

    function mouseUpHandler() {
        canvas.removeEventListener('mousemove', mouseMoveHandler);
        canvas.removeEventListener('mouseup', mouseUpHandler);
        saveComponents();
        drawComponents();
    }

    // Run circuit simulation using a simple graph search
    document.getElementById('runSimulation').addEventListener('click', () => {
        const powerSource = components.find(comp => comp.type === 'powerSource');
        const lamp = components.find(comp => comp.type === 'lamp');
        if (!powerSource || !lamp) {
            alert('Power source or lamp missing.');
            return;
        }

        const graph = {};
        const addEdge = (a, b) => {
            const k1 = `${a.x},${a.y}`;
            const k2 = `${b.x},${b.y}`;
            graph[k1] = graph[k1] || [];
            graph[k2] = graph[k2] || [];
            graph[k1].push(k2);
            graph[k2].push(k1);
        };

        components.forEach(comp => {
            if (comp.type === 'line') {
                addEdge({x: comp.x, y: comp.y}, {x: comp.endX, y: comp.endY});
            } else if (comp.type === 'switch' && comp.state) {
                addEdge({x: comp.x - 20, y: comp.y}, {x: comp.x + 20, y: comp.y});
            } else if (comp.type === 'andGate') {
                addEdge({x: comp.x - 30, y: comp.y}, {x: comp.x + 30, y: comp.y});
            }
        });

        const start = {x: powerSource.x - 25, y: powerSource.y};
        const goal = {x: lamp.x - 20, y: lamp.y};
        const queue = [`${start.x},${start.y}`];
        const visited = new Set(queue);
        let found = false;

        while (queue.length > 0) {
            const node = queue.shift();
            if (node === `${goal.x},${goal.y}`) {
                found = true;
                break;
            }
            (graph[node] || []).forEach(next => {
                if (!visited.has(next)) {
                    visited.add(next);
                    queue.push(next);
                }
            });
        }

        lamp.on = found;
        drawComponents();
        alert(lamp.on ? 'Circuit is working!' : 'Incomplete circuit. Check connections.');
    });

    document.getElementById('undoBtn').addEventListener('click', () => {
        if (undoStack.length > 0) {
            redoStack.push(JSON.stringify(components));
            const state = undoStack.pop();
            restoreState(state);
            drawComponents();
            saveComponents();
        }
    });

    document.getElementById('redoBtn').addEventListener('click', () => {
        if (redoStack.length > 0) {
            undoStack.push(JSON.stringify(components));
            const state = redoStack.pop();
            restoreState(state);
            drawComponents();
            saveComponents();
        }
    });

    document.getElementById('exportJson').addEventListener('click', () => {
        const blob = new Blob([JSON.stringify(components)], {type: 'application/json'});
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'circuit.json';
        a.click();
        URL.revokeObjectURL(url);
    });

    document.getElementById('importJson').addEventListener('click', () => {
        document.getElementById('importFile').click();
    });

    document.getElementById('importFile').addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = () => {
            saveState();
            restoreState(reader.result);
            drawComponents();
            saveComponents();
        };
        reader.readAsText(file);
    });

    // Initial draw of the grid and components

    // Run circuit simulation using a simple graph search
    document.getElementById('runSimulation').addEventListener('click', () => {
        const powerSource = components.find(comp => comp.type === 'powerSource');
        const lamp = components.find(comp => comp.type === 'lamp');
        if (!powerSource || !lamp) {
            alert('Power source or lamp missing.');
            return;
        }

        const graph = {};
        const addEdge = (a, b) => {
            const k1 = `${a.x},${a.y}`;
            const k2 = `${b.x},${b.y}`;
            graph[k1] = graph[k1] || [];
            graph[k2] = graph[k2] || [];
            graph[k1].push(k2);
            graph[k2].push(k1);
        };

        components.forEach(comp => {
            if (comp.type === 'line') {
                addEdge({x: comp.x, y: comp.y}, {x: comp.endX, y: comp.endY});
            } else if (comp.type === 'switch' && comp.state) {
                addEdge({x: comp.x - 20, y: comp.y}, {x: comp.x + 20, y: comp.y});
            } else if (comp.type === 'andGate') {
                addEdge({x: comp.x - 30, y: comp.y}, {x: comp.x + 30, y: comp.y});
            }
        });

        const start = {x: powerSource.x - 25, y: powerSource.y};
        const goal = {x: lamp.x - 20, y: lamp.y};
        const queue = [`${start.x},${start.y}`];
        const visited = new Set(queue);
        let found = false;

        while (queue.length > 0) {
            const node = queue.shift();
            if (node === `${goal.x},${goal.y}`) {
                found = true;
                break;
            }
            (graph[node] || []).forEach(next => {
                if (!visited.has(next)) {
                    visited.add(next);
                    queue.push(next);
                }
            });
        }

        lamp.on = found;
        drawComponents();
        alert(lamp.on ? 'Circuit is working!' : 'Incomplete circuit. Check connections.');
    });

    document.getElementById('undoBtn').addEventListener('click', () => {
        if (undoStack.length > 0) {
            redoStack.push(JSON.stringify(components));
            const state = undoStack.pop();
            restoreState(state);
            drawComponents();
            saveComponents();
        }
    });

    document.getElementById('redoBtn').addEventListener('click', () => {
        if (redoStack.length > 0) {
            undoStack.push(JSON.stringify(components));
            const state = redoStack.pop();
            restoreState(state);
            drawComponents();
            saveComponents();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.key.toLowerCase() === 'z') {
            document.getElementById('undoBtn').click();
        } else if (e.ctrlKey && e.key.toLowerCase() === 'y') {
            document.getElementById('redoBtn').click();
        }
    });

    document.getElementById('exportJson').addEventListener('click', () => {
        const blob = new Blob([JSON.stringify(components)], {type: 'application/json'});
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'circuit.json';
        a.click();
        URL.revokeObjectURL(url);
    });

    document.getElementById('importJson').addEventListener('click', () => {
        document.getElementById('importFile').click();
    });

    document.getElementById('importFile').addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = () => {
            saveState();
            restoreState(reader.result);
            drawComponents();
            saveComponents();
        };
        reader.readAsText(file);
    });

    // Initial draw of the grid and components
 main
    // Run circuit simulation using a simple graph search
    document.getElementById('runSimulation').addEventListener('click', () => {
        const powerSource = components.find(comp => comp.type === 'powerSource');
        const lamp = components.find(comp => comp.type === 'lamp');
        if (!powerSource || !lamp) {
            alert('Power source or lamp missing.');
            return;
        }

        const graph = {};
        const addEdge = (a, b) => {
            const k1 = `${a.x},${a.y}`;
            const k2 = `${b.x},${b.y}`;
            graph[k1] = graph[k1] || [];
            graph[k2] = graph[k2] || [];
            graph[k1].push(k2);
            graph[k2].push(k1);
        };

        components.forEach(comp => {
            if (comp.type === 'line') {
                addEdge({x: comp.x, y: comp.y}, {x: comp.endX, y: comp.endY});
            } else if (comp.type === 'switch' && comp.state) {
                addEdge({x: comp.x - 20, y: comp.y}, {x: comp.x + 20, y: comp.y});
            } else if (comp.type === 'andGate') {
                addEdge({x: comp.x - 30, y: comp.y}, {x: comp.x + 30, y: comp.y});
            }
        });

        const start = {x: powerSource.x - 25, y: powerSource.y};
        const goal = {x: lamp.x - 20, y: lamp.y};
        const queue = [`${start.x},${start.y}`];
        const visited = new Set(queue);
        let found = false;

        while (queue.length > 0) {
            const node = queue.shift();
            if (node === `${goal.x},${goal.y}`) {
                found = true;
                break;
            }
            (graph[node] || []).forEach(next => {
                if (!visited.has(next)) {
                    visited.add(next);
                    queue.push(next);
                }
            });
        }

        lamp.on = found;
        drawComponents();
        alert(lamp.on ? 'Circuit is working!' : 'Incomplete circuit. Check connections.');
    });

    document.getElementById('undoBtn').addEventListener('click', () => {
        if (undoStack.length > 0) {
            redoStack.push(JSON.stringify(components));
            const state = undoStack.pop();
            restoreState(state);
            drawComponents();
            saveComponents();
        }
    });

    document.getElementById('redoBtn').addEventListener('click', () => {
        if (redoStack.length > 0) {
            undoStack.push(JSON.stringify(components));
            const state = redoStack.pop();
            restoreState(state);
            drawComponents();
            saveComponents();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.key.toLowerCase() === 'z') {
            document.getElementById('undoBtn').click();
        } else if (e.ctrlKey && e.key.toLowerCase() === 'y') {
            document.getElementById('redoBtn').click();
        }
    });

    document.getElementById('exportJson').addEventListener('click', () => {
        const blob = new Blob([JSON.stringify(components)], {type: 'application/json'});
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'circuit.json';
        a.click();
        URL.revokeObjectURL(url);
    });

    document.getElementById('importJson').addEventListener('click', () => {
        document.getElementById('importFile').click();
    });

    document.getElementById('importFile').addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = () => {
            saveState();
            restoreState(reader.result);
            drawComponents();
            saveComponents();
        };
        reader.readAsText(file);
    });

    // Initial draw of the grid and components
    drawGrid();
    drawComponents();
</script>

</body>
</html>
</html>

    drawGrid();
    drawComponents();
</script>

</body>
</html>

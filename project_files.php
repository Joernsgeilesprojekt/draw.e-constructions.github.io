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

<!-- HTML code remains the same -->
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projektdateien</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="header">
    <div class="logo">Logo</div>
    <div>
        <?= htmlspecialchars($_SESSION['username']) ?>
        <img src="img/user.png" alt="User Icon">
    </div>
</div>

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
    <a href="file_explorer.php">Zurück zum Dateiexplorer</a>
    <a href="edit_project.php?project_id=<?= $project_id ?>">Schaltplan erstellen/bearbeiten</a>

    <!-- Toolbox for Circuit Components -->
    <div class="toolbox">
        <button id="lineTool">Power Line</button>
        <button id="switchTool">Switch</button>
        <button id="lampTool">Lamp</button>
        <button id="andGateTool">AND Gate</button>
        <button id="powerSourceTool">Power Source</button>
    </div>

    <!-- Drawing Canvas -->
    <canvas id="circuitCanvas" width="1000" height="800"></canvas>

    <!-- Run Simulation Button -->
    <button class="run-simulation" id="runSimulation">Run Simulation</button>
    <button id="undoBtn">Undo</button>
    <button id="redoBtn">Redo</button>
    <button id="exportJson">Export JSON</button>
    <button id="importJson">Import JSON</button>
    <input type="file" id="importFile" style="display:none" accept="application/json">
</div>

<script>
    const canvas = document.getElementById('circuitCanvas');
    const ctx = canvas.getContext('2d');
    let drawingMode = 'select';
    let currentTool = 'select';
    const components = [];
    const undoStack = [];
    const redoStack = [];

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
                case 'line': component = new Line(saved.x, saved.y, saved.endX, saved.endY); break;
                case 'switch': component = new Switch(saved.x, saved.y, saved.state); break;
                case 'lamp': component = new Lamp(saved.x, saved.y, saved.on); break;
                case 'andGate': component = new ANDGate(saved.x, saved.y); break;
                case 'powerSource': component = new PowerSource(saved.x, saved.y); break;
            }
            components.push(component);
        });
    }

    // ... rest of the JavaScript code remains the same, with the above changes applied

    // Load saved components from localStorage
    let savedComponents = JSON.parse(localStorage.getItem('components')) || [];
    savedComponents.forEach(saved => {
        let component;
        switch (saved.type) {
            case 'line': component = new Line(saved.x, saved.y, saved.endX, saved.endY); break;
            case 'switch': component = new Switch(saved.x, saved.y, saved.state); break;
            case 'lamp': component = new Lamp(saved.x, saved.y, saved.on); break;
            case 'andGate': component = new ANDGate(saved.x, saved.y); break;
            case 'powerSource': component = new PowerSource(saved.x, saved.y); break;
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
        constructor(x, y, endX = x, endY = y) {
            this.x = x;
            this.y = y;
            this.endX = endX;
            this.endY = endY;
            this.type = 'line';
        }

        draw() {
            ctx.strokeStyle = 'black';
            ctx.lineWidth = 4;
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
        switch (drawingMode) {
            case 'line':
                component = new Line(x, y);
                canvas.addEventListener('mousemove', mouseMoveHandler);
                canvas.addEventListener('mouseup', mouseUpHandler);
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
    }

    function mouseUpHandler() {
        canvas.removeEventListener('mousemove', mouseMoveHandler);
        canvas.removeEventListener('mouseup', mouseUpHandler);
        saveComponents();
        drawComponents();
    }

    // Run circuit simulation
    document.getElementById('runSimulation').addEventListener('click', () => {
        // Basic simulation logic
        const powerSource = components.find(comp => comp.type === 'powerSource');
        const lamp = components.find(comp => comp.type === 'lamp');
        if (powerSource && lamp) {
            // Simplified: Check if there's a path between power source and lamp
            const connected = components.some(comp => comp.type === 'line' && comp.x === powerSource.x && comp.endX === lamp.x);
            lamp.on = connected;
        }
        drawComponents();
        alert(lamp && lamp.on ? 'Circuit is working!' : 'Incomplete circuit. Check connections.');
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
    drawGrid();
    drawComponents();
</script>

</body>
</html>
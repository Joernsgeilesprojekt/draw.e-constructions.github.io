document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('circuitCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let drawing = false;

    const colorPicker = document.getElementById('colorPicker');
    const widthInput = document.getElementById('lineWidth');
    const clearButton = document.getElementById('clearCanvas');

    const getColor = () => (colorPicker ? colorPicker.value : '#000000');
    const getWidth = () => (widthInput ? parseInt(widthInput.value, 10) || 1 : 1);

    canvas.addEventListener('mousedown', (e) => {
        drawing = true;
        ctx.beginPath();
        ctx.moveTo(e.offsetX, e.offsetY);
    });

    canvas.addEventListener('mousemove', (e) => {
        if (!drawing) return;
        ctx.strokeStyle = getColor();
        ctx.lineWidth = getWidth();
        ctx.lineTo(e.offsetX, e.offsetY);
        ctx.stroke();
    });

    const stopDrawing = () => {
        drawing = false;
    };

    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseleave', stopDrawing);

    if (clearButton) {
        clearButton.addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        });
    }
});

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
            location.reload();  // Seite neu laden, um die gespeicherten Designs anzuzeigen
        } else {
            alert('Fehler beim Speichern des Schaltplans.');
        }
    };
    xhr.send('image_data=' + encodeURIComponent(dataURL) + '&project_id=' + project_id);
}

window.onload = function() {
    const canvas = document.getElementById('circuitCanvas');
    const ctx = canvas.getContext('2d');
    let drawing = false;

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

    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'save_design.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            alert('Schaltplan gespeichert!');
            location.reload();  
        } else {
            alert('Fehler beim Speichern des Schaltplans.');
        }
    };
    xhr.send('image_data=' + encodeURIComponent(dataURL) + '&project_id=' + project_id);
}

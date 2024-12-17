from flask import Flask, Response
from picamera import PiCamera
from threading import Condition
import time

# Flask-Setup
app = Flask(__name__)
camera = PiCamera()
camera.resolution = (640, 480)
output = None
condition = Condition()

# Kamerastreaming-Klasse
class CameraOutput:
    def __init__(self):
        self.frame = None

    def write(self, buf):
        global output
        if buf.startswith(b'\xff\xd8'):
            with condition:
                self.frame = buf
                condition.notify_all()
        output = self

output = CameraOutput()
camera.start_recording(output, format='mjpeg')

@app.route('/')
def index():
    """ Startseite der Webseite """
    return ("<html><head><title>Raspberry Pi Kamera Stream</title></head>"
            "<body><h1>Live Kamera Stream</h1>"
            "<img src='/video_feed'>"
            "</body></html>")

@app.route('/video_feed')
def video_feed():
    """ MJPEG-Stream von der Kamera """
    def generate():
        while True:
            with condition:
                condition.wait()
                frame = output.frame
            yield (b'--frame\r\n'
                   b'Content-Type: image/jpeg\r\n\r\n' + frame + b'\r\n')
    return Response(generate(), mimetype='multipart/x-mixed-replace; boundary=frame')

if __name__ == '__main__':
    try:
        print("Starte Server auf http://0.0.0.0:5000")
        app.run(host='0.0.0.0', port=5000, debug=False, threaded=True)
    except KeyboardInterrupt:
        print("Beende Server...")
        camera.stop_recording()

<?php
session_start();
require 'config.php';
require 'csrf.php';
csrf_verify();

<?php
session_start();
require 'config.php';
require 'csrf.php';
csrf_verify();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // Sicherstellen, dass die Rolle gesetzt wird

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // Sicherstellen, dass die Rolle gesetzt wird
        header('Location: dashboard.php');
    } else {
        echo "Invalid username or password.";
    }
}
?>
<form method="POST" action="login.php">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>
<div class="texthead">
    <a href="impressum.php">Impressum</a> |
    <a href="datenschutz.php">Datenschutz</a>
</div>
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('service-worker.js').catch(() => {});
}
</script>
<script src="cookie.js"></script>
<form method="POST" action="login.php">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>
<div class="texthead">
    <a href="impressum.php">Impressum</a> |
    <a href="datenschutz.php">Datenschutz</a>
</div>
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('service-worker.js').catch(() => {});
}
</script>
<script src="cookie.js"></script>

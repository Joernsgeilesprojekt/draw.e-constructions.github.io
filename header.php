<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<div class="header">
    <div class="logo">Logo</div>
    <div>
        <?= htmlspecialchars($_SESSION['username'] ?? '') ?>
        <img src="img/user.png" alt="User Icon">
        <button id="themeToggle" style="margin-left:10px;">🌓</button>
    </div>
</div>
<script>
document.getElementById('themeToggle').addEventListener('click', () => {
    document.body.classList.toggle('dark');
});
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('service-worker.js').catch(() => {});
}
</script>
<script src="cookie.js"></script>
<div class="texthead">
    <a href="impressum.php">Impressum</a> |
    <a href="datenschutz.php">Datenschutz</a>
</div>

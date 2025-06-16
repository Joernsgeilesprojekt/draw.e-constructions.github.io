<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require 'config.php';
$design_id = filter_input(INPUT_GET, 'design_id', FILTER_VALIDATE_INT);
$stmt = $conn->prepare("SELECT id, created_at FROM design_versions WHERE design_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $design_id);
$stmt->execute();
$versions = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Versionen</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <h1>Versionsverlauf</h1>
    <ul>
        <?php while ($v = $versions->fetch_assoc()): ?>
        <li><a href="view_version.php?version_id=<?= $v['id'] ?>">Version vom <?= htmlspecialchars($v['created_at']) ?></a></li>
        <?php endwhile; ?>
    </ul>
</div>
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
</body>
</html>

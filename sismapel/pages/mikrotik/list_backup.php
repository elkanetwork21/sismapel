<?php
$backup_dir = __DIR__ . "/backups/";

if (!is_dir($backup_dir)) {
    die("❌ Folder backup belum ada.");
}

$files = array_diff(scandir($backup_dir), ['.', '..']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Backup MikroTik</title>
</head>
<body>
    <h2>📂 Daftar File Backup MikroTik</h2>
    <ul>
        <?php foreach ($files as $file): ?>
            <li>
                <?= htmlspecialchars($file) ?> 
                - <a href="download.php?file=<?= urlencode($file) ?>">Download</a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>

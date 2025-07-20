<?php

$title = isset($data['title']) ? $data['title'] : 'Web Torrent';
$description = isset($data['description']) ? $data['description'] : 'A simple web-based torrent client';
$js = isset($data['jsPaths']) ? $data['jsPaths'] : [];
$css = isset($data['cssPaths']) ? $data['cssPaths'] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($description); ?>">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <?php foreach ($css as $path): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($path); ?>">
    <?php endforeach; ?>
    <?php foreach ($js as $path): ?>
        <script src="<?php echo htmlspecialchars($path); ?>"></script>
    <?php endforeach; ?>
</head>
<body>
    <header>
        <h1><?php echo htmlspecialchars($title); ?></h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="?page=upload">Upload Torrent</a></li>
                <li><a href="?page=downloads">Downloads</a></li>
                <li><a href="?page=torrents">Torrents</a></li>
            </ul>
        </nav>
    </header>

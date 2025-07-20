<?php 

?>

<div class="container">
    <h1>Welcome to Web Torrent</h1>
    <p>Use the navigation menu to upload and manage your torrent files.</p>
    <p>Current Torrent Directory: <?php echo htmlspecialchars($torrentDir); ?></p>
    <p>Current Upload Directory: <?php echo htmlspecialchars($uploadDir); ?></p>
    <h2>Uploaded Torrents</h2>
    <ul id="torrentList">
        <?php foreach ($torrentFiles as $file): ?>
            <li data-file-name="<?php echo htmlspecialchars($file['name']); ?>"><?php echo htmlspecialchars($file['name']); ?> (<?php echo htmlspecialchars($file['size']); ?> bytes)</li>
        <?php endforeach; ?>
    </ul>
    <p><a href="?page=upload">Upload a new torrent</a></p>
</div>
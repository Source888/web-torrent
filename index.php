<?php
$rootDir = __DIR__;
define('TORRENT_DIR', $rootDir.'/torrents/');
define('UPLOAD_DIR', $rootDir.'/uploads/');
define('RPC_URL', 'http://localhost:9091/transmission/rpc');

require_once 'classes/MySQL.php';
require_once 'classes/TorrentHandler.php';
require_once 'classes/DownloadsHandler.php';

// Initialize database and create tables if they do not exist
$db = new MySQL();
$db->createDatabaseTables();
$db->close();

function show_files() {
    $downloadsHandler = new DownloadsHandler();
    $files = $downloadsHandler->listUploadedFiles();
    foreach ($files as $file) {
        echo "<li>" . htmlspecialchars($file) . " <a href='?delete=" . urlencode($file) . "'>Delete</a></li>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['torrent_file'])) {
    $torrentHandler = new TorrentHandler();
    if ($torrentHandler->addTorrent($_FILES['torrent_file']['tmp_name'])) {
        echo "Torrent file uploaded successfully.";
    } else {
        echo "Failed to upload torrent file.";
    }
}

if (isset($_GET['delete'])) {
    $torrentHandler = new TorrentHandler();
    if ($torrentHandler->deleteFile($_GET['delete'])) {
        echo "File deleted successfully.";
    } else {
        echo "Failed to delete file.";
    }
}

echo "<h1>Uploaded Torrent Files</h1>";
echo "<ul>";
show_files();
echo "</ul>";
?>
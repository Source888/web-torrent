<?php
require_once 'classes/MySQL.php';
require_once 'classes/TorrentHandler.php';
require_once 'classes/DownloadsHandler.php';
require_once 'classes/ViewController.php';

class RouteController {
    private $db;
    private $torrentHandler;
    private $downloadsHandler;
    private $viewController;

    public function __construct() {
        $this->db = new MySQL();
        $this->torrentHandler = new TorrentHandler();
        $this->downloadsHandler = new DownloadsHandler();
        $this->viewController = new ViewController();
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
                $input = file_get_contents('php://input');
                $_POST = json_decode($input, true);
            }
            $action = isset($_POST['action']) ? $_POST['action'] : '';
            switch ($action) {
                case 'start_download':
                    if (isset($_POST['torrent_file'])) {
                        $torrentFile = $_POST['torrent_file'];
                        $this->startDownload($torrentFile);
                    }
                    break;
                case 'upload_torrent':
                    if (isset($_FILES['torrentFile']) && $_FILES['torrentFile']['error'] === UPLOAD_ERR_OK) {
                        $this->handleTorrentUpload($_FILES['torrentFile']);
                    } else {
                        echo "No torrent file uploaded or there was an error.";
                    }
                    break;
                case 'delete_file':
                    if (isset($_POST['file_name'])) {
                        $fileName = $_POST['file_name'];
                        if ($this->downloadsHandler->deleteFile($fileName)) {
                            header('Location: ?page=files');
                        } else {
                            echo "Failed to delete file.";
                        }
                    }
                    break;
                default:
                    var_dump($_POST);
                    $this->indexPage();
            }

        } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $page = isset($_GET['page']) ? $_GET['page'] : 'index';
            switch ($page) {
                case 'index':
                    $this->indexPage();
                    break;
                case 'upload':

                    $this->showUploadForm();
                    break;
                case 'files':
                    $this->showFiles();
                    break;
                case 'torrents':
                    $this->showTorrents();
                    break;
                default:
                    $this->indexPage();
            }
        }
    }
    public function indexPage(){
        $torrentDir = $this->torrentHandler->getTorrentDir();
        $uploadDir = $this->downloadsHandler->getUploadDir();
        $torrentFiles = $this->torrentHandler->listTorrentFiles();
        $this->viewController->showIndexPage($torrentDir, $uploadDir, $torrentFiles);
    }

    private function startDownload($torrentFile) {
        if ($this->torrentHandler->startDownload($torrentFile)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Torrent file uploaded successfully.']);
            return;
        } else {
            echo "Failed to upload torrent file.";
        }
    }

    private function handleTorrentUpload($torrentFile) {
        if ($this->torrentHandler->addTorrent($torrentFile['tmp_name'])) {
            header('Location: ?page=index');
        } else {
            echo "Failed to upload torrent file.";
        }
    }
    private function showUploadForm() {
        $title = 'Upload Torrent';
        $this->viewController->showUploadForm($title);
    }

    private function showTorrents() {
        $torrents = $this->torrentHandler->listAllTorrentsFromTransmission();
        $this->viewController->showTorrentsPage($torrents);
    }


    private function showFiles() {
        $files = $this->downloadsHandler->listUploadedFiles();
        foreach ($files as $file) {
            echo "<li>" . htmlspecialchars($file) . " <a href='?delete=" . urlencode($file) . "'>Delete</a></li>";
        }
    }
}
<?php

class DownloadsHandler {
    private $torrentDir;
    private $uploadDir;

    public function __construct() {
        $this->torrentDir = TORRENT_DIR;
        $this->uploadDir = UPLOAD_DIR;
    }

    public function getTorrentDir() {
        return $this->torrentDir;
    }

    public function getUploadDir() {
        return $this->uploadDir;
    }

    public function listUploadedFiles() {
        $files = scandir($this->uploadDir);
        $files = array_diff($files, ['.', '..']);
        return $files;
    }

    public function deleteFile($fileName) {
        $filePath = $this->uploadDir . basename($fileName);
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                return true;
            }
        }
        return false;
    }




}
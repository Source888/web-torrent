<?php
require_once 'MySQL.php';


class TorrentHandler {
    private $torrentDir;
    private $uploadDir;
    private $rpcUrl;

    public function __construct() {
        $this->torrentDir = TORRENT_DIR;
        $this->uploadDir = UPLOAD_DIR;
        $this->rpcUrl = RPC_URL;
    }

    public function getTorrentDir() {
        return $this->torrentDir;
    }

    public function getUploadDir() {
        return $this->uploadDir;
    }

    public function addTorrent($torrentFile) {
        $destination = $this->torrentDir . basename($torrentFile);
        if (move_uploaded_file($torrentFile, $destination)) {
            $db = new MySQL();
            $db->query("INSERT INTO torrents (name, size) VALUES ('" . basename($torrentFile) . "', " . filesize($destination) . ")");
            $db->close();
            return true;
        }
        return false;
    }

    public function startDownload($torrentFile) {
        $filePath = $this->torrentDir . basename($torrentFile);
        if (file_exists($filePath)) {
            $ch = curl_init($this->rpcUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'method' => 'torrent-add',
                'arguments' => [
                    'filename' => $filePath,
                    'download-dir' => $this->uploadDir
                ]
            ]));
            $response = curl_exec($ch);
            curl_close($ch);
            $responseData = json_decode($response, true);
            if (isset($responseData['result']) && $responseData['result'] === 'success') {
                $db = new MySQL();
                $db->query("INSERT INTO downloads (torrent_id, status) VALUES ((SELECT id FROM torrents WHERE name = '" . basename($torrentFile) . "'), 'downloading')");
                $db->close();
                return true;
            }
            return false;
        }
        return false;

    }

    public function checkDownloadStatus($torrentId) {
        $ch = curl_init($this->rpcUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'method' => 'torrent-get',
            'arguments' => [
                'fields' => ['id', 'status', 'name'],
                'ids' => [$torrentId]
            ]
        ]));
        $response = curl_exec($ch);
        curl_close($ch);
        $responseData = json_decode($response, true);
        return $responseData['arguments']['torrents'] ?? [];
    }

    public function listTorrentsWithStatus() {
        $db = new MySQL();
        $result = $db->query("SELECT t.id, t.name, t.size, d.status FROM torrents t LEFT JOIN downloads d ON t.id = d.torrent_id");
        $torrents = [];
        while ($row = $result->fetch_assoc()) {
            $torrents[] = $row;
        }
        $db->close();
        return $torrents;
    }
}
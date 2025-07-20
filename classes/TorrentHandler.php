<?php
require_once 'MySQL.php';


class TorrentHandler {
    private $torrentDir;
    private $uploadDir;
    private $rpcUrl;
    private $db;
    private $rpcUser;
    private $rpcPass;

    public function __construct() {
        $env = parse_ini_file(__DIR__ . '/../.env');
        $this->torrentDir = TORRENT_DIR;
        $this->uploadDir = UPLOAD_DIR;
        $this->rpcUrl = RPC_URL;
        $this->db = new MySQL();
        $this->rpcUser = isset($env['RPC_USERNAME']) ? $env['RPC_USERNAME'] : 'transmission';
        $this->rpcPass = isset($env['RPC_PASS']) ? $env['RPC_PASS'] : 'your_rpc_password';
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
        $filePath = $this->torrentDir . $torrentFile;
        $auth = $this->getTransmissionAuth();
        $payload = json_encode([
                'method' => 'torrent-add',
                'arguments' => [
                    'metainfo' => base64_encode(file_get_contents($filePath)),
                    'download-dir' => $this->uploadDir,
                    'paused' => false
                ]
            ]);
        if (file_exists($filePath)) {
            $ch = curl_init($this->rpcUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded',
                'X-Transmission-Session-Id: ' . $this->getSessionId(),
            ]);
            curl_setopt($ch, CURLOPT_USERPWD, $auth);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            $response = curl_exec($ch);

            curl_close($ch);
            $responseData = json_decode($response, true);
            if (isset($responseData['result']) && $responseData['result'] === 'success') {
                $db = new MySQL();
                $db->query("INSERT INTO downloads (torrent_id, status) VALUES ((SELECT id FROM torrents WHERE name = '" . basename($torrentFile) . "'), 'downloading')");
                $db->close();
                return true;
            } else {
                echo "Failed to start download: " . (isset($responseData['result']) ? $responseData['result'] : 'Unknown error');
            }
            return false;
        }
        return false;

    }

    public function checkDownloadStatus($torrentId) {
        $auth = $this->getTransmissionAuth();
        $sessionId = $this->getSessionId();

        $payload = json_encode([
            'method' => 'torrent-get',
            'arguments' => [
                'fields' => ['id', 'status', 'name'],
                'ids' => [$torrentId]
            ]
        ]);

        $ch = curl_init($this->rpcUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $auth,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Transmission-Session-Id: ' . $sessionId,
            ],
            CURLOPT_POSTFIELDS => $payload,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $responseData = json_decode($response, true);
        return $responseData['arguments']['torrents'] ?? [];
    }


    public function listAllTorrentsFromTransmission() {
        $auth = $this->getTransmissionAuth();
        $sessionId = $this->getSessionId();

        $payload = json_encode([
            'method' => 'torrent-get',
            'arguments' => [
                'fields' => ['id', 'name', 'status', 'percentDone', 'sizeWhenDone']
            ]
        ]);

        $ch = curl_init($this->rpcUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $auth,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Transmission-Session-Id: ' . $sessionId,
            ],
            CURLOPT_POSTFIELDS => $payload,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $responseData = json_decode($response, true);
        return $responseData['arguments']['torrents'] ?? [];
    }


    public function deleteTorrent($torrentId, $removeData = false) {
        $db = new MySQL();
        $db->query("DELETE FROM torrents WHERE id = $torrentId");
        $db->query("DELETE FROM downloads WHERE torrent_id = $torrentId");
        $db->close();

        // Remove from Transmission daemon too
        $auth = $this->getTransmissionAuth();
        $sessionId = $this->getSessionId();

        $payload = json_encode([
            'method' => 'torrent-remove',
            'arguments' => [
                'ids' => [$torrentId],
                'delete-local-data' => $removeData
            ]
        ]);

        $ch = curl_init($this->rpcUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $auth,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Transmission-Session-Id: ' . $sessionId,
            ],
            CURLOPT_POSTFIELDS => $payload,
        ]);

        curl_exec($ch);
        curl_close($ch);

        // Optionally remove the .torrent file
        $torrentFile = $this->torrentDir . $torrentId . '.torrent';
        if (file_exists($torrentFile)) {
            unlink($torrentFile);
        }
    }
    public function listTorrentFiles() {
        $uploadedTorrents = [];
        $this->db->query("SELECT * FROM torrents");
        $result = $this->db->query("SELECT * FROM torrents");
        while ($row = $result->fetch_assoc()) {
            $uploadedTorrents[] = $row;
        }
        return $uploadedTorrents;
    }

    private function getTransmissionAuth() {
        $auth = "{$this->rpcUser}:{$this->rpcPass}";
        return $auth;
    }

    private function getSessionId(): string
    {
        $ch = curl_init($this->rpcUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => "{$this->rpcUser}:{$this->rpcPass}",
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => false, // changed!
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['method' => 'session-get']),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);
        $response = curl_exec($ch);
        if ($response === false) {
            throw new RuntimeException('cURL error: ' . curl_error($ch));
        }

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        curl_close($ch);

        preg_match('/X-Transmission-Session-Id: ([^\r\n]+)/', $headers, $matches);
        if (empty($matches[1])) {
            throw new RuntimeException("Failed to get session ID from Transmission.");
        }

        return trim($matches[1]);
    }
}
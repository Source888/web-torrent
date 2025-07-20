<?php


class MySQL {
    private $connection;

    public function __construct() {
        
        if (file_exists(__DIR__ . '/../.env')) {
            $env = parse_ini_file(__DIR__ . '/../.env');
            $host = $env['DB_HOST'];
            $username = $env['DB_USER'];
            $password = $env['DB_PASSWORD'];
            $database = $env['DB_NAME'];
        }
        $this->connection = new mysqli($host, $username, $password, $database);
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }

    public function query($sql) {
        return $this->connection->query($sql);
    }

    public function close() {
        $this->connection->close();
    }

    public function createDatabaseTables() {
        $this->query("CREATE TABLE IF NOT EXISTS torrents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            size BIGINT NOT NULL,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $this->query("CREATE TABLE IF NOT EXISTS downloads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            torrent_id INT NOT NULL,
            status ENUM('pending', 'downloading', 'completed') DEFAULT 'pending',
            started_at TIMESTAMP NULL,
            completed_at TIMESTAMP NULL,
            FOREIGN KEY (torrent_id) REFERENCES torrents(id)
        )");        
    }
}
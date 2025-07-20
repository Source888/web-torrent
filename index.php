<?php
$rootDir = __DIR__;
define('TORRENT_DIR', $rootDir.'/torrents/');
define('UPLOAD_DIR', $rootDir.'/downloads/');
define('RPC_URL', 'http://localhost:9091/transmission/rpc');

require_once 'classes/MySQL.php';
require_once 'classes/TorrentHandler.php';
require_once 'classes/DownloadsHandler.php';
require_once 'classes/ViewController.php';

$router = new RouteController();
$router->handleRequest();

?>
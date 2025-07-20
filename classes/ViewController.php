<?php

require_once 'classes/RouteController.php';

class ViewController {
    private $jsPaths = [
        'js/app.js'
    ];
    private $cssPaths = [
        'css/style.css'
    ];
    private $data = [];
    public function __construct() {
        $this->data['jsPaths'] = $this->prepareJSPathsWithLastModified();
        $this->data['cssPaths'] = $this->prepareCSSPathsWithLastModified();
    }
    public function showIndexPage($torrentDir, $uploadDir, $torrentFiles) {
        $data = $this->data;

        $data['torrentDir'] = $torrentDir;
        $data['uploadDir'] = $uploadDir;
        $data['torrentFiles'] = $torrentFiles;
        $data['page'] = 'index';
        ob_start();
        require_once 'views/main.php';
        $output = ob_get_clean();
        echo $output;
    }

    public function showFilesPage($torrentFiles) {
        $data = $this->data;
        $data['torrentFiles'] = $torrentFiles;
        $data['page'] = 'files';
        ob_start();
        require_once 'views/main.php';
        $output = ob_get_clean();
        echo $output;
    }

    public function showTorrentsPage($torrents){
        $data = $this->data;
        $data['torrents'] = $torrents;
        $data['page'] = 'torrents';
        ob_start();
        require_once 'views/main.php';
        $output = ob_get_clean();
        echo $output;
    }


    public function showUploadForm($title) {
        $data = $this->data;
        $data['title'] = $title;
        $data['page'] = 'upload';
        ob_start();
        require_once 'views/main.php';
        $output = ob_get_clean();
        echo $output;
    }

    private function prepareJSPathsWithLastModified() {
        $versionedPaths = [];
        foreach ($this->jsPaths as $path) {
            $lastModified = filemtime($path);
            $versionedPaths[] = $path . '?v=' . $lastModified;
        }
        return $versionedPaths;
    }
    private function prepareCSSPathsWithLastModified() {
        $versionedPaths = [];
        foreach ($this->cssPaths as $path) {
            $lastModified = filemtime($path);
            $versionedPaths[] = $path . '?v=' . $lastModified;
        }
        return $versionedPaths;
    }

}
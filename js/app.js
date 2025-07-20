const sendDownloadStart = (torrentId) => {
    const data = {
        action: 'start_download',
        torrent_file: torrentId
    };
    fetch(`index.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Download started successfully');
        } else {
            console.error('Error starting download:', data.message);
        }
    })
    .catch(error => console.error('Fetch error:', error));
}


document.addEventListener('DOMContentLoaded', function() {
    const torrentsList = document.getElementById('torrentList');
    if (torrentsList) {
        const torrents = document.querySelectorAll('#torrentList li');
        if( torrents.length > 0) {
            torrents.forEach(torrent => {
                const startButton = document.createElement('button');
                startButton.textContent = 'Start Download';
                startButton.addEventListener('click', function() {
                    const torrentId = torrent.getAttribute('data-file-name');
                    sendDownloadStart(torrentId);
                });
                torrent.appendChild(startButton);
            });
        }
    }
})
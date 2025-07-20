<?php
?>
<div class="container">
    <h1>Upload Torrent</h1>
    <form action="index.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="torrentFile">Select Torrent File:</label>
            <input type="file" name="torrentFile" id="torrentFile" required>
        </div>
        <input type="hidden" name="action" value="upload_torrent">
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
    <p><a href="?page=index">Back to Home</a></p>
</div>

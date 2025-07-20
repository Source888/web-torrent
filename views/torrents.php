<?php
$statusLabels = [
    0 => 'Stopped',
    1 => 'Check Wait',
    2 => 'Checking',
    3 => 'Download Wait',
    4 => 'Downloading',
    5 => 'Seed Wait',
    6 => 'Seeding',
];
$data['title'] = 'Torrents';
$data['description'] = 'Manage your torrents here.';

?>

<div class="torrent-list">
    <h2>Torrents</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Size</th>
                <th>Status</th>
                <th>Percent Complete</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['torrents'] as $torrent): ?>
                <tr>
                    <td><?php echo htmlspecialchars($torrent['name']); ?></td>
                    <td><?php echo htmlspecialchars($torrent['sizeWhenDone']); ?></td>
                    <td><?php echo htmlspecialchars($statusLabels[$torrent['status']]); ?></td>
                    <td><?php echo htmlspecialchars($torrent['percentDone'] * 100); ?>%</td>
                    <td><a href="download.php?id=<?php echo $torrent['id']; ?>">Download</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="?page=upload">Upload a new torrent</a></p>
</div>
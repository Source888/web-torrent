<?php
$page = isset($data['page']) ? $data['page'] : 'main';
require_once 'views/header.php';
?>
<div class="main-content">
    <?php
    require_once 'views/' . $page . '.php';
    ?>
</div>
<?php
require_once 'views/footer.php';
?>
</body>
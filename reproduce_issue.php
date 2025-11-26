<?php
require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Issue</title>
</head>
<body>
    <h1>Test Page</h1>
    <div class="test-badges">
        Status: <?= getStatusBadge('pending') ?><br>
        Priority: <?= getPriorityBadge('urgent') ?>
    </div>
</body>
</html>

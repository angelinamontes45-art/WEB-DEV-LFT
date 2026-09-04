<?php
require_once __DIR__ . '/../Includes/db.php';
$user = requireRole(['admin']);
?>
<header class="admin-local-header"><strong>LFT Admin</strong><span><?= e($user['name']) ?></span></header>

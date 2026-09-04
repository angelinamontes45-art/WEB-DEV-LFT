<?php
require '../_guard.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') db()->prepare('DELETE FROM messages WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
adminRedirect('messages', 'message=' . urlencode('Message deleted.'));
<?php
require '../_guard.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') { $statement = db()->prepare('DELETE FROM memberships WHERE id = ?'); $statement->execute([(int) ($_POST['id'] ?? 0)]); }
adminRedirect('memberships', 'message=' . urlencode('Membership plan deleted.'));

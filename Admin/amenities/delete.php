<?php
require '../_guard.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') { $statement = db()->prepare('DELETE FROM amenities WHERE id = ?'); $statement->execute([(int) ($_POST['id'] ?? 0)]); }
adminRedirect('amenities', 'message=' . urlencode('Amenity deleted.'));

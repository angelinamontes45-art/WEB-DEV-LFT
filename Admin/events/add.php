<?php
require '../_guard.php';
$errors = [];
$values = ['image' => 'conference.png', 'title' => '', 'description' => '', 'event_date' => '', 'event_time' => '', 'location' => 'LFT Dumaguete', 'status' => 'Active', 'category' => 'Community Event'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	foreach (array_keys($values) as $field) $values[$field] = trim($_POST[$field] ?? $values[$field]);
	$values['status'] = $values['status'] === 'Inactive' ? 'Inactive' : 'Active';
	foreach (['image', 'title', 'description', 'event_date', 'event_time', 'location'] as $field) if ($values[$field] === '') $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
	if (!$errors) { $statement = db()->prepare('INSERT INTO events (event_date, event_time, category, title, description, image, location, status, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'); $statement->execute([$values['event_date'], $values['event_time'], $values['category'], $values['title'], $values['description'], $values['image'], $values['location'], $values['status'], $values['status'] === 'Active' ? 1 : 0]); adminRedirect('events', 'message=' . urlencode('Event added successfully.')); }
}
$pageTitle = 'Add event | LFT Admin';
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= e($pageTitle) ?></title><link rel="stylesheet" href="../../assets/css/style.css"></head><body><main class="admin-app"><?php require '../sidebar.php'; ?><div class="admin-main"><header class="admin-topbar"><div><p class="section-label">EVENT CALENDAR</p><h1>Add event</h1><p>Create an event for the public calendar.</p></div><a class="text-link" href="index.php">Back to events</a><?php require "../profile.php"; ?></header><section class="admin-widget booking-detail"><?php foreach ($errors as $error): ?><div class="form-error"><?= e($error) ?></div><?php endforeach; ?><form class="admin-editor" method="post"><label>Event image<input name="image" value="<?= e($values['image']) ?>" placeholder="conference.png" required></label><label>Event title<input name="title" value="<?= e($values['title']) ?>" required></label><label>Description<textarea name="description" rows="4" required><?= e($values['description']) ?></textarea></label><label>Date<input name="event_date" type="date" value="<?= e($values['event_date']) ?>" required></label><label>Time<input name="event_time" type="time" value="<?= e($values['event_time']) ?>" required></label><label>Location<input name="location" value="<?= e($values['location']) ?>" required></label><label>Category<input name="category" value="<?= e($values['category']) ?>" required></label><label>Status<select name="status"><option>Active</option><option <?= $values['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option></select></label><button class="btn btn-green" type="submit">ADD EVENT</button></form></section></div></main></body></html>
<?php
require '../_guard.php';
adminRedirect('events');

<?php
require '../_guard.php';
$spaceId = (int) ($_GET['id'] ?? 0);
$statement = db()->prepare('SELECT * FROM spaces WHERE id = ?');
$statement->execute([$spaceId]);
$space = $statement->fetch();
if (!$space) adminRedirect('spaces', 'message=' . urlencode('Space not found.'));
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim($_POST['name'] ?? '');
	$category = trim($_POST['category'] ?? '');
	$description = trim($_POST['description'] ?? '');
	$rates = trim($_POST['rates'] ?? '');
	$seats = (int) ($_POST['seats'] ?? 0);
	$image = trim($_POST['image'] ?? '') ?: 'common.png';
	$active = isset($_POST['active']) ? 1 : 0;
	foreach (['name' => $name, 'category' => $category, 'description' => $description, 'rates' => $rates] as $field => $value) if ($value === '') $errors[] = ucfirst($field) . ' is required.';
	if ($seats < 1) $errors[] = 'Seats must be at least 1.';
	if (!$errors) {
		$update = db()->prepare('UPDATE spaces SET name = ?, category = ?, description = ?, rates = ?, seats = ?, image = ?, active = ? WHERE id = ?');
		$update->execute([$name, $category, $description, $rates, $seats, $image, $active, $spaceId]);
		adminRedirect('spaces', 'message=' . urlencode('Space updated successfully.'));
	}
	$space = array_merge($space, compact('name', 'category', 'description', 'rates', 'seats', 'image', 'active'));
}
$pageTitle = 'Edit space | LFT Admin';
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= e($pageTitle) ?></title><link rel="stylesheet" href="../../assets/css/style.css"></head><body><main class="admin-app"><?php require "../sidebar.php"; ?><div class="admin-main"><header class="admin-topbar"><div><p class="section-label">SPACE CATALOG</p><h1>Edit space</h1><p>Update <?= e($space['name']) ?>.</p></div><a class="text-link" href="index.php">Back to spaces</a><?php require "../profile.php"; ?></header><section class="admin-widget booking-detail"><?php foreach ($errors as $error): ?><div class="form-error"><?= e($error) ?></div><?php endforeach; ?><form class="admin-editor" method="post"><label>Name<input name="name" value="<?= e($space['name']) ?>" required></label><label>Category<input name="category" value="<?= e($space['category']) ?>" required></label><label>Description<textarea name="description" rows="4" required><?= e($space['description']) ?></textarea></label><label>Seats<input name="seats" type="number" min="1" value="<?= (int) $space['seats'] ?>" required></label><label>Rates<textarea name="rates" rows="4" required><?= e($space['rates']) ?></textarea></label><label>Image filename<input name="image" value="<?= e($space['image']) ?>"></label><label class="admin-checkbox"><input type="checkbox" name="active" <?= (int) $space['active'] === 1 ? 'checked' : '' ?>> Publish this space</label><button class="btn btn-green" type="submit">SAVE CHANGES</button></form></section></div></main></body></html>

<?php
require '../_guard.php';
$errors = [];
$values = ['name' => '', 'category' => '', 'description' => '', 'rates' => '', 'seats' => '1', 'image' => 'common.png', 'active' => '1'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	foreach (array_keys($values) as $field) $values[$field] = trim($_POST[$field] ?? $values[$field]);
	$values['active'] = isset($_POST['active']) ? '1' : '0';
	foreach (['name', 'category', 'description', 'rates'] as $field) if ($values[$field] === '') $errors[] = ucfirst($field) . ' is required.';
	if ((int) $values['seats'] < 1) $errors[] = 'Seats must be at least 1.';
	if (!$errors) {
		$statement = db()->prepare('INSERT INTO spaces (name, category, description, rates, seats, image, active) VALUES (?, ?, ?, ?, ?, ?, ?)');
		$statement->execute([$values['name'], $values['category'], $values['description'], $values['rates'], (int) $values['seats'], $values['image'] ?: 'common.png', $values['active']]);
		adminRedirect('spaces', 'message=' . urlencode('Space added successfully.'));
	}
}
$pageTitle = 'Add space | LFT Admin';
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= e($pageTitle) ?></title><link rel="stylesheet" href="../../assets/css/style.css"></head><body><main class="admin-app"><?php require "../sidebar.php"; ?><div class="admin-main"><header class="admin-topbar"><div><p class="section-label">SPACE CATALOG</p><h1>Add a space</h1><p>Create a new listing for the public catalog.</p></div><a class="text-link" href="index.php">Back to spaces</a><?php require "../profile.php"; ?></header><section class="admin-widget booking-detail"><?php foreach ($errors as $error): ?><div class="form-error"><?= e($error) ?></div><?php endforeach; ?><form class="admin-editor" method="post"><label>Name<input name="name" value="<?= e($values['name']) ?>" required></label><label>Category<input name="category" value="<?= e($values['category']) ?>" required></label><label>Description<textarea name="description" rows="4" required><?= e($values['description']) ?></textarea></label><label>Seats<input name="seats" type="number" min="1" value="<?= e($values['seats']) ?>" required></label><label>Rates<textarea name="rates" rows="4" placeholder="₱199 / hour" required><?= e($values['rates']) ?></textarea></label><label>Image filename<input name="image" value="<?= e($values['image']) ?>" placeholder="common.png"></label><label class="admin-checkbox"><input type="checkbox" name="active" <?= $values['active'] === '1' ? 'checked' : '' ?>> Publish this space</label><button class="btn btn-green" type="submit">ADD SPACE</button></form></section></div></main></body></html>

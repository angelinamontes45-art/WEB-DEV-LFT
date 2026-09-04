<?php
require '../_guard.php';
$membershipId = (int) ($_GET['id'] ?? 0);
$statement = db()->prepare('SELECT * FROM memberships WHERE id = ?');
$statement->execute([$membershipId]);
$membership = $statement->fetch();
if (!$membership) adminRedirect('memberships', 'message=' . urlencode('Membership not found.'));
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? ''); $description = trim($_POST['description'] ?? ''); $price = trim($_POST['price'] ?? ''); $period = trim($_POST['period'] ?? ''); $features = trim($_POST['features'] ?? ''); $status = ($_POST['status'] ?? '') === 'Inactive' ? 'Inactive' : 'Active';
    foreach (['name' => $name, 'description' => $description, 'price' => $price, 'period' => $period, 'features' => $features] as $field => $value) if ($value === '') $errors[] = ucfirst($field) . ' is required.';
    if (!$errors) { $update = db()->prepare('UPDATE memberships SET name = ?, price = ?, period = ?, description = ?, features = ?, status = ?, active = ? WHERE id = ?'); $update->execute([$name, $price, $period, $description, $features, $status, $status === 'Active' ? 1 : 0, $membershipId]); adminRedirect('memberships', 'message=' . urlencode('Membership updated successfully.')); }
    $membership = array_merge($membership, compact('name', 'description', 'price', 'period', 'features', 'status'));
}
$pageTitle = 'Edit membership | LFT Admin';
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= e($pageTitle) ?></title><link rel="stylesheet" href="../../assets/css/style.css"></head><body><main class="admin-app"><?php require "../sidebar.php"; ?><div class="admin-main"><header class="admin-topbar"><div><p class="section-label">MEMBERSHIP CATALOG</p><h1>Edit membership</h1><p>Update <?= e($membership['name']) ?>.</p></div><a class="text-link" href="index.php">Back to memberships</a><?php require "../profile.php"; ?></header><section class="admin-widget booking-detail"><?php foreach ($errors as $error): ?><div class="form-error"><?= e($error) ?></div><?php endforeach; ?><form class="admin-editor" method="post"><label>Membership name<input name="name" value="<?= e($membership['name']) ?>" required></label><label>Price<input name="price" value="<?= e($membership['price']) ?>" required></label><label>Duration<input name="period" value="<?= e($membership['period']) ?>" required></label><label>Status<select name="status"><option <?= $membership['status'] === 'Active' ? 'selected' : '' ?>>Active</option><option <?= $membership['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option></select></label><label>Description<textarea name="description" rows="4" required><?= e($membership['description']) ?></textarea></label><label>Features<textarea name="features" rows="5" required><?= e($membership['features']) ?></textarea></label><button class="btn btn-green" type="submit">SAVE CHANGES</button></form></section></div></main></body></html>
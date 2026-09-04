<?php
require '../_guard.php';

$connection = db();
$notice = $_GET['message'] ?? '';
$errors = [];
$settings = $connection->query('SELECT * FROM site_settings WHERE id = 1')->fetch();

if (!$settings) {
    $connection->exec('INSERT OR IGNORE INTO site_settings (id) VALUES (1)');
    $settings = $connection->query('SELECT * FROM site_settings WHERE id = 1')->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'profile') {
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid name and email address.';
        } else {
            try {
                $update = $connection->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
                $update->execute([$name, $email, $user['id']]);
                header('Location: index.php?message=' . urlencode('Admin profile updated.'));
                exit;
            } catch (PDOException $exception) {
                $errors[] = 'That email address is already in use.';
            }
        }
    }

    if ($action === 'website') {
        $values = [
            'website_name' => trim($_POST['website_name'] ?? ''),
            'email' => strtolower(trim($_POST['email'] ?? '')),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'facebook' => trim($_POST['facebook'] ?? ''),
            'instagram' => trim($_POST['instagram'] ?? ''),
        ];

        if ($values['website_name'] === '' || $values['phone'] === '' || $values['address'] === '') {
            $errors[] = 'Website name, phone, and address are required.';
        }
        if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid public email address.';
        }
        foreach (['facebook', 'instagram'] as $field) {
            if ($values[$field] !== '' && !filter_var($values[$field], FILTER_VALIDATE_URL)) {
                $errors[] = ucfirst($field) . ' must be a complete valid URL.';
            }
        }

        if (!$errors) {
            $update = $connection->prepare('UPDATE site_settings SET website_name = ?, email = ?, phone = ?, address = ?, facebook = ?, instagram = ?, updated_at = CURRENT_TIMESTAMP WHERE id = 1');
            $update->execute(array_values($values));
            header('Location: index.php?message=' . urlencode('Website information updated.'));
            exit;
        }

        $settings = array_merge($settings, $values);
    }

    if ($action === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $account = $connection->prepare('SELECT password_hash FROM users WHERE id = ?');
        $account->execute([$user['id']]);
        $hash = (string) $account->fetchColumn();

        if (!password_verify($current, $hash)) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($new) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        } elseif ($new !== $confirm) {
            $errors[] = 'New password and confirmation do not match.';
        } else {
            $update = $connection->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $update->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
            header('Location: index.php?message=' . urlencode('Password changed successfully.'));
            exit;
        }
    }
}

$pageTitle = 'Settings | LFT Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<main class="admin-app">
    <?php require '../sidebar.php'; ?>
    <div class="admin-main">
        <header class="admin-topbar">
            <div><p class="section-label">LFT DUMAGUETE</p><h1>Settings</h1><p>Manage your administrator profile and the contact information shown on the public website.</p></div>
            <?php require '../profile.php'; ?>
        </header>

        <?php foreach ($errors as $error): ?><div class="form-error"><?= e($error) ?></div><?php endforeach; ?>
        <?php if ($notice): ?><div class="success-message"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> <?= e($notice) ?></div><?php endif; ?>

        <section class="settings-grid">
            <article class="admin-widget settings-card">
                <h2>Admin profile</h2>
                <p>Used for your administrator account.</p>
                <form class="admin-editor" method="post">
                    <input type="hidden" name="action" value="profile">
                    <label>Name<input name="name" value="<?= e($user['name']) ?>" required></label>
                    <label>Email<input name="email" type="email" value="<?= e($user['email']) ?>" required></label>
                    <button class="btn btn-green" type="submit">SAVE PROFILE</button>
                </form>
            </article>

            <article class="admin-widget settings-card">
                <h2>Public website information</h2>
                <p>These details feed the public Contact page and footer.</p>
                <form class="admin-editor" method="post">
                    <input type="hidden" name="action" value="website">
                    <label>Website name<input name="website_name" value="<?= e($settings['website_name']) ?>" required></label>
                    <label>Public email<input name="email" type="email" value="<?= e($settings['email']) ?>" required></label>
                    <label>Phone<input name="phone" value="<?= e($settings['phone']) ?>" required></label>
                    <label>Address<textarea name="address" rows="3" required><?= e($settings['address']) ?></textarea></label>
                    <label>Facebook link<input name="facebook" type="url" value="<?= e($settings['facebook']) ?>" placeholder="https://facebook.com/..."></label>
                    <label>Instagram link<input name="instagram" type="url" value="<?= e($settings['instagram']) ?>" placeholder="https://instagram.com/..."></label>
                    <button class="btn btn-green" type="submit">SAVE WEBSITE INFO</button>
                </form>
            </article>

            <article class="admin-widget settings-card">
                <h2>Change password</h2>
                <p>Use at least 8 characters and keep this account private.</p>
                <form class="admin-editor" method="post">
                    <input type="hidden" name="action" value="password">
                    <label>Current password<input name="current_password" type="password" autocomplete="current-password" required></label>
                    <label>New password<input name="new_password" type="password" minlength="8" autocomplete="new-password" required></label>
                    <label>Confirm new password<input name="confirm_password" type="password" minlength="8" autocomplete="new-password" required></label>
                    <button class="btn btn-green" type="submit">CHANGE PASSWORD</button>
                </form>
            </article>
        </section>
    </div>
</main>
</body>
</html>

<?php
require '../Includes/db.php';
$user = requireRole(['customer']);
$connection = db();
$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'profile') {
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        if (mb_strlen($name) < 2) $errors[] = 'Enter your full name.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
        if (!$errors) {
            try {
                $statement = $connection->prepare('UPDATE users SET name = ?, email = ?, phone = ?, updated_at = ? WHERE id = ?');
                $statement->execute([$name, $email, $phone, appNow()->format('Y-m-d H:i:s'), $user['id']]);
                $message = 'Account details updated.';
                $user = currentUser();
            } catch (PDOException $exception) {
                $errors[] = 'That email is already in use.';
            }
        }
    } elseif ($action === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $statement = $connection->prepare('SELECT password_hash FROM users WHERE id = ?');
        $statement->execute([$user['id']]);
        $hash = (string)$statement->fetchColumn();
        if (!password_verify($current, $hash)) $errors[] = 'Current password is incorrect.';
        if (strlen($new) < 8) $errors[] = 'New password must be at least 8 characters.';
        if ($new !== $confirm) $errors[] = 'New passwords do not match.';
        if (!$errors) {
            $update = $connection->prepare('UPDATE users SET password_hash = ?, updated_at = ? WHERE id = ?');
            $update->execute([password_hash($new, PASSWORD_DEFAULT), appNow()->format('Y-m-d H:i:s'), $user['id']]);
            session_regenerate_id(true);
            $message = 'Password updated successfully.';
        }
    }
}

$pageTitle = 'My Account | LFT Dumaguete';
$currentPage = 'DASHBOARD';
require '../Includes/header.php';
?>
<main><section class="dashboard-section"><div class="container">
    <div class="dashboard-heading"><div><p class="section-label">MY ACCOUNT</p><h1>Account settings</h1><p>Keep your contact and security details current.</p></div><a class="btn btn-outline1" href="index.php">BACK TO DASHBOARD</a></div>
    <?php if ($message): ?><div class="success-message"><i class="fa-solid fa-circle-check"></i> <?= e($message) ?></div><?php endif; ?>
    <?php foreach ($errors as $error): ?><div class="form-error"><?= e($error) ?></div><?php endforeach; ?>
    <div class="admin-grid">
        <section class="admin-widget"><div class="widget-heading"><div><h2>Profile details</h2><p>Used for your LFT bookings.</p></div></div>
            <form class="tour-form" method="post"><?= csrfField() ?><input type="hidden" name="action" value="profile">
                <label for="name">Full name</label><input id="name" name="name" value="<?= e($user['name']) ?>" maxlength="120" required>
                <label for="email">Email</label><input id="email" name="email" type="email" value="<?= e($user['email']) ?>" maxlength="190" required>
                <label for="phone">Phone</label><input id="phone" name="phone" value="<?= e($user['phone'] ?? '') ?>" maxlength="40" autocomplete="tel">
                <button class="btn btn-green" type="submit">SAVE PROFILE</button>
            </form>
        </section>
        <section class="admin-widget"><div class="widget-heading"><div><h2>Change password</h2><p>Use at least 8 characters.</p></div></div>
            <form class="tour-form" method="post"><?= csrfField() ?><input type="hidden" name="action" value="password">
                <label for="current_password">Current password</label><input id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                <label for="new_password">New password</label><input id="new_password" name="new_password" type="password" minlength="8" autocomplete="new-password" required>
                <label for="confirm_password">Confirm new password</label><input id="confirm_password" name="confirm_password" type="password" minlength="8" autocomplete="new-password" required>
                <button class="btn btn-green" type="submit">UPDATE PASSWORD</button>
            </form>
        </section>
    </div>
</div></section></main>
<?php require '../Includes/footer.php'; ?>

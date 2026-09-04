<?php
require '../_guard.php';
$connection = db();
$message = $_GET['message'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetUserId = (int) ($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($targetUserId > 0 && in_array($action, ['promote', 'remove'], true)) {
        if ($action === 'promote') {
            $statement = $connection->prepare("UPDATE users SET role = 'staff' WHERE id = ? AND role = 'customer'");
            $statement->execute([$targetUserId]);
            $text = $statement->rowCount() ? 'Staff access granted.' : 'User could not be promoted.';
        } else {
            $statement = $connection->prepare("UPDATE users SET role = 'customer' WHERE id = ? AND role = 'staff'");
            $statement->execute([$targetUserId]);
            $text = $statement->rowCount() ? 'Staff access removed.' : 'Staff account could not be updated.';
        }
        header('Location: index.php?message=' . urlencode($text));
        exit;
    }
}

$staff = $connection->query("SELECT id, name, email, created_at FROM users WHERE role = 'staff' ORDER BY name")->fetchAll();
$customers = $connection->query("SELECT id, name, email, created_at FROM users WHERE role = 'customer' ORDER BY name LIMIT 100")->fetchAll();
$pageTitle = 'Staff Accounts | LFT Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/portal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<main class="admin-app">
    <?php require '../sidebar.php'; ?>
    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <p class="section-label">ACCESS CONTROL</p>
                <h1>Staff accounts</h1>
                <p>Grant existing LFT accounts access to the staff operations desk.</p>
            </div>
            <?php require '../profile.php'; ?>
        </header>

        <?php if ($message): ?><div class="success-message"><i class="fa-solid fa-circle-check"></i><?= e($message) ?></div><?php endif; ?>

        <section class="admin-widget booking-panel">
            <div class="widget-heading"><div><h2>Current staff</h2><p><?= count($staff) ?> staff account<?= count($staff) === 1 ? '' : 's' ?></p></div></div>
            <?php if (!$staff): ?>
                <div class="empty-state">No staff accounts yet. Promote a registered customer below.</div>
            <?php else: ?>
                <?php foreach ($staff as $member): ?>
                    <article class="staff-row">
                        <div><h3><?= e($member['name']) ?></h3><small><?= e($member['email']) ?></small></div>
                        <div><strong>Staff</strong><span>Since <?= e(date('M Y', strtotime($member['created_at']))) ?></span></div>
                        <form method="post" onsubmit="return confirm('Remove staff access for this account?');">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="user_id" value="<?= (int) $member['id'] ?>">
                            <button class="danger-link" type="submit">Remove staff access</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <section class="admin-widget booking-panel" style="margin-top:24px;">
            <div class="widget-heading"><div><h2>Registered customers</h2><p>Promote an existing account to staff.</p></div></div>
            <?php if (!$customers): ?>
                <div class="empty-state">No customer accounts are available.</div>
            <?php else: ?>
                <?php foreach ($customers as $customer): ?>
                    <article class="staff-row">
                        <div><h3><?= e($customer['name']) ?></h3><small><?= e($customer['email']) ?></small></div>
                        <div><strong>Customer</strong><span>Joined <?= e(date('M Y', strtotime($customer['created_at']))) ?></span></div>
                        <form method="post" onsubmit="return confirm('Give this account staff access?');">
                            <input type="hidden" name="action" value="promote">
                            <input type="hidden" name="user_id" value="<?= (int) $customer['id'] ?>">
                            <button class="btn btn-green" type="submit">MAKE STAFF</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </div>
</main>
</body>
</html>

<?php
require '../_guard.php';

$messageId = (int) ($_GET['id'] ?? 0);
$statement = db()->prepare('SELECT * FROM messages WHERE id = ?');
$statement->execute([$messageId]);
$message = $statement->fetch();

if (!$message) {
    header('Location: index.php?message=' . urlencode('Message not found.'));
    exit;
}

if ($message['status'] !== 'Read') {
    $update = db()->prepare('UPDATE messages SET status = ? WHERE id = ?');
    $update->execute(['Read', $messageId]);
    $message['status'] = 'Read';
}

$pageTitle = 'View Message | LFT Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<main class="admin-app">
    <?php require '../sidebar.php'; ?>
    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <p class="section-label">LFT DUMAGUETE</p>
                <h1>Message details</h1>
                <p>Read and manage a visitor inquiry.</p>
            </div>
            <?php require '../profile.php'; ?>
        </header>

        <section class="admin-widget booking-panel">
            <div class="widget-heading">
                <div>
                    <p class="section-label"><?= e($message['status']) ?></p>
                    <h2><?= e($message['subject']) ?></h2>
                    <p>Received <?= e(date('M j, Y g:i A', strtotime($message['created_at']))) ?></p>
                </div>
                <a class="text-link" href="index.php">Back to messages</a>
            </div>

            <div class="booking-card">
                <p><strong>From:</strong> <?= e($message['sender']) ?></p>
                <p><strong>Email:</strong> <a href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a></p>
                <hr>
                <p style="white-space: pre-wrap;"><?= e($message['message']) ?></p>
            </div>

            <div class="dashboard-actions">
                <a class="btn btn-green" href="mailto:<?= e($message['email']) ?>?subject=Re%3A%20<?= rawurlencode($message['subject']) ?>">REPLY BY EMAIL</a>
                <form method="post" action="delete.php" onsubmit="return confirm('Delete this message?');">
                    <input type="hidden" name="id" value="<?= (int) $message['id'] ?>">
                    <button class="danger-link" type="submit">Delete message</button>
                </form>
            </div>
        </section>
    </div>
</main>
</body>
</html>

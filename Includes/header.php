<?php
require_once __DIR__ . '/db.php';
$basePath = ($currentPage ?? 'HOME') === 'HOME' ? '' : '../';
$loggedInUser = currentUser();
$customerFirstName = '';
$customerInitial = '';
$customerNotifications = [];
$customerNotificationCount = 0;

if ($loggedInUser && $loggedInUser['role'] === 'customer') {
    $nameParts = preg_split('/\s+/', trim($loggedInUser['name']));
    $customerFirstName = $nameParts[0] ?? $loggedInUser['name'];
    $customerInitial = strtoupper(substr($customerFirstName, 0, 1));

    $notificationStatement = db()->prepare(
        "SELECT id, booking_reference, booking_type, space, visit_date, visit_time, status
         FROM bookings
         WHERE user_id = ?
           AND status IN ('Pending', 'Confirmed', 'Checked in')
           AND visit_date >= ?
         ORDER BY visit_date ASC, visit_time ASC
         LIMIT 5"
    );
    $notificationStatement->execute([(int) $loggedInUser['id'], appToday()]);
    $customerNotifications = $notificationStatement->fetchAll();

    $notificationCountStatement = db()->prepare(
        "SELECT COUNT(*)
         FROM bookings
         WHERE user_id = ?
           AND status IN ('Pending', 'Confirmed', 'Checked in')
           AND visit_date >= ?"
    );
    $notificationCountStatement->execute([(int) $loggedInUser['id'], appToday()]);
    $customerNotificationCount = (int) $notificationCountStatement->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'LFT Dumaguete') ?></title>
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/portal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<header class="site-header <?= $loggedInUser && $loggedInUser['role'] === 'customer' ? 'customer-header' : '' ?>">
    <div class="container nav-container">
        <a href="<?= $basePath ?>index.php" class="logo">
            <img src="<?= $basePath ?>assets/images/Logo.png" alt="LFT Dumaguete">
        </a>

        <button class="menu-toggle" id="menuToggle" type="button" aria-label="Toggle navigation" aria-expanded="false">☰</button>

        <nav class="main-nav" id="mainNav">
            <a href="<?= $basePath ?>index.php" class="<?= $currentPage === 'HOME' ? 'active' : '' ?>">HOME</a>
            <a href="<?= $basePath ?>About/index.php" class="<?= $currentPage === 'ABOUT' ? 'active' : '' ?>">ABOUT</a>
            <a href="<?= $basePath ?>Spaces/index.php" class="<?= $currentPage === 'SPACES' ? 'active' : '' ?>">SPACES</a>
            <a href="<?= $basePath ?>Membership/index.php" class="<?= $currentPage === 'MEMBERSHIPS' ? 'active' : '' ?>">MEMBERSHIPS</a>
            <a href="<?= $basePath ?>Amenities/index.php" class="<?= $currentPage === 'AMENITIES' ? 'active' : '' ?>">AMENITIES</a>
            <a href="<?= $basePath ?>Events/index.php" class="<?= $currentPage === 'EVENTS' ? 'active' : '' ?>">EVENTS</a>
            <a href="<?= $basePath ?>Contact/index.php" class="<?= $currentPage === 'CONTACT' ? 'active' : '' ?>">CONTACT</a>

            <?php if ($loggedInUser): ?>
                <?php if ($loggedInUser['role'] === 'customer'): ?>
                    <div class="customer-header-actions">
                        <a href="<?= $basePath ?>Booking/index.php?type=walk-in" class="customer-checkin-button">
                            <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                            <span>CHECK IN</span>
                        </a>

                        <details class="customer-notification-menu">
                            <summary aria-label="Notifications">
                                <i class="fa-regular fa-bell" aria-hidden="true"></i>
                                <?php if ($customerNotificationCount > 0): ?>
                                    <span class="customer-notification-badge"><?= $customerNotificationCount > 9 ? '9+' : $customerNotificationCount ?></span>
                                <?php endif; ?>
                            </summary>
                            <div class="customer-notification-dropdown">
                                <div class="customer-dropdown-heading">
                                    <div>
                                        <strong>Notifications</strong>
                                        <small>Your upcoming booking activity</small>
                                    </div>
                                    <?php if ($customerNotificationCount > 0): ?><span><?= $customerNotificationCount ?></span><?php endif; ?>
                                </div>

                                <?php if (!$customerNotifications): ?>
                                    <div class="customer-notification-empty">
                                        <i class="fa-regular fa-bell-slash" aria-hidden="true"></i>
                                        <span>No new booking updates.</span>
                                    </div>
                                <?php else: ?>
                                    <div class="customer-notification-list">
                                        <?php foreach ($customerNotifications as $notification): ?>
                                            <a href="<?= $basePath ?>Dashboard/index.php#booking-<?= (int) $notification['id'] ?>">
                                                <span class="customer-notification-icon"><i class="fa-regular fa-calendar" aria-hidden="true"></i></span>
                                                <span class="customer-notification-copy">
                                                    <strong><?= e($notification['space']) ?></strong>
                                                    <small><?= e($notification['status']) ?> · <?= e(date('M j', strtotime($notification['visit_date']))) ?> at <?= e(date('g:i A', strtotime($notification['visit_time']))) ?></small>
                                                    <?php if (!empty($notification['booking_reference'])): ?><em><?= e($notification['booking_reference']) ?></em><?php endif; ?>
                                                </span>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <a class="customer-notification-view-all" href="<?= $basePath ?>Dashboard/index.php">View all bookings</a>
                            </div>
                        </details>

                        <details class="customer-account-menu">
                            <summary>
                                <span class="customer-avatar" aria-hidden="true"><?= e($customerInitial) ?></span>
                                <span class="customer-account-copy">
                                    <small>HI,</small>
                                    <strong><?= e($customerFirstName) ?></strong>
                                </span>
                                <i class="fa-solid fa-chevron-down account-chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="customer-account-dropdown">
                                <div class="customer-account-card">
                                    <span class="customer-avatar customer-avatar-large" aria-hidden="true"><?= e($customerInitial) ?></span>
                                    <div>
                                        <strong><?= e($loggedInUser['name']) ?></strong>
                                        <small><?= e($loggedInUser['email']) ?></small>
                                    </div>
                                </div>
                                <a href="<?= $basePath ?>Dashboard/account.php">
                                    <i class="fa-regular fa-user" aria-hidden="true"></i>
                                    <span>My Profile</span>
                                </a>
                                <a href="<?= $basePath ?>Dashboard/index.php">
                                    <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                                    <span>My Bookings</span>
                                </a>
                                <a href="<?= $basePath ?>Membership/index.php">
                                    <i class="fa-regular fa-credit-card" aria-hidden="true"></i>
                                    <span>My Membership</span>
                                </a>
                                <a href="<?= $basePath ?>Dashboard/index.php">
                                    <i class="fa-regular fa-bell" aria-hidden="true"></i>
                                    <span>Notifications</span>
                                    <?php if ($customerNotificationCount > 0): ?><span class="customer-menu-count"><?= $customerNotificationCount > 9 ? '9+' : $customerNotificationCount ?></span><?php endif; ?>
                                </a>
                                <a href="<?= $basePath ?>Dashboard/account.php">
                                    <i class="fa-solid fa-gear" aria-hidden="true"></i>
                                    <span>Settings</span>
                                </a>
                                <a href="<?= $basePath ?>Logout/index.php" class="customer-logout-link">
                                    <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </details>
                    </div>
                <?php elseif ($loggedInUser['role'] === 'staff'): ?>
                    <a href="<?= $basePath ?>Staff/index.php" class="account-link">
                        <i class="fa-solid fa-headset" aria-hidden="true"></i>
                        STAFF DESK
                    </a>
                    <a href="<?= $basePath ?>Contact/index.php#tour" class="nav-button">BOOK A TOUR</a>
                <?php elseif ($loggedInUser['role'] === 'admin'): ?>
                    <a href="<?= $basePath ?>Admin/index.php" class="account-link">
                        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                        ADMIN
                    </a>
                    <a href="<?= $basePath ?>Contact/index.php#tour" class="nav-button">BOOK A TOUR</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?= $basePath ?>Login/index.php" class="account-link <?= $currentPage === 'LOGIN' ? 'active' : '' ?>">
                    <i class="fa-regular fa-user" aria-hidden="true"></i>
                    LOG IN
                </a>
                <a href="<?= $basePath ?>Contact/index.php#tour" class="nav-button">BOOK A TOUR</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

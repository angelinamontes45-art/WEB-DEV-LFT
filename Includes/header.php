<?php
require_once __DIR__ . '/db.php';
$basePath = ($currentPage ?? 'HOME') === 'HOME' ? '' : '../';
$loggedInUser = currentUser();
$customerFirstName = '';
$customerInitial = '';
if ($loggedInUser && $loggedInUser['role'] === 'customer') {
    $nameParts = preg_split('/\s+/', trim($loggedInUser['name']));
    $customerFirstName = $nameParts[0] ?? $loggedInUser['name'];
    $customerInitial = strtoupper(substr($customerFirstName, 0, 1));
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
                        <a href="<?= $basePath ?>Booking/index.php" class="nav-button customer-book-button">
                            <i class="fa-regular fa-calendar-plus" aria-hidden="true"></i>
                            <span>BOOK A SPACE</span>
                        </a>

                        <details class="customer-account-menu">
                            <summary>
                                <span class="customer-avatar" aria-hidden="true"><?= e($customerInitial) ?></span>
                                <span class="customer-account-copy">
                                    <small>WELCOME BACK</small>
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
                                <a href="<?= $basePath ?>Dashboard/index.php">
                                    <i class="fa-solid fa-gauge-high" aria-hidden="true"></i>
                                    <span>My Dashboard</span>
                                </a>
                                <a href="<?= $basePath ?>Booking/index.php">
                                    <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                                    <span>Book a Space</span>
                                </a>
                                <a href="<?= $basePath ?>Booking/index.php?type=walk-in">
                                    <i class="fa-solid fa-person-walking-arrow-right" aria-hidden="true"></i>
                                    <span>Walk-in Check-in</span>
                                </a>
                                <a href="<?= $basePath ?>Spaces/index.php">
                                    <i class="fa-solid fa-couch" aria-hidden="true"></i>
                                    <span>Explore Spaces</span>
                                </a>
                                <a href="<?= $basePath ?>Logout/index.php" class="customer-logout-link">
                                    <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                                    <span>Log Out</span>
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

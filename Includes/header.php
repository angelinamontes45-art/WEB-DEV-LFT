<?php
require_once __DIR__ . '/db.php';
$basePath = ($currentPage ?? 'HOME') === 'HOME' ? '' : '../';
$loggedInUser = currentUser();
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
<header class="site-header">
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
                    <a href="<?= $basePath ?>Dashboard/index.php" class="account-link <?= $currentPage === 'DASHBOARD' ? 'active' : '' ?>">
                        <i class="fa-regular fa-user" aria-hidden="true"></i>
                        MY DASHBOARD
                    </a>
                <?php elseif ($loggedInUser['role'] === 'staff'): ?>
                    <a href="<?= $basePath ?>Staff/index.php" class="account-link">
                        <i class="fa-solid fa-headset" aria-hidden="true"></i>
                        STAFF DESK
                    </a>
                <?php elseif ($loggedInUser['role'] === 'admin'): ?>
                    <a href="<?= $basePath ?>Admin/index.php" class="account-link">
                        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                        ADMIN
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?= $basePath ?>Login/index.php" class="account-link <?= $currentPage === 'LOGIN' ? 'active' : '' ?>">
                    <i class="fa-regular fa-user" aria-hidden="true"></i>
                    LOG IN
                </a>
            <?php endif; ?>

            <?php if ($loggedInUser && $loggedInUser['role'] === 'customer'): ?>
                <a href="<?= $basePath ?>Booking/index.php" class="nav-button">BOOK A SPACE</a>
            <?php else: ?>
                <a href="<?= $basePath ?>Contact/index.php#tour" class="nav-button">BOOK A TOUR</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

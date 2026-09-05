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

$assetVersion = '20260905-3';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'LFT Dumaguete') ?></title>
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/style.css?v=<?= $assetVersion ?>">
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/portal.css?v=<?= $assetVersion ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .customer-header .nav-container { width: min(1440px, calc(100% - 64px)); }
        .customer-header .main-nav { gap: 25px; }
        .customer-header-actions {
            display: flex;
            align-items: center;
            gap: 0;
            margin-left: 10px;
            white-space: nowrap;
        }
        .customer-checkin-button {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 48px;
            padding: 0 24px !important;
            border: 1.5px solid var(--green);
            border-radius: 999px;
            color: var(--green) !important;
            background: #fff;
            font-size: 12px !important;
            font-weight: 700;
            letter-spacing: .2px;
            transition: .2s ease;
        }
        .customer-checkin-button::after { display: none !important; }
        .customer-checkin-button i { font-size: 18px; }
        .customer-checkin-button:hover { background: var(--green); color: #fff !important; }

        .customer-notification-menu,
        .customer-account-menu { position: relative; }
        .customer-notification-menu { margin-left: 22px; padding: 0 22px; border-left: 1px solid #d8d8d8; border-right: 1px solid #d8d8d8; }
        .customer-notification-menu summary,
        .customer-account-menu summary { list-style: none; cursor: pointer; }
        .customer-notification-menu summary::-webkit-details-marker,
        .customer-account-menu summary::-webkit-details-marker { display: none; }
        .customer-notification-menu summary {
            position: relative;
            width: 34px;
            height: 48px;
            display: grid;
            place-items: center;
            border: 0;
            background: transparent;
            color: #111;
        }
        .customer-notification-menu summary i { font-size: 23px; }
        .customer-notification-menu summary:hover,
        .customer-notification-menu[open] summary { color: var(--green); }
        .customer-notification-badge {
            position: absolute;
            top: 1px;
            right: -4px;
            min-width: 19px;
            height: 19px;
            padding: 0 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #d92d2d;
            color: #fff;
            border: 2px solid #fff;
            font-size: 9px;
            font-weight: 800;
            line-height: 1;
        }

        .customer-account-menu { margin-left: 22px; }
        .customer-account-menu summary {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 48px;
            padding: 0;
            border: 0;
            background: transparent;
        }
        .customer-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            background: var(--green);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
        }
        .customer-account-copy { display: flex; flex-direction: row; gap: 4px; align-items: baseline; color: #111; }
        .customer-account-copy small { color: #111; font-size: 12px; font-weight: 700; letter-spacing: 0; }
        .customer-account-copy strong { color: #111; font-size: 12px; font-weight: 700; max-width: 92px; overflow: hidden; text-overflow: ellipsis; }
        .account-chevron { color: #111; font-size: 11px; margin-left: 2px; transition: transform .2s ease; }
        .customer-account-menu[open] .account-chevron { transform: rotate(180deg); }

        .customer-notification-dropdown,
        .customer-account-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 12px);
            z-index: 1400;
            background: #fff;
            border: 1px solid #dedede;
            border-radius: 10px;
            box-shadow: 0 18px 48px rgba(0,0,0,.14);
        }
        .customer-notification-dropdown { width: 350px; padding: 10px; }
        .customer-account-dropdown { width: 285px; padding: 10px; }
        .customer-dropdown-heading { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 10px 13px; border-bottom:1px solid #ececec; }
        .customer-dropdown-heading > div { display:flex; flex-direction:column; }
        .customer-dropdown-heading strong { color:var(--green); font-size:14px; }
        .customer-dropdown-heading small { color:var(--gray); font-size:10px; }
        .customer-dropdown-heading > span,
        .customer-menu-count { min-width:20px; height:20px; padding:0 6px; display:inline-flex; align-items:center; justify-content:center; border-radius:999px; background:#d92d2d; color:#fff; font-size:9px; font-weight:800; }
        .customer-notification-list { display:grid; }
        .customer-notification-list > a { display:grid; grid-template-columns:38px minmax(0,1fr); gap:10px; padding:12px 9px !important; border-bottom:1px solid #f0f0f0; border-radius:8px; }
        .customer-notification-list > a::after,
        .customer-account-dropdown > a::after,
        .customer-notification-view-all::after { display:none !important; }
        .customer-notification-list > a:hover { background:var(--cream); }
        .customer-notification-icon { width:36px; height:36px; display:grid; place-items:center; border-radius:50%; background:rgba(16,63,46,.08); color:var(--green); }
        .customer-notification-copy { display:flex; min-width:0; flex-direction:column; gap:2px; }
        .customer-notification-copy strong { color:#222; font-size:12px; }
        .customer-notification-copy small { color:var(--gray); font-size:10px; line-height:1.35; }
        .customer-notification-copy em { color:var(--gold); font-size:9px; font-style:normal; font-weight:700; }
        .customer-notification-empty { display:flex; flex-direction:column; align-items:center; gap:8px; padding:28px 14px; color:var(--gray); text-align:center; font-size:11px; }
        .customer-notification-view-all { display:block; padding:10px !important; color:var(--green) !important; text-align:center; font-size:11px !important; font-weight:700; border-top:1px solid #ececec; }

        .customer-account-card { display:flex; gap:11px; align-items:center; padding:10px 9px 14px; margin-bottom:5px; border-bottom:1px solid #eee; }
        .customer-avatar-large { width:42px; height:42px; }
        .customer-account-card div { min-width:0; display:flex; flex-direction:column; }
        .customer-account-card strong { color:var(--green); font-size:13px; overflow:hidden; text-overflow:ellipsis; }
        .customer-account-card small { color:var(--gray); font-size:10px; overflow:hidden; text-overflow:ellipsis; }
        .customer-account-dropdown > a { display:flex !important; align-items:center; gap:10px; padding:10px !important; border-radius:8px; color:#2f2f2f; font-size:12px !important; font-weight:600; }
        .customer-account-dropdown > a i { width:18px; color:var(--gold); text-align:center; }
        .customer-account-dropdown > a > span:nth-child(2) { flex:1; }
        .customer-account-dropdown > a:hover { background:var(--cream); color:var(--green); }
        .customer-account-dropdown .customer-logout-link { margin-top:5px; border-top:1px solid #eee; color:#8d2f27; }
        .customer-account-dropdown .customer-logout-link i { color:#8d2f27; }

        @media (max-width: 1280px) {
            .customer-header .main-nav { gap: 15px; }
            .customer-checkin-button { padding: 0 16px !important; }
            .customer-account-copy { display: none; }
        }
        @media (max-width: 850px) {
            .customer-header-actions { width:100%; flex-direction:column; align-items:stretch; gap:8px; margin:10px 0 0; }
            .customer-checkin-button { width:100%; }
            .customer-notification-menu { margin:0; padding:0; border:0; width:100%; }
            .customer-notification-menu summary { width:100%; height:44px; display:flex; align-items:center; gap:10px; padding:0 12px; border:1px solid rgba(16,63,46,.14); border-radius:8px; }
            .customer-notification-menu summary::after { content:'Notifications'; color:var(--green); font-size:12px; font-weight:700; }
            .customer-notification-badge { position:static; margin-left:auto; border:0; }
            .customer-account-menu { margin:0; width:100%; }
            .customer-account-menu summary { width:100%; padding:0 12px; border:1px solid rgba(16,63,46,.14); border-radius:8px; }
            .customer-account-copy { display:flex; }
            .customer-notification-dropdown,
            .customer-account-dropdown { position:static; width:100%; margin-top:8px; box-shadow:none; }
        }
    </style>
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
                                    <small>Hi,</small>
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
                                <a href="<?= $basePath ?>Dashboard/account.php"><i class="fa-regular fa-user" aria-hidden="true"></i><span>My Profile</span></a>
                                <a href="<?= $basePath ?>Dashboard/index.php"><i class="fa-regular fa-calendar-check" aria-hidden="true"></i><span>My Bookings</span></a>
                                <a href="<?= $basePath ?>Membership/index.php"><i class="fa-regular fa-credit-card" aria-hidden="true"></i><span>My Membership</span></a>
                                <a href="<?= $basePath ?>Dashboard/index.php"><i class="fa-regular fa-bell" aria-hidden="true"></i><span>Notifications</span><?php if ($customerNotificationCount > 0): ?><span class="customer-menu-count"><?= $customerNotificationCount > 9 ? '9+' : $customerNotificationCount ?></span><?php endif; ?></a>
                                <a href="<?= $basePath ?>Dashboard/account.php"><i class="fa-solid fa-gear" aria-hidden="true"></i><span>Settings</span></a>
                                <a href="<?= $basePath ?>Logout/index.php" class="customer-logout-link"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i><span>Logout</span></a>
                            </div>
                        </details>
                    </div>
                <?php elseif ($loggedInUser['role'] === 'staff'): ?>
                    <a href="<?= $basePath ?>Staff/index.php" class="account-link"><i class="fa-solid fa-headset" aria-hidden="true"></i>STAFF DESK</a>
                    <a href="<?= $basePath ?>Contact/index.php#tour" class="nav-button">BOOK A TOUR</a>
                <?php elseif ($loggedInUser['role'] === 'admin'): ?>
                    <a href="<?= $basePath ?>Admin/index.php" class="account-link"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i>ADMIN</a>
                    <a href="<?= $basePath ?>Contact/index.php#tour" class="nav-button">BOOK A TOUR</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?= $basePath ?>Login/index.php" class="account-link <?= $currentPage === 'LOGIN' ? 'active' : '' ?>"><i class="fa-regular fa-user" aria-hidden="true"></i>LOG IN</a>
                <a href="<?= $basePath ?>Contact/index.php#tour" class="nav-button">BOOK A TOUR</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<script>
document.addEventListener('click', function (event) {
    document.querySelectorAll('.customer-header details[open]').forEach(function (menu) {
        if (!menu.contains(event.target)) menu.removeAttribute('open');
    });
});
document.querySelectorAll('.customer-header details').forEach(function (menu) {
    menu.addEventListener('toggle', function () {
        if (!menu.open) return;
        document.querySelectorAll('.customer-header details[open]').forEach(function (other) {
            if (other !== menu) other.removeAttribute('open');
        });
    });
});
</script>

<?php
$currentFolder = basename(dirname($_SERVER['SCRIPT_FILENAME']));
$pendingBookings = (int) db()->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn();
$unreadMessages = (int) db()->query("SELECT COUNT(*) FROM messages WHERE status = 'Unread'")->fetchColumn();
$isStaffWorkspace = $currentFolder === 'Staff';
$isStaff = ($user['role'] ?? '') === 'staff' || $isStaffWorkspace;
$logoutPath = $isStaff ? '../Logout/index.php' : null;

if ($isStaff) {
    $adminPath = '';
    $assetPath = '../';
} elseif ($currentFolder === 'Admin') {
    $adminPath = '';
    $assetPath = '../';
} else {
    $adminPath = '../';
    $assetPath = '../../';
}
?>

<link rel="stylesheet" href="<?= $assetPath ?>assets/css/portal.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<aside class="admin-sidebar">
    <a class="admin-brand" href="<?= $isStaff ? '../Staff/index.php' : $adminPath ?>">
        <img src="<?= $assetPath ?>assets/images/Logo.png" alt="LFT Dumaguete">
        <span>DUMAGUETE</span>
        <small>CURATED WORKSPACES</small>
    </a>

    <div class="admin-nav-heading">Workspace</div>
    <nav>
    <?php if ($isStaff): ?>
        <a class="<?= ($currentStaffPage ?? 'overview') === 'overview' ? 'admin-nav-active' : '' ?>" href="../Staff/index.php">
            <i class="fa-solid fa-table-cells-large" aria-hidden="true"></i><span>Desk overview</span>
        </a>
        <a class="<?= ($currentStaffPage ?? '') === 'bookings' ? 'admin-nav-active' : '' ?>" href="../Staff/bookings.php">
            <i class="fa-regular fa-calendar-check" aria-hidden="true"></i><span>Booking queue</span>
            <?php if ($pendingBookings): ?><small class="admin-nav-count"><?= $pendingBookings ?></small><?php endif; ?>
        </a>
        <a href="../Staff/index.php#arrivals">
            <i class="fa-solid fa-person-walking-arrow-right" aria-hidden="true"></i><span>Today’s arrivals</span>
        </a>
        <?php if (($user['role'] ?? '') === 'admin'): ?>
            <a href="../Admin/index.php">
                <i class="fa-solid fa-shield-halved" aria-hidden="true"></i><span>Back to Admin</span>
            </a>
        <?php endif; ?>
        <a href="../index.php">
            <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i><span>Public website</span>
        </a>
    <?php else: ?>
        <a class="<?= $currentFolder === 'Admin' ? 'admin-nav-active' : '' ?>" href="<?= $adminPath ?>">
            <i class="fa-solid fa-table-cells-large" aria-hidden="true"></i><span>Dashboard</span>
        </a>
        <a class="<?= $currentFolder === 'bookings' ? 'admin-nav-active' : '' ?>" href="<?= $adminPath ?>bookings/">
            <i class="fa-regular fa-calendar-check" aria-hidden="true"></i><span>Bookings</span>
            <?php if ($pendingBookings): ?><small class="admin-nav-count"><?= $pendingBookings ?></small><?php endif; ?>
        </a>
        <a class="<?= $currentFolder === 'customers' ? 'admin-nav-active' : '' ?>" href="<?= $adminPath ?>customers/">
            <i class="fa-solid fa-users" aria-hidden="true"></i><span>Customers</span>
        </a>
        <a class="<?= $currentFolder === 'staff' ? 'admin-nav-active' : '' ?>" href="<?= $adminPath ?>staff/">
            <i class="fa-solid fa-users-gear" aria-hidden="true"></i><span>Staff accounts</span>
        </a>
        <a class="<?= $currentFolder === 'memberships' ? 'admin-nav-active' : '' ?>" href="<?= $adminPath ?>memberships/">
            <i class="fa-regular fa-credit-card" aria-hidden="true"></i><span>Memberships</span>
        </a>
        <a class="<?= $currentFolder === 'spaces' ? 'admin-nav-active' : '' ?>" href="<?= $adminPath ?>spaces/">
            <i class="fa-solid fa-couch" aria-hidden="true"></i><span>Spaces</span>
        </a>
        <a class="<?= $currentFolder === 'amenities' ? 'admin-nav-active' : '' ?>" href="<?= $adminPath ?>amenities/">
            <i class="fa-regular fa-star" aria-hidden="true"></i><span>Amenities</span>
        </a>
        <a class="<?= $currentFolder === 'events' ? 'admin-nav-active' : '' ?>" href="<?= $adminPath ?>events/">
            <i class="fa-regular fa-calendar" aria-hidden="true"></i><span>Events</span>
        </a>
        <a class="<?= $currentFolder === 'messages' ? 'admin-nav-active' : '' ?>" href="<?= $adminPath ?>messages/">
            <i class="fa-regular fa-envelope" aria-hidden="true"></i><span>Messages</span>
            <?php if ($unreadMessages): ?><small class="admin-nav-count"><?= $unreadMessages ?></small><?php endif; ?>
        </a>
        <a class="<?= $currentFolder === 'settings' ? 'admin-nav-active' : '' ?>" href="<?= $adminPath ?>settings/">
            <i class="fa-solid fa-gear" aria-hidden="true"></i><span>Settings</span>
        </a>
    <?php endif; ?>
    </nav>

    <div class="admin-sidebar-note">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>Operations online</span>
    </div>

    <a class="admin-logout" href="<?= $logoutPath ?? ($adminPath . 'logout.php') ?>">
        <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i><span>Logout</span>
    </a>
</aside>

<script>
    window.adminPath = <?= json_encode($adminPath) ?>;
    window.adminLogout = <?= json_encode($logoutPath ?? ($adminPath . 'logout.php')) ?>;
    window.adminUser = <?= json_encode(['name' => $user['name'], 'email' => $user['email'], 'role' => ucfirst($user['role'])]) ?>;
    window.pendingBookings = <?= $pendingBookings ?>;
    window.notificationPath = <?= json_encode($isStaff ? '../Staff/bookings.php?status=Pending' : $adminPath . 'bookings/') ?>;
</script>
<script src="<?= $assetPath ?>assets/js/admin.js"></script>

<?php
$currentFolder = basename(dirname($_SERVER['SCRIPT_FILENAME']));
$pendingBookings = (int) db()->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn();
$unreadMessages = (int) db()->query("SELECT COUNT(*) FROM messages WHERE status = 'Unread'")->fetchColumn();
$isStaffWorkspace = $currentFolder === 'Staff';
$isStaff = ($user['role'] ?? '') === 'staff' || $isStaffWorkspace;
$logoutPath = $isStaff ? '../Logout/index.php' : null;
$csrfTokenValue = csrfToken();

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
        <span>DUMAGUETE</span><small>CURATED WORKSPACES</small>
    </a>
    <div class="admin-nav-heading">Workspace</div>
    <nav>
    <?php if ($isStaff): ?>
        <a class="<?= ($currentStaffPage ?? 'overview') === 'overview' ? 'admin-nav-active' : '' ?>" href="../Staff/index.php"><i class="fa-solid fa-table-cells-large"></i><span>Desk overview</span></a>
        <a class="<?= ($currentStaffPage ?? '') === 'bookings' ? 'admin-nav-active' : '' ?>" href="../Staff/bookings.php"><i class="fa-regular fa-calendar-check"></i><span>Booking queue</span><?php if ($pendingBookings): ?><small class="admin-nav-count"><?= $pendingBookings ?></small><?php endif; ?></a>
        <a href="../Staff/index.php#arrivals"><i class="fa-solid fa-person-walking-arrow-right"></i><span>Today’s arrivals</span></a>
        <?php if (($user['role'] ?? '') === 'admin'): ?><a href="../Admin/index.php"><i class="fa-solid fa-shield-halved"></i><span>Back to Admin</span></a><?php endif; ?>
        <a href="../index.php"><i class="fa-solid fa-arrow-up-right-from-square"></i><span>Public website</span></a>
    <?php else: ?>
        <a class="<?= $currentFolder === 'Admin' ? 'admin-nav-active' : '' ?>" href="<?= $adminPath ?>"><i class="fa-solid fa-table-cells-large"></i><span>Dashboard</span></a>
        <a class="<?= $currentFolder === 'bookings' ? 'admin-nav-active' : '' ?>" href="<?= $adminPath ?>bookings/"><i class="fa-regular fa-calendar-check"></i><span>Bookings</span><?php if ($pendingBookings): ?><small class="admin-nav-count"><?= $pendingBookings ?></small><?php endif; ?></a>
        <a class="<?= $currentFolder === 'customers' ? 'admin-nav-active' : '' ?>" href="<?= $adminPath ?>customers/"><i class="fa-solid fa-users"></i><span>Customers</span></a>
        <a class="<?= $currentFolder === 'staff' ? 'admin-nav-active' : '' ?>" href="<?= $adminPath ?>staff/"><i class="fa-solid fa-users-gear"></i><span>Staff accounts</span></a>
        <a class="<?= $currentFolder === 'memberships' ? 'admin-nav-active' : '' ?>" href="<?= $adminPath ?>memberships/"><i class="fa-regular fa-credit-card"></i><span>Memberships</span></a>
        <a class="<?= $currentFolder === 'spaces' ? 'admin-nav-active' : '' ?>" href="<?= $adminPath ?>spaces/"><i class="fa-solid fa-couch"></i><span>Spaces</span></a>
        <a class="<?= $currentFolder === 'amenities' ? 'admin-nav-active' : '' ?>" href="<?= $adminPath ?>amenities/"><i class="fa-regular fa-star"></i><span>Amenities</span></a>
        <a class="<?= $currentFolder === 'events' ? 'admin-nav-active' : '' ?>" href="<?= $adminPath ?>events/"><i class="fa-regular fa-calendar"></i><span>Events</span></a>
        <a class="<?= $currentFolder === 'messages' ? 'admin-nav-active' : '' ?>" href="<?= $adminPath ?>messages/"><i class="fa-regular fa-envelope"></i><span>Messages</span><?php if ($unreadMessages): ?><small class="admin-nav-count"><?= $unreadMessages ?></small><?php endif; ?></a>
        <a class="<?= $currentFolder === 'settings' ? 'admin-nav-active' : '' ?>" href="<?= $adminPath ?>settings/"><i class="fa-solid fa-gear"></i><span>Settings</span></a>
    <?php endif; ?>
    </nav>
    <div class="admin-sidebar-note"><i class="fa-solid fa-circle-check"></i><span>Operations online</span></div>
    <a class="admin-logout" href="<?= $logoutPath ?? ($adminPath . 'logout.php') ?>"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Logout</span></a>
</aside>
<script>
window.adminPath = <?= json_encode($adminPath) ?>;
window.adminLogout = <?= json_encode($logoutPath ?? ($adminPath . 'logout.php')) ?>;
window.adminUser = <?= json_encode(['name' => $user['name'], 'email' => $user['email'], 'role' => ucfirst($user['role'])]) ?>;
window.pendingBookings = <?= $pendingBookings ?>;
window.notificationPath = <?= json_encode($isStaff ? '../Staff/bookings.php?status=Pending' : $adminPath . 'bookings/') ?>;
window.csrfToken = <?= json_encode($csrfTokenValue) ?>;
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[method="post"], form[method="POST"]').forEach(function (form) {
        if (form.querySelector('input[name="csrf_token"]')) return;
        var input = document.createElement('input');
        input.type = 'hidden'; input.name = 'csrf_token'; input.value = window.csrfToken;
        form.appendChild(input);
    });
});
</script>
<script src="<?= $assetPath ?>assets/js/admin.js"></script>

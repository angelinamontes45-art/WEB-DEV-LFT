<?php
require_once __DIR__ . '/../Includes/db.php';

$adminUser = currentUser();
if (!$adminUser || $adminUser['role'] !== 'admin') {
    $currentFolder = basename(dirname($_SERVER['SCRIPT_FILENAME'] ?? ''));
    $loginPath = $currentFolder === 'Admin' ? '../Login/index.php' : '../../Login/index.php';
    header('Location: ' . $loginPath);
    exit;
}

function adminRedirect(string $section, string $query = ''): never
{
    $allowedSections = ['bookings', 'customers', 'staff', 'memberships', 'spaces', 'amenities', 'events', 'messages', 'settings'];
    $section = in_array($section, $allowedSections, true) ? $section : '';
    $target = $section === '' ? '../index.php' : '../' . $section . '/index.php';

    if ($query !== '') {
        $target .= '?' . ltrim($query, '?');
    }

    header('Location: ' . $target);
    exit;
}

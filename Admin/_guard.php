<?php
require_once __DIR__ . '/../Includes/db.php';
requireRole(['admin']);

function adminRedirect(string $section, string $query = ''): never
{
    $allowedSections = ['bookings', 'customers', 'staff', 'memberships', 'spaces', 'amenities', 'events', 'messages', 'settings'];
    $section = in_array($section, $allowedSections, true) ? $section : '';

    if ($section === '') {
        $target = '../index.php';
    } else {
        $target = '../' . $section . '/index.php';
    }

    if ($query !== '') {
        $target .= '?' . ltrim($query, '?');
    }

    header('Location: ' . $target);
    exit;
}

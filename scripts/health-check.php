<?php

declare(strict_types=1);

require dirname(__DIR__) . '/Includes/db.php';

$connection = db();
$requiredTables = ['users','bookings','spaces','memberships','amenities','events','messages','site_settings'];
$tables = $connection->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
$missing = array_values(array_diff($requiredTables, $tables));
if ($missing) {
    fwrite(STDERR, 'Missing database tables: ' . implode(', ', $missing) . PHP_EOL);
    exit(1);
}

$bookingColumns = columnNames($connection, 'bookings');
foreach (['duration_minutes','guest_count','checked_in_at','updated_at'] as $column) {
    if (!in_array($column, $bookingColumns, true)) {
        fwrite(STDERR, 'Missing bookings column: ' . $column . PHP_EOL);
        exit(1);
    }
}

if (APP_TIMEZONE !== 'Asia/Manila' || date_default_timezone_get() !== 'Asia/Manila') {
    fwrite(STDERR, 'Application timezone is not Asia/Manila.' . PHP_EOL);
    exit(1);
}

if (count(bookingStatusTransitions()) !== 5) {
    fwrite(STDERR, 'Booking workflow configuration is invalid.' . PHP_EOL);
    exit(1);
}

echo "LFT deployment health check passed.\n";

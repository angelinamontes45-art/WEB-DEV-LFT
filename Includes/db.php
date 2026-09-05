<?php

declare(strict_types=1);

require_once __DIR__ . '/security.php';
require_once dirname(__DIR__) . '/database/schema.php';

function db(): PDO
{
    static $connection;
    if ($connection instanceof PDO) return $connection;

    $storagePath = dirname(__DIR__) . '/storage';
    if (!is_dir($storagePath) && !mkdir($storagePath, 0750, true) && !is_dir($storagePath)) {
        throw new RuntimeException('Unable to create storage directory.');
    }

    $connection = new PDO('sqlite:' . $storagePath . '/lft.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $connection->exec('PRAGMA foreign_keys = ON');
    $connection->exec('PRAGMA busy_timeout = 5000');
    ensureDatabaseSchema($connection);
    return $connection;
}

function currentUser(): ?array
{
    startUserSession();
    if (empty($_SESSION['user_id'])) return null;
    $statement = db()->prepare('SELECT id, name, email, phone, role, created_at FROM users WHERE id = ?');
    $statement->execute([(int)$_SESSION['user_id']]);
    return $statement->fetch() ?: null;
}

function requireUser(): array
{
    $user = currentUser();
    if (!$user) {
        $next = urlencode($_SERVER['REQUEST_URI'] ?? '../Dashboard/index.php');
        header('Location: ../Login/index.php?next=' . $next);
        exit;
    }
    return $user;
}

function requireRole(array $roles): array
{
    $user = currentUser();
    if (!$user || !in_array($user['role'], $roles, true)) {
        header('Location: ../Login/index.php');
        exit;
    }
    return $user;
}

function e(string|int|float|null $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function appToday(): string
{
    return (new DateTimeImmutable('now', new DateTimeZone(APP_TIMEZONE)))->format('Y-m-d');
}

function appNow(): DateTimeImmutable
{
    return new DateTimeImmutable('now', new DateTimeZone(APP_TIMEZONE));
}

function bookingReference(int $id): string
{
    return 'LFT-' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);
}

function bookingStatusTransitions(): array
{
    return [
        'Pending' => ['Confirmed', 'Cancelled'],
        'Confirmed' => ['Checked in', 'Cancelled'],
        'Checked in' => ['Completed'],
        'Completed' => [],
        'Cancelled' => [],
    ];
}

function canTransitionBooking(string $from, string $to, bool $adminOverride = false): bool
{
    if ($from === $to) return true;
    if ($adminOverride && in_array($to, array_keys(bookingStatusTransitions()), true)) return true;
    return in_array($to, bookingStatusTransitions()[$from] ?? [], true);
}

function updateBookingStatus(PDO $connection, int $bookingId, string $newStatus, bool $adminOverride = false): bool
{
    $statement = $connection->prepare('SELECT status FROM bookings WHERE id = ?');
    $statement->execute([$bookingId]);
    $current = $statement->fetchColumn();
    if (!is_string($current) || !canTransitionBooking($current, $newStatus, $adminOverride)) return false;

    $checkedInAt = $newStatus === 'Checked in' ? appNow()->format('Y-m-d H:i:s') : null;
    $sql = $newStatus === 'Checked in'
        ? 'UPDATE bookings SET status = ?, checked_in_at = ?, updated_at = ? WHERE id = ?'
        : 'UPDATE bookings SET status = ?, updated_at = ? WHERE id = ?';
    $update = $connection->prepare($sql);
    if ($newStatus === 'Checked in') {
        $update->execute([$newStatus, $checkedInAt, appNow()->format('Y-m-d H:i:s'), $bookingId]);
    } else {
        $update->execute([$newStatus, appNow()->format('Y-m-d H:i:s'), $bookingId]);
    }
    return $update->rowCount() > 0;
}

function bookingHasConflict(PDO $connection, string $space, string $date, string $time, int $durationMinutes, ?int $excludeId = null): bool
{
    $durationMinutes = max(30, min(720, $durationMinutes));
    $start = DateTimeImmutable::createFromFormat('!H:i', $time);
    if (!$start) return true;
    $end = $start->modify('+' . $durationMinutes . ' minutes')->format('H:i');

    $sql = "SELECT visit_time, duration_minutes FROM bookings
            WHERE space = ? AND visit_date = ?
              AND status NOT IN ('Cancelled', 'Completed')";
    $params = [$space, $date];
    if ($excludeId !== null) {
        $sql .= ' AND id <> ?';
        $params[] = $excludeId;
    }
    $statement = $connection->prepare($sql);
    $statement->execute($params);

    foreach ($statement->fetchAll() as $booking) {
        $otherStart = DateTimeImmutable::createFromFormat('!H:i', (string)$booking['visit_time']);
        if (!$otherStart) continue;
        $otherEnd = $otherStart->modify('+' . max(30, (int)$booking['duration_minutes']) . ' minutes')->format('H:i');
        if ($time < $otherEnd && $end > (string)$booking['visit_time']) return true;
    }
    return false;
}

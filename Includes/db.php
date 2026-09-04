<?php
function db(): PDO
{
    static $connection;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $storagePath = dirname(__DIR__) . '/storage';
    if (!is_dir($storagePath)) {
        mkdir($storagePath, 0750, true);
    }

    $connection = new PDO('sqlite:' . $storagePath . '/lft.sqlite');
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $connection->exec('PRAGMA foreign_keys = ON');
    $connection->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT "customer" CHECK (role IN ("customer", "staff", "admin")),
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
    $userColumns = $connection->query('PRAGMA table_info(users)')->fetchAll();
    $hasRoleColumn = false;
    foreach ($userColumns as $column) {
        if ($column['name'] === 'role') {
            $hasRoleColumn = true;
            break;
        }
    }
    if (!$hasRoleColumn) {
        $connection->exec('ALTER TABLE users ADD COLUMN role TEXT NOT NULL DEFAULT "customer"');
    }
    $connection->exec('CREATE TABLE IF NOT EXISTS messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sender TEXT NOT NULL,
        email TEXT NOT NULL,
        subject TEXT NOT NULL,
        message TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT "Unread",
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
    $connection->exec('CREATE TABLE IF NOT EXISTS site_settings (
        id INTEGER PRIMARY KEY CHECK (id = 1),
        website_name TEXT NOT NULL DEFAULT "LFT Dumaguete",
        email TEXT NOT NULL DEFAULT "lftdumaguete@gmail.com",
        phone TEXT NOT NULL DEFAULT "+63 9912345678",
        address TEXT NOT NULL DEFAULT "Hibard St. Dumaguete City, Negros Oriental",
        facebook TEXT NOT NULL DEFAULT "",
        instagram TEXT NOT NULL DEFAULT "",
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
    $connection->exec('INSERT OR IGNORE INTO site_settings (id) VALUES (1)');
    $connection->exec('CREATE TABLE IF NOT EXISTS bookings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        booking_type TEXT NOT NULL CHECK (booking_type IN ("booking", "walk-in")),
        space TEXT NOT NULL,
        visit_date TEXT NOT NULL,
        visit_time TEXT NOT NULL,
        notes TEXT,
        status TEXT NOT NULL DEFAULT "Pending",
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )');
    $connection->exec('CREATE TABLE IF NOT EXISTS spaces (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        category TEXT NOT NULL,
        description TEXT NOT NULL,
        rates TEXT NOT NULL,
        seats INTEGER NOT NULL DEFAULT 1,
        image TEXT NOT NULL DEFAULT "common.png",
        active INTEGER NOT NULL DEFAULT 1,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
    $spaceColumns = array_column($connection->query('PRAGMA table_info(spaces)')->fetchAll(), 'name');
    if (!in_array('seats', $spaceColumns, true)) {
        $connection->exec('ALTER TABLE spaces ADD COLUMN seats INTEGER NOT NULL DEFAULT 1');
    }
    $connection->exec('CREATE TABLE IF NOT EXISTS memberships (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        label TEXT NOT NULL,
        name TEXT NOT NULL,
        price TEXT NOT NULL,
        period TEXT NOT NULL,
        description TEXT NOT NULL,
        featured INTEGER NOT NULL DEFAULT 0,
        active INTEGER NOT NULL DEFAULT 1,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
    $membershipColumns = array_column($connection->query('PRAGMA table_info(memberships)')->fetchAll(), 'name');
    if (!in_array('features', $membershipColumns, true)) $connection->exec("ALTER TABLE memberships ADD COLUMN features TEXT NOT NULL DEFAULT ''");
    if (!in_array('status', $membershipColumns, true)) $connection->exec("ALTER TABLE memberships ADD COLUMN status TEXT NOT NULL DEFAULT 'Active'");
    $connection->exec("UPDATE memberships SET status = CASE WHEN active = 1 THEN 'Active' ELSE 'Inactive' END WHERE status = '' OR status IS NULL");
    if ((int) $connection->query('SELECT COUNT(*) FROM memberships')->fetchColumn() === 0) {
        $memberships = [
            ['DAY PASS', 'Commons Day', '₱199', 'day', 'One full day of access to our shared workspace, Wi-Fi, and coffee.', 'Shared workspace\nWi-Fi\nCoffee', 0],
            ['MOST FLEXIBLE', 'Monthly Commons', '₱3,500', 'month', 'Your regular desk away from home, plus community events and member rates.', 'Regular desk\nCommunity events\nMember rates', 1],
            ['PRIVATE ACCESS', 'The Club', '₱25,000', 'month', 'A premium private room and full access for focused teams and professionals.', 'Private room\nFull access\nTeam workspace', 0],
        ];
        $insert = $connection->prepare('INSERT INTO memberships (label, name, price, period, description, features, featured) VALUES (?, ?, ?, ?, ?, ?, ?)');
        foreach ($memberships as $membership) $insert->execute($membership);
    }
    $connection->exec('CREATE TABLE IF NOT EXISTS amenities (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        icon TEXT NOT NULL,
        name TEXT NOT NULL,
        description TEXT NOT NULL,
        active INTEGER NOT NULL DEFAULT 1,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
    if ((int) $connection->query('SELECT COUNT(*) FROM amenities')->fetchColumn() === 0) {
        $amenities = [
            ['fa-solid fa-wifi', 'Fast Wi-Fi', 'Reliable connectivity for calls, collaboration, and deep work.'],
            ['fa-solid fa-mug-hot', 'Coffee corner', 'Good coffee and easy refills to keep your ideas in motion.'],
            ['fa-solid fa-door-open', 'Meeting rooms', 'Private, professional spaces when your work needs a little more focus.'],
            ['fa-solid fa-calendar-days', 'Community events', 'Workshops and gatherings that turn useful connections into momentum.'],
            ['fa-solid fa-print', 'Work essentials', 'Printing, power, comfortable seating, and the tools you reach for every day.'],
            ['fa-solid fa-location-dot', 'Central Dumaguete', 'Find us on Hibard St., close to the city energy and everyday essentials.'],
        ];
        $insert = $connection->prepare('INSERT INTO amenities (icon, name, description) VALUES (?, ?, ?)');
        foreach ($amenities as $amenity) $insert->execute($amenity);
    }
    $connection->exec('CREATE TABLE IF NOT EXISTS events (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        event_date TEXT NOT NULL,
        event_time TEXT NOT NULL,
        category TEXT NOT NULL,
        title TEXT NOT NULL,
        description TEXT NOT NULL,
        active INTEGER NOT NULL DEFAULT 1
    )');
    $eventColumns = array_column($connection->query('PRAGMA table_info(events)')->fetchAll(), 'name');
    if (!in_array('image', $eventColumns, true)) $connection->exec("ALTER TABLE events ADD COLUMN image TEXT NOT NULL DEFAULT 'conference.png'");
    if (!in_array('location', $eventColumns, true)) $connection->exec("ALTER TABLE events ADD COLUMN location TEXT NOT NULL DEFAULT 'LFT Dumaguete'");
    if (!in_array('status', $eventColumns, true)) $connection->exec("ALTER TABLE events ADD COLUMN status TEXT NOT NULL DEFAULT 'Active'");
    $connection->exec("UPDATE events SET status = CASE WHEN active = 1 THEN 'Active' ELSE 'Inactive' END WHERE status = '' OR status IS NULL");
    $spaces = [
                ['LFT Commons', 'Flexible desks', 'A bright, social workspace for freelancers and remote teams.', '₱199 / day', 30, 'common.png'],
                ['Podcast Studio', 'Creator studio', 'A ready-to-use recording room for conversations and content.', '₱650 / hour', 4, 'podcast.png'],
                ['The Club', 'Private workspace', 'A premium room with full access for teams who need room to grow.', '₱25,000 / month', 8, 'the club.png'],
                ['Conference Room', 'Meetings', 'A professional setting for meetings and workshops.', '₱850 / hour', 12, 'conference.png'],
                ['LFT Reserve', 'Private room', 'A private room reserved by the hour for focused work and calls.', '₱240 / hour', 4, 'common.png'],
                ['Learning Studio', 'Learning and workshops', 'A welcoming room for classes, study sessions, and workshops.', "₱199 / hour\n₱548 / 3 hours", 20, 'common.png'],
                ['Focus Suite', 'Deep work', 'A quiet suite for uninterrupted focus and productivity.', "₱149 / hour\n₱399 / 3 hours", 1, 'about.png'],
                ['Team Suite', 'Team workspace', 'A collaborative room for meetings and growing teams.', "₱450 / hour\n₱1,600 / half day (4 hours)\n₱2,800 / day", 10, 'conference.png'],
                ['Private Office', 'Private workspace', 'A private professional office for focused work and calls.', "₱1,250 / hour\n₱8,999 / day", 4, 'the club.png'],
                ['Content Studio', 'Content creation', 'A ready-to-use studio for podcasts, presentations, and content.', "₱650 / hour\n₱3,999 / day", 6, 'podcast.png'],
    ];
    $findSpace = $connection->prepare('SELECT COUNT(*) FROM spaces WHERE name = ?');
            $insert = $connection->prepare('INSERT INTO spaces (name, category, description, rates, seats, image) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($spaces as $space) {
        $findSpace->execute([$space[0]]);
        if ((int) $findSpace->fetchColumn() === 0) $insert->execute($space);
    }
            $seatUpdates = $connection->prepare('UPDATE spaces SET seats = ? WHERE name = ?');
            foreach ($spaces as $space) $seatUpdates->execute([$space[4], $space[0]]);
    if ((int) $connection->query('SELECT COUNT(*) FROM events')->fetchColumn() === 0) {
        $events = [
            ['2026-06-15', '09:00', 'Workshop', 'Build Your Focus System', 'A practical morning session for creating routines that make deep work easier.'],
            ['2026-06-22', '18:00', 'Community Meetup', 'Dumaguete Makers Night', 'Meet the people building thoughtful businesses around the city.'],
            ['2026-06-29', '14:00', 'Creator Session', 'Make Better Content', 'Learn from local creators and see what the studio can do.'],
        ];
        $insert = $connection->prepare('INSERT INTO events (event_date, event_time, category, title, description) VALUES (?, ?, ?, ?, ?)');
        foreach ($events as $event) $insert->execute($event);
    }

    return $connection;
}

function startUserSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function currentUser(): ?array
{
    startUserSession();
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $statement = db()->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
    $statement->execute([$_SESSION['user_id']]);
    $user = $statement->fetch();
    return $user ?: null;
}

function requireUser(): array
{
    $user = currentUser();
    if (!$user) {
        header('Location: ../Login/index.php?next=../Booking/index.php');
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

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

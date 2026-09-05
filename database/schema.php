<?php

declare(strict_types=1);

function columnNames(PDO $db, string $table): array
{
    return array_column($db->query('PRAGMA table_info(' . $table . ')')->fetchAll(), 'name');
}

function addColumnIfMissing(PDO $db, string $table, string $column, string $definition): void
{
    if (!in_array($column, columnNames($db, $table), true)) {
        $db->exec('ALTER TABLE ' . $table . ' ADD COLUMN ' . $column . ' ' . $definition);
    }
}

function ensureDatabaseSchema(PDO $db): void
{
    $db->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT "customer" CHECK (role IN ("customer", "staff", "admin")),
        phone TEXT NOT NULL DEFAULT "",
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
    addColumnIfMissing($db, 'users', 'role', 'TEXT NOT NULL DEFAULT "customer"');
    addColumnIfMissing($db, 'users', 'phone', 'TEXT NOT NULL DEFAULT ""');
    addColumnIfMissing($db, 'users', 'updated_at', 'TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP');

    $db->exec('CREATE TABLE IF NOT EXISTS messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sender TEXT NOT NULL,
        email TEXT NOT NULL,
        subject TEXT NOT NULL,
        message TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT "Unread",
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $db->exec('CREATE TABLE IF NOT EXISTS site_settings (
        id INTEGER PRIMARY KEY CHECK (id = 1),
        website_name TEXT NOT NULL DEFAULT "LFT Dumaguete",
        email TEXT NOT NULL DEFAULT "lftdumaguete@gmail.com",
        phone TEXT NOT NULL DEFAULT "+63 9912345678",
        address TEXT NOT NULL DEFAULT "Hibard St. Dumaguete City, Negros Oriental",
        facebook TEXT NOT NULL DEFAULT "",
        instagram TEXT NOT NULL DEFAULT "",
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
    $db->exec('INSERT OR IGNORE INTO site_settings (id) VALUES (1)');

    $db->exec('CREATE TABLE IF NOT EXISTS bookings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        booking_type TEXT NOT NULL CHECK (booking_type IN ("booking", "walk-in")),
        space TEXT NOT NULL,
        visit_date TEXT NOT NULL,
        visit_time TEXT NOT NULL,
        duration_minutes INTEGER NOT NULL DEFAULT 60,
        guest_count INTEGER NOT NULL DEFAULT 1,
        notes TEXT,
        status TEXT NOT NULL DEFAULT "Pending",
        checked_in_at TEXT,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )');
    addColumnIfMissing($db, 'bookings', 'duration_minutes', 'INTEGER NOT NULL DEFAULT 60');
    addColumnIfMissing($db, 'bookings', 'guest_count', 'INTEGER NOT NULL DEFAULT 1');
    addColumnIfMissing($db, 'bookings', 'checked_in_at', 'TEXT');
    addColumnIfMissing($db, 'bookings', 'updated_at', 'TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP');
    $db->exec('CREATE INDEX IF NOT EXISTS idx_bookings_schedule ON bookings(space, visit_date, visit_time, status)');
    $db->exec('CREATE INDEX IF NOT EXISTS idx_bookings_user ON bookings(user_id, visit_date)');

    $db->exec('CREATE TABLE IF NOT EXISTS spaces (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        category TEXT NOT NULL,
        description TEXT NOT NULL,
        rates TEXT NOT NULL,
        seats INTEGER NOT NULL DEFAULT 1,
        image TEXT NOT NULL DEFAULT "common.png",
        active INTEGER NOT NULL DEFAULT 1,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
    addColumnIfMissing($db, 'spaces', 'seats', 'INTEGER NOT NULL DEFAULT 1');

    $db->exec('CREATE TABLE IF NOT EXISTS memberships (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        label TEXT NOT NULL,
        name TEXT NOT NULL,
        price TEXT NOT NULL,
        period TEXT NOT NULL,
        description TEXT NOT NULL,
        features TEXT NOT NULL DEFAULT "",
        featured INTEGER NOT NULL DEFAULT 0,
        active INTEGER NOT NULL DEFAULT 1,
        status TEXT NOT NULL DEFAULT "Active",
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
    addColumnIfMissing($db, 'memberships', 'features', 'TEXT NOT NULL DEFAULT ""');
    addColumnIfMissing($db, 'memberships', 'status', 'TEXT NOT NULL DEFAULT "Active"');

    $db->exec('CREATE TABLE IF NOT EXISTS amenities (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        icon TEXT NOT NULL,
        name TEXT NOT NULL,
        description TEXT NOT NULL,
        active INTEGER NOT NULL DEFAULT 1,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $db->exec('CREATE TABLE IF NOT EXISTS events (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        event_date TEXT NOT NULL,
        event_time TEXT NOT NULL,
        category TEXT NOT NULL,
        title TEXT NOT NULL,
        description TEXT NOT NULL,
        image TEXT NOT NULL DEFAULT "conference.png",
        location TEXT NOT NULL DEFAULT "LFT Dumaguete",
        status TEXT NOT NULL DEFAULT "Active",
        active INTEGER NOT NULL DEFAULT 1
    )');
    addColumnIfMissing($db, 'events', 'image', 'TEXT NOT NULL DEFAULT "conference.png"');
    addColumnIfMissing($db, 'events', 'location', 'TEXT NOT NULL DEFAULT "LFT Dumaguete"');
    addColumnIfMissing($db, 'events', 'status', 'TEXT NOT NULL DEFAULT "Active"');

    $spaces = [
        ['LFT Commons','Flexible desks','A bright, social workspace for freelancers and remote teams.','₱199 / day',30,'common.png'],
        ['Podcast Studio','Creator studio','A ready-to-use recording room for conversations and content.','₱650 / hour',4,'podcast.png'],
        ['The Club','Private workspace','A premium room with full access for teams who need room to grow.','₱25,000 / month',8,'the club.png'],
        ['Conference Room','Meetings','A professional setting for meetings and workshops.','₱850 / hour',12,'conference.png'],
        ['LFT Reserve','Private room','A private room reserved by the hour for focused work and calls.','₱240 / hour',4,'common.png'],
        ['Learning Studio','Learning and workshops','A welcoming room for classes, study sessions, and workshops.',"₱199 / hour\n₱548 / 3 hours",20,'common.png'],
        ['Focus Suite','Deep work','A quiet suite for uninterrupted focus and productivity.',"₱149 / hour\n₱399 / 3 hours",1,'about.png'],
        ['Team Suite','Team workspace','A collaborative room for meetings and growing teams.',"₱450 / hour\n₱1,600 / half day (4 hours)\n₱2,800 / day",10,'conference.png'],
        ['Private Office','Private workspace','A private professional office for focused work and calls.',"₱1,250 / hour\n₱8,999 / day",4,'the club.png'],
        ['Content Studio','Content creation','A ready-to-use studio for podcasts, presentations, and content.',"₱650 / hour\n₱3,999 / day",6,'podcast.png'],
    ];
    $findSpace = $db->prepare('SELECT COUNT(*) FROM spaces WHERE name = ?');
    $insertSpace = $db->prepare('INSERT INTO spaces (name, category, description, rates, seats, image) VALUES (?, ?, ?, ?, ?, ?)');
    $updateSeats = $db->prepare('UPDATE spaces SET seats = ? WHERE name = ?');
    foreach ($spaces as $space) {
        $findSpace->execute([$space[0]]);
        if ((int)$findSpace->fetchColumn() === 0) $insertSpace->execute($space);
        $updateSeats->execute([$space[4], $space[0]]);
    }

    if ((int)$db->query('SELECT COUNT(*) FROM memberships')->fetchColumn() === 0) {
        $insert = $db->prepare('INSERT INTO memberships (label,name,price,period,description,features,featured) VALUES (?,?,?,?,?,?,?)');
        foreach ([
            ['DAY PASS','Commons Day','₱199','day','One full day of access to our shared workspace, Wi-Fi, and coffee.',"Shared workspace\nWi-Fi\nCoffee",0],
            ['MOST FLEXIBLE','Monthly Commons','₱3,500','month','Your regular desk away from home, plus community events and member rates.',"Regular desk\nCommunity events\nMember rates",1],
            ['PRIVATE ACCESS','The Club','₱25,000','month','A premium private room and full access for focused teams and professionals.',"Private room\nFull access\nTeam workspace",0],
        ] as $row) $insert->execute($row);
    }

    if ((int)$db->query('SELECT COUNT(*) FROM amenities')->fetchColumn() === 0) {
        $insert = $db->prepare('INSERT INTO amenities (icon,name,description) VALUES (?,?,?)');
        foreach ([
            ['fa-solid fa-wifi','Fast Wi-Fi','Reliable connectivity for calls, collaboration, and deep work.'],
            ['fa-solid fa-mug-hot','Coffee corner','Good coffee and easy refills to keep your ideas in motion.'],
            ['fa-solid fa-door-open','Meeting rooms','Private, professional spaces when your work needs a little more focus.'],
            ['fa-solid fa-calendar-days','Community events','Workshops and gatherings that turn useful connections into momentum.'],
            ['fa-solid fa-print','Work essentials','Printing, power, comfortable seating, and the tools you reach for every day.'],
            ['fa-solid fa-location-dot','Central Dumaguete','Find us on Hibard St., close to the city energy and everyday essentials.'],
        ] as $row) $insert->execute($row);
    }
}

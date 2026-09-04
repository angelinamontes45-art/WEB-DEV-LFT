<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../Includes/db.php';

if ($argc !== 4 || !in_array($argv[1], ['staff', 'admin'], true)) {
    fwrite(STDERR, "Usage: php scripts/create-role-user.php <staff|admin> <email> <password>\n");
    exit(1);
}

[$script, $role, $email, $password] = $argv;
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
    fwrite(STDERR, "Use a valid email and a password of at least 8 characters.\n");
    exit(1);
}

$name = $role === 'admin' ? 'LFT Administrator' : 'LFT Staff';
$statement = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
$statement->execute([$name, strtolower($email), password_hash($password, PASSWORD_DEFAULT), $role]);
fwrite(STDOUT, ucfirst($role) . " account created.\n");

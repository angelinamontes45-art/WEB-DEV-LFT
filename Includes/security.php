<?php

declare(strict_types=1);

const APP_TIMEZONE = 'Asia/Manila';
date_default_timezone_set(APP_TIMEZONE);

function startUserSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}

function csrfToken(): string
{
    startUserSession();
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

function verifyCsrf(): void
{
    startUserSession();
    $provided = $_POST['csrf_token'] ?? '';
    $expected = $_SESSION['csrf_token'] ?? '';
    if (!is_string($provided) || !is_string($expected) || $expected === '' || !hash_equals($expected, $provided)) {
        http_response_code(403);
        exit('Security validation failed. Refresh the page and try again.');
    }
}

function requirePostWithCsrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Method not allowed.');
    }
    verifyCsrf();
}

function safeNextPath(string $next, string $fallback = '../Dashboard/index.php'): string
{
    $next = trim($next);
    if ($next === '' || str_contains($next, "\r") || str_contains($next, "\n")) return $fallback;
    $parts = parse_url($next);
    if ($parts === false || isset($parts['scheme']) || isset($parts['host']) || str_starts_with($next, '//')) return $fallback;
    return $next;
}

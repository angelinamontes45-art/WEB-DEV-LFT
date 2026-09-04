<?php
require_once __DIR__ . '/../Includes/db.php';
requireRole(['admin']);

function adminRedirect(string $section, string $query = ''): never
{
    header('Location: ../index.php' . ($query ? '?' . $query : '') . '#' . $section);
    exit;
}

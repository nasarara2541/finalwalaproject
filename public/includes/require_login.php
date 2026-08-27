<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function appBasePrefix() {
    $publicDir = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    $entryDir  = str_replace('\\', '/', realpath(dirname($_SERVER['SCRIPT_FILENAME'])));
    if ($entryDir === $publicDir) return '';
    $rel = trim(substr($entryDir, strlen($publicDir)), '/');
    return str_repeat('../', substr_count($rel, '/') + 1);
}

if (empty($_SESSION['active_db'])) {
    header('Location: ' . appBasePrefix() . 'login.php');
    exit;
}

if (empty($_SESSION['emp_user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? 'pos.php';
    header('Location: ' . appBasePrefix() . 'user_login.php');
    exit;
}

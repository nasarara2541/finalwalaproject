<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['active_db'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['emp_user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? 'pos.php';
    header('Location: user_login.php');
    exit;
}

<?php
require_once __DIR__ . '/require_login.php';

if (empty($_SESSION['emp_is_admin'])) {
    header('Location: ' . appBasePrefix() . 'pos.php');
    exit;
}

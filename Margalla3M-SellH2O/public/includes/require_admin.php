<?php
// Not used by any screen yet — ready for whenever the admin-only screens
// are built. Include this instead of require_login.php at the top of any
// screen that only the Administrator group (Local_admin = 'A') may open.
require_once __DIR__ . '/require_login.php';

if (empty($_SESSION['emp_is_admin'])) {
    header('Location: pos.php');
    exit;
}

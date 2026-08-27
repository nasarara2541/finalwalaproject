<?php
// Anoosha's original auth_guard.php checked $_SESSION['user_id'] and
// redirected to her own login.php -- a session key and a file this project
// never sets/has, so anyone already logged into this app would have been
// bounced to a 404. Reuses this app's real, depth-aware login gate instead,
// so a cashier/admin already signed in here passes straight through.
//
// All 3 of her screens (Purchase Report, Short Items, Search Items) fall
// under the Inventory role's remit in Zeeshan's matrix -- checked centrally
// here since every one of her screens includes this same file.
require_once __DIR__ . '/../../includes/access.php';
requireAccess('inventory');

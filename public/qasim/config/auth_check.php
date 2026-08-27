<?php
// Qasim's original checked $_SESSION['logged_in'] and redirected to a
// login.php this app doesn't have at this path -- reuses this app's real,
// depth-aware login gate instead, so a user already signed in here passes
// straight through. (His admin-only checks elsewhere use $_SESSION['user_role']
// !== 'Admin' -- those are adapted individually to this app's real
// $_SESSION['emp_is_admin'] where they occur, not centralized here, since
// not every one of his screens/APIs is admin-only.)
require_once __DIR__ . '/../../includes/require_login.php';

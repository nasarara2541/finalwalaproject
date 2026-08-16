<?php
if (session_status() === PHP_SESSION_NONE) session_start();
unset($_SESSION['emp_user_id'], $_SESSION['emp_user_name'], $_SESSION['emp_is_admin'], $_SESSION['emp_group_name']);
header('Location: user_login.php');
exit;

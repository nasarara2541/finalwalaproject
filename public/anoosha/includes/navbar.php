<?php
$current = basename($_SERVER['PHP_SELF']);
$anoosha_screen_names = [
    'purchase_report.php' => 'Purchase Report',
    'short_items.php'     => 'Short Items',
    'search_items.php'    => 'Search Items',
];
$SCREEN_NAME = $anoosha_screen_names[$current] ?? 'Purchase Report';
$SCREEN_ICON = 'clipboard-list';
require __DIR__ . '/../../includes/navbar.php';

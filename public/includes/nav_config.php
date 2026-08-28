<?php
// Single source of truth for the categorized menubar (navbar.php renders it,
// nothing else should hardcode this list). 'file' is always relative to
// public/. 'view' is only set for pos.php's own internal tabs (Sale/
// Transactions/Suppliers/Booking/Reports) which switch client-side via
// switchView() when you're already on pos.php, and via ?view= when you're
// navigating to pos.php from somewhere else -- see navbar.php.
// 'bucket' is checked with canAccess() from access.php.
//
// The "Suppliers" item below was a real, pre-existing gap: pos.php computed
// $canSuppliers and built the whole Suppliers tab/view, but no nav link to
// it ever existed anywhere in the app -- it was unreachable. Fixed here by
// simply giving it a menu entry like everything else.
function buildNavMenu(): array {
    return [
        'sale' => [
            'label' => 'Sale',
            'items' => [
                ['label' => 'Sale',         'file' => 'pos.php', 'view' => 'pos',          'bucket' => 'sale'],
                ['label' => 'Transactions', 'file' => 'pos.php', 'view' => 'transactions', 'bucket' => 'sale'],
                ['label' => 'Reports',      'file' => 'pos.php', 'view' => 'reports',      'bucket' => 'sale'],
                ['label' => 'Sale Reports', 'file' => 'sale_reports.php', 'bucket' => 'daily_sale'],
                ['label' => 'Sale Items',   'file' => 'sale_items.php',   'bucket' => 'daily_sale'],
            ],
        ],
        'booking' => [
            'label' => 'Booking',
            'items' => [
                ['label' => 'Booking', 'file' => 'pos.php', 'view' => 'booking', 'bucket' => 'booking'],
            ],
        ],
        'stock' => [
            'label' => 'Stock',
            'items' => [
                ['label' => 'Stock Receiving',         'file' => 'stock_receiving.php', 'bucket' => 'inventory'],
                ['label' => 'Stock Search',             'file' => 'stock_search.php',    'bucket' => 'inventory'],
                ['label' => 'Manufacture',              'file' => 'manufacture.php',     'bucket' => 'inventory'],
                ['label' => 'Suppliers',                'file' => 'pos.php', 'view' => 'suppliers', 'bucket' => 'suppliers'],
                ['label' => 'Purchase Report',          'file' => 'anoosha/purchase_report.php', 'bucket' => 'inventory'],
                ['label' => 'Short Items',               'file' => 'anoosha/short_items.php',    'bucket' => 'inventory'],
                ['label' => 'Search Items',              'file' => 'anoosha/search_items.php',   'bucket' => 'inventory'],
                ['label' => 'Group Wise Stock Search',   'file' => 'zeeshan/stock_search.php',   'bucket' => 'inventory'],
                ['label' => 'Dead Items',                'file' => 'zeeshan/dead_items.php',     'bucket' => 'inventory'],
                ['label' => 'Stock In Hand',             'file' => 'rafia/stock_in_hand.php',    'bucket' => 'inventory'],
                ['label' => 'Purchase & Returns',        'file' => 'rafia/purchase_return_summary.php', 'bucket' => 'inventory'],
                ['label' => 'Narcotics Register',        'file' => 'rafia/narcotics.php',        'bucket' => 'inventory'],
            ],
        ],
        'purchase_order' => [
            'label' => 'Purchase Order',
            'items' => [
                ['label' => 'Purchase Order', 'file' => 'qasim/public/purchase_order.php', 'bucket' => 'inventory'],
            ],
        ],
        'ai' => [
            'label' => 'AI',
            'items' => [
                ['label' => 'AI Insights', 'file' => 'ai_insights.php', 'bucket' => 'admin_area'],
            ],
        ],
        'admin' => [
            'label' => 'Admin',
            'items' => [
                ['label' => 'Dashboard',            'file' => 'admin_dashboard.php',           'bucket' => 'admin_area'],
                ['label' => 'Profit Reports',       'file' => 'reports/admin_reports.php',     'bucket' => 'admin_area'],
                ['label' => 'Item Details',         'file' => 'item_details.php',              'bucket' => 'admin_area'],
                ['label' => 'Sales Report (Qasim)', 'file' => 'qasim/public/sales_report.php', 'bucket' => 'admin_area'],
                ['label' => 'Manage Users',         'file' => 'zeeshan/manage_users.php',      'bucket' => 'admin'],
            ],
        ],
    ];
}

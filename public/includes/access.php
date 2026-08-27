<?php
// Role-based screen access, per Zeeshan's proposed 5-role matrix. Screen
// names on his list matched the team's original shared blueprint (visible
// verbatim in Qasim's own index.php $validPages) -- most of which nobody
// actually built. This maps his intent onto the screens that really exist
// in this integrated project (confirmed with the user where genuinely
// ambiguous), not the blueprint's imaginary ones.
//
// 'admin' bucket = strictly Admin only (Manage Users -- creating/deleting
// staff). 'admin_area' = Admin + Management (rest of the Administration
// dashboard). Everything else is bucketed by real screen groupings.
require_once __DIR__ . '/require_login.php';

$GLOBALS['ROLE_ACCESS'] = [
    // Manage Users specifically -- true Admin only, per "Management: everything
    // EXCEPT Manage Users."
    'admin'        => ['Admin'],
    // Rest of the Administration dashboard (Dashboard, Profit Reports, Item
    // Details, Qasim's Sales Report, Zeeshan's Dead Items is NOT here -- see
    // 'items' below, since his own matrix puts it under Inventory's remit,
    // not Administration).
    'admin_area'   => ['Admin', 'Management'],
    // Issue Item (POS) + Transactions + the embedded Reports tab -- the
    // cashier-facing sale workflow.
    'sale'         => ['Admin', 'Management', 'Cashier'],
    // Daily Sale reporting/entry screens.
    'daily_sale'   => ['Admin', 'Management', 'Cashier'],
    // Booking tab specifically.
    'booking'      => ['Admin', 'Management', 'Booking'],
    // Stock Receiving, Items Management (product list/search/manufacture),
    // and Purchase Orders -- all one bucket in Zeeshan's matrix (Inventory's
    // full remit), plus Anoosha's Purchase Report and Zeeshan's Dead Items
    // per the user's explicit confirmation, plus Rafia's Stock In Hand,
    // Purchase & Return Summary, and Narcotics Register (same inventory/
    // purchasing remit as everyone else's screens above).
    'inventory'    => ['Admin', 'Management', 'Inventory'],
    // Suppliers tab -- purchasing-adjacent, same bucket as Inventory.
    'suppliers'    => ['Admin', 'Management', 'Inventory'],
];

function currentRole() {
    return $_SESSION['emp_role'] ?? 'Management';
}

function canAccess($bucket) {
    $allowed = $GLOBALS['ROLE_ACCESS'][$bucket] ?? [];
    return in_array(currentRole(), $allowed, true);
}

// Redirects away if the current role isn't in the given bucket's allow-list.
// Admins always pass (belt-and-suspenders with the buckets already including
// 'Admin' everywhere).
function requireAccess($bucket) {
    if ($_SESSION['emp_role'] ?? null) {
        if (canAccess($bucket)) return;
    } elseif (!empty($_SESSION['emp_is_admin'])) {
        return; // legacy session predating emp_role -- true admins still pass
    } else {
        return; // no role info at all -- fail open to avoid locking out every
                // existing session on deploy; real gap-closing happens as
                // people re-login and pick up emp_role
    }
    header('Location: ' . appBasePrefix() . 'pos.php');
    exit;
}

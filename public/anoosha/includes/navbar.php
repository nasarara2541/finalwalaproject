<?php $current = basename($_SERVER['PHP_SELF']); ?>
<div class="win-menubar">
    <span class="win-menu-item" onclick="window.location='../pos.php'">&#x2190; Back to Sale</span>
    <span class="win-menu-item<?php echo $current === 'purchase_report.php' ? ' nav-active' : ''; ?>" onclick="window.location='purchase_report.php'">Purchase Report</span>
    <span class="win-menu-item<?php echo $current === 'short_items.php' ? ' nav-active' : ''; ?>" onclick="window.location='short_items.php'">Short Items</span>
    <span class="win-menu-item<?php echo $current === 'search_items.php' ? ' nav-active' : ''; ?>" onclick="window.location='search_items.php'">Search Items</span>
    <?php if (!empty($_SESSION['emp_is_admin'])): ?>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='../admin_users.php'">&#x1F464; Manage Users</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='../admin_dashboard.php'">&#x1F4CA; Dashboard</span>
    <span class="win-menu-item" style="color:#5b3a8a;font-weight:bold;" onclick="window.location='../reports/admin_reports.php'">&#x1F4C8; Profit Reports</span>
    <?php endif; ?>
    <span style="flex:1"></span>
    <span class="win-menu-item" style="color:#555;">Database: <b><?php echo htmlspecialchars($_SESSION['active_db_label'] ?? 'Water Distribution'); ?></b></span>
    <span class="win-menu-item" onclick="window.location='../login.php'" title="Pick a different database">&#x1F504; Switch Database</span>
    <span class="win-menu-item" style="color:#555;">User: <b><?php echo htmlspecialchars($_SESSION['emp_user_name'] ?? '—'); ?></b></span>
    <span class="win-menu-item" onclick="window.location='../logout.php'" title="Sign out" style="color:darkred;">&#x1F6AA; Logout</span>
</div>

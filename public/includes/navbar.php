<?php
// Shared header (titlebar) + categorized, keyboard-navigable menubar for
// every screen in the app. A screen sets $SCREEN_NAME (and optionally
// $SCREEN_ICON, a Font Awesome 6 icon name without the 'fa-' prefix, e.g.
// 'droplet') before requiring this file where its old titlebar/menubar
// block used to be. pos.php additionally sets $NAV_CURRENT_VIEW so the
// right item highlights and view-switch clicks stay client-side instead
// of reloading.
require_once __DIR__ . '/access.php';
require_once __DIR__ . '/nav_config.php';

$SCREEN_NAME = $SCREEN_NAME ?? 'AISellProduct';
$SCREEN_ICON = $SCREEN_ICON ?? 'droplet';
$NAV_CURRENT_VIEW = $NAV_CURRENT_VIEW ?? null;
$NAV_PREFIX = appBasePrefix();

// Auto-detect which screen file is currently running, e.g. 'pos.php' or
// 'zeeshan/dead_items.php' -- must match the 'file' values in nav_config.php.
$__navPublicDir   = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$__navScriptFile  = $_SERVER['SCRIPT_FILENAME'] ?? '';
$__navScriptReal  = $__navScriptFile ? str_replace('\\', '/', realpath($__navScriptFile)) : '';
$NAV_CURRENT_FILE = ($__navScriptReal && strpos($__navScriptReal, $__navPublicDir) === 0)
    ? ltrim(substr($__navScriptReal, strlen($__navPublicDir)), '/')
    : '';

function navItemIsCurrent(array $item): bool {
    global $NAV_CURRENT_FILE, $NAV_CURRENT_VIEW;
    if ($item['file'] !== $NAV_CURRENT_FILE) return false;
    if (isset($item['view'])) return $item['view'] === $NAV_CURRENT_VIEW;
    return true;
}

function navItemHref(array $item): string {
    global $NAV_PREFIX;
    $href = $NAV_PREFIX . $item['file'];
    if (isset($item['view'])) $href .= '?view=' . urlencode($item['view']);
    return $href;
}

function navItemOnclick(array $item): string {
    global $NAV_CURRENT_FILE;
    if (isset($item['view']) && $item['file'] === $NAV_CURRENT_FILE) {
        // Already on this screen -- switch the tab client-side (pos.php
        // defines switchView()) instead of a full reload.
        return "event.stopPropagation(); switchView('" . htmlspecialchars($item['view'], ENT_QUOTES) . "'); navCloseAll(); return false;";
    }
    return "event.stopPropagation(); window.location='" . htmlspecialchars(navItemHref($item), ENT_QUOTES) . "';";
}

// Build the menu the current role is actually allowed to see. A category
// with zero visible items is dropped entirely.
$navMenu = [];
foreach (buildNavMenu() as $key => $cat) {
    $visibleItems = array_values(array_filter($cat['items'], function ($item) {
        return canAccess($item['bucket']);
    }));
    if ($visibleItems) {
        $navMenu[$key] = ['label' => $cat['label'], 'items' => $visibleItems];
    }
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.nav-category { position: relative; }
.nav-icon-btn {
    background: transparent; border-color: transparent; color: white; cursor: pointer;
    font-size: 13px; padding: 3px 7px;
}
.nav-icon-btn:hover { background: #8b0000 !important; border-color: transparent !important; }
.win-dropdown-menu {
    display: none; position: absolute; top: 100%; left: 0; z-index: 50; min-width: 190px;
    background: #d4d0c8; border: 1px solid; border-color: #ffffff #808080 #808080 #ffffff;
    box-shadow: 2px 2px 4px rgba(0,0,0,0.3); padding: 2px;
}
.win-dropdown-item {
    border: none !important; display: block; text-align: left; white-space: nowrap;
    /* Explicit, not inherited -- the category button above a dropdown often
       carries color:white !important (.nav-active/.nav-parent-active, for its
       own navy/purple highlight), and color is an inherited property, so
       without this every non-active item in the dropdown silently inherited
       that white text against the dropdown's light grey background. */
    color: #000;
}
.nav-active { background: #0a246a !important; color: white !important; }
.nav-parent-active { background: #5b3a8a !important; color: white !important; }
.nav-category:focus, .win-dropdown-item:focus, .nav-user-toggle:focus {
    outline: 2px solid #ffff99; outline-offset: -2px;
}
.nav-usermenu { position: relative; }
</style>

<div class="win-titlebar">
    <span><i class="fa-solid fa-<?php echo htmlspecialchars($SCREEN_ICON, ENT_QUOTES); ?>"></i> AISellProduct &mdash; <span id="nav-screen-name"><?php echo htmlspecialchars($SCREEN_NAME); ?></span></span>
    <div style="display:flex;align-items:center;gap:12px;">
        <span id="live-clock" style="font-weight:normal;font-size:11px;"></span>
        <div class="nav-usermenu">
            <span class="win-menu-item nav-user-toggle" tabindex="0" role="button" aria-haspopup="true" aria-expanded="false"
                  style="color:white;background:transparent;border-color:transparent;"
                  onclick="navToggleUserMenu(event)" onkeydown="navUserMenuKeydown(event)">
                <i class="fa-solid fa-user"></i>
                <b><?php echo htmlspecialchars($_SESSION['emp_user_name'] ?? '—'); ?></b>
                <i class="fa-solid fa-caret-down"></i>
            </span>
            <div class="win-dropdown-menu" id="nav-user-dropdown" role="menu" style="left:auto;right:0;">
                <span class="win-menu-item win-dropdown-item" style="color:#555;cursor:default;">Database: <b><?php echo htmlspecialchars($_SESSION['active_db_label'] ?? 'Water Distribution'); ?></b></span>
                <span class="win-menu-item win-dropdown-item" role="menuitem" tabindex="-1" onclick="window.location='<?php echo htmlspecialchars($NAV_PREFIX, ENT_QUOTES); ?>login.php'" title="Pick a different database"><i class="fa-solid fa-right-left"></i> Switch Database</span>
            </div>
        </div>
        <span class="nav-icon-btn" role="button" tabindex="0" title="Log out"
              onclick="window.location='<?php echo htmlspecialchars($NAV_PREFIX, ENT_QUOTES); ?>logout.php'"
              onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">
            <i class="fa-solid fa-right-from-bracket"></i>
        </span>
    </div>
</div>

<div class="win-menubar" id="nav-menubar" role="menubar">
    <?php $__navFirst = true; foreach ($navMenu as $key => $cat):
        $isActiveCat = false;
        foreach ($cat['items'] as $item) { if (navItemIsCurrent($item)) { $isActiveCat = true; break; } }
    ?>
    <span class="win-menu-item nav-category<?php echo $isActiveCat ? ' nav-parent-active' : ''; ?>"
          id="navcat-<?php echo htmlspecialchars($key); ?>" role="menuitem" tabindex="<?php echo $__navFirst ? '0' : '-1'; ?>"
          aria-haspopup="true" aria-expanded="false"
          onclick="navToggleCategory(event,'<?php echo htmlspecialchars($key, ENT_QUOTES); ?>')">
        <?php echo htmlspecialchars($cat['label']); ?> <i class="fa-solid fa-caret-down"></i>
        <div class="win-dropdown-menu" role="menu">
            <?php foreach ($cat['items'] as $item): $current = navItemIsCurrent($item); ?>
            <span class="win-menu-item win-dropdown-item<?php echo $current ? ' nav-active' : ''; ?>" role="menuitem" tabindex="-1"
                  onclick="<?php echo navItemOnclick($item); ?>"><?php echo htmlspecialchars($item['label']); ?></span>
            <?php endforeach; ?>
        </div>
    </span>
    <?php $__navFirst = false; endforeach; ?>
    <span style="flex:1"></span>
</div>

<script>
(function(){
    function qsa(sel, el){ return Array.prototype.slice.call((el||document).querySelectorAll(sel)); }
    var menubar = document.getElementById('nav-menubar');
    var categories = menubar ? qsa('.nav-category', menubar) : [];
    var openCat = null;

    function closeAll(){
        categories.forEach(function(c){
            c.setAttribute('aria-expanded','false');
            var d = c.querySelector('.win-dropdown-menu');
            if (d) d.style.display = 'none';
        });
        openCat = null;
    }
    window.navCloseAll = closeAll;

    function openCategory(cat, focusFirst){
        closeAll();
        if (!cat) return;
        cat.setAttribute('aria-expanded','true');
        var d = cat.querySelector('.win-dropdown-menu');
        if (d) {
            d.style.display = 'block';
            openCat = cat;
            if (focusFirst) {
                var items = qsa('.win-dropdown-item[role="menuitem"]', d);
                if (items.length) items[0].focus();
            }
        }
    }

    window.navToggleCategory = function(e, key){
        e.stopPropagation();
        var cat = document.getElementById('navcat-' + key);
        if (openCat === cat) { closeAll(); cat.focus(); }
        else { openCategory(cat, false); }
    };

    function focusCategory(idx){
        if (!categories.length) return null;
        idx = ((idx % categories.length) + categories.length) % categories.length;
        categories.forEach(function(c,i){ c.tabIndex = (i===idx) ? 0 : -1; });
        categories[idx].focus();
        return categories[idx];
    }

    categories.forEach(function(cat){
        cat.addEventListener('keydown', function(e){
            var currentIdx = categories.indexOf(cat);
            if (e.key === 'ArrowRight') {
                e.preventDefault();
                var nc = focusCategory(currentIdx+1);
                if (openCat) openCategory(nc, true);
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                var pc = focusCategory(currentIdx-1);
                if (openCat) openCategory(pc, true);
            } else if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                e.preventDefault();
                openCategory(cat, true);
            } else if (e.key === 'Escape') {
                e.preventDefault();
                closeAll();
            }
        });

        var dropdown = cat.querySelector('.win-dropdown-menu');
        if (!dropdown) return;
        var items = qsa('.win-dropdown-item[role="menuitem"]', dropdown);
        items.forEach(function(item, iIdx){
            item.addEventListener('keydown', function(e){
                var currentIdx = categories.indexOf(cat);
                // Every branch here stops propagation -- the dropdown is
                // nested inside the category span (for CSS positioning), so
                // an unstopped keydown would bubble up to the category's own
                // handler right after this one runs and immediately undo it
                // (e.g. ArrowDown would re-open the dropdown and reset focus
                // back to the first item instead of advancing).
                if (e.key === 'ArrowDown') {
                    e.preventDefault(); e.stopPropagation();
                    items[(iIdx+1) % items.length].focus();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault(); e.stopPropagation();
                    items[(iIdx-1+items.length) % items.length].focus();
                } else if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault(); e.stopPropagation();
                    item.click();
                } else if (e.key === 'Escape') {
                    e.preventDefault(); e.stopPropagation();
                    closeAll();
                    cat.focus();
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault(); e.stopPropagation();
                    var nc = focusCategory(currentIdx+1);
                    openCategory(nc, true);
                } else if (e.key === 'ArrowLeft') {
                    e.preventDefault(); e.stopPropagation();
                    var pc = focusCategory(currentIdx-1);
                    openCategory(pc, true);
                }
            });
        });
    });

    document.addEventListener('click', function(e){
        if (!menubar || !e.target.closest('#nav-menubar')) closeAll();
    });

    // User menu -- separate, simple click/Enter toggle.
    window.navToggleUserMenu = function(e){
        e.stopPropagation();
        var dd = document.getElementById('nav-user-dropdown');
        if (!dd) return;
        dd.style.display = (dd.style.display === 'block') ? 'none' : 'block';
    };
    window.navUserMenuKeydown = function(e){
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); window.navToggleUserMenu(e); }
        else if (e.key === 'Escape') { var dd = document.getElementById('nav-user-dropdown'); if (dd) dd.style.display = 'none'; }
    };
    document.addEventListener('click', function(e){
        if (!e.target.closest('.nav-usermenu')) {
            var dd = document.getElementById('nav-user-dropdown');
            if (dd) dd.style.display = 'none';
        }
    });

    // Live clock -- was copy-pasted per screen before, now lives here once.
    function navClockTick(){
        var el = document.getElementById('live-clock');
        if (el) el.textContent = new Date().toLocaleString('en-GB');
    }
    navClockTick();
    setInterval(navClockTick, 1000);
})();
</script>

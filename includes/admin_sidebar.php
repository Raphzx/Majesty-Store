<?php
$active_page = isset($active_page) ? $active_page : '';
function admin_active($active, $page) { return $active === $page ? 'active' : ''; }
?>
<aside id="adminSidebar" class="sidebar-admin w-64 flex-shrink-0">
    <div class="sidebar-header flex items-center justify-between">
        <h2 class="text-xl">Admin Panel</h2>
        <div class="flex items-center gap-2">
            <button id="themeToggle" class="theme-toggle" aria-label="Mode Terang/Gelap" aria-pressed="false">
                <i class="fas fa-moon" id="themeToggleIcon"></i>
            </button>
            <button id="sidebarClose" class="admin-menu-close" aria-label="Tutup menu">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <nav class="mt-4">
        <a href="<?php echo url('admin/dashboard'); ?>" class="nav-link <?php echo admin_active($active_page, 'dashboard'); ?>"><i class="fas fa-home w-6"></i>Dashboard</a>
        <a href="<?php echo url('admin/products'); ?>" class="nav-link <?php echo admin_active($active_page, 'products'); ?>"><i class="fas fa-box w-6"></i>Kelola Produk</a>
        <a href="<?php echo url('admin/categories'); ?>" class="nav-link <?php echo admin_active($active_page, 'categories'); ?>"><i class="fas fa-tags w-6"></i>Kategori</a>
        <a href="<?php echo url('admin/transactions'); ?>" class="nav-link <?php echo admin_active($active_page, 'transactions'); ?>"><i class="fas fa-receipt w-6"></i>Transaksi</a>
        <a href="<?php echo url('admin/reports'); ?>" class="nav-link <?php echo admin_active($active_page, 'reports'); ?>"><i class="fas fa-chart-bar w-6"></i>Laporan</a>
        <a href="<?php echo url('admin/logout'); ?>" class="nav-link mt-8 text-red-300 hover:text-red-200"><i class="fas fa-sign-out-alt w-6"></i>Logout</a>
    </nav>
</aside>
<div id="sidebarOverlay" class="admin-overlay"></div>
<script src="<?php echo url('assets/js/theme.js'); ?>"></script>

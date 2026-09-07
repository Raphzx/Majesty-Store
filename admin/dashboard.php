<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ' . url('admin/login'));
    exit();
}

$products = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc();
$categories = $conn->query("SELECT COUNT(*) as total FROM categories")->fetch_assoc();
$transactions = $conn->query("SELECT COUNT(*) as total FROM transactions")->fetch_assoc();
$revenue = $conn->query("SELECT SUM(total) as total FROM transactions")->fetch_assoc();
$customers = $conn->query("SELECT COUNT(DISTINCT customer_email) as total FROM transactions")->fetch_assoc();
$pendingOrders = $conn->query("SELECT COUNT(*) as total FROM transactions WHERE status IN ('Baru','Diproses')")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Toko Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-cream">
    <div class="flex min-h-screen">
        <?php $active_page = 'dashboard'; include '../includes/admin_sidebar.php'; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-10">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-3">
                    <button id="adminBurger" class="admin-burger" aria-label="Buka menu"><i class="fas fa-bars"></i></button>
                    <div>
                        <h1 class="section-title text-2xl sm:text-3xl">Dashboard</h1>
                        <p class="section-subtitle mt-2">Ringkasan aktivitas toko Anda</p>
                    </div>
                </div>
                <div class="text-right hidden sm:block">
                    <p class="text-sm text-gray-500"><?php echo date('d F Y'); ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-10">
                <div class="stat-card animate-slide-up">
                    <div class="flex justify-between items-start mb-3">
                        <div class="stat-label">Total Produk</div>
                        <div class="stat-icon"><i class="fas fa-box"></i></div>
                    </div>
                    <div class="stat-value"><?php echo $products['total']; ?></div>
                </div>
                <div class="stat-card animate-slide-up animate-slide-up-delay-1">
                    <div class="flex justify-between items-start mb-3">
                        <div class="stat-label">Kategori</div>
                        <div class="stat-icon"><i class="fas fa-tags"></i></div>
                    </div>
                    <div class="stat-value"><?php echo $categories['total']; ?></div>
                </div>
                <div class="stat-card animate-slide-up animate-slide-up-delay-2">
                    <div class="flex justify-between items-start mb-3">
                        <div class="stat-label">Total Transaksi</div>
                        <div class="stat-icon"><i class="fas fa-receipt"></i></div>
                    </div>
                    <div class="stat-value"><?php echo $transactions['total']; ?></div>
                </div>
                <div class="stat-card animate-slide-up animate-slide-up-delay-3">
                    <div class="flex justify-between items-start mb-3">
                        <div class="stat-label">Pesanan Proses</div>
                        <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                    </div>
                    <div class="stat-value"><?php echo $pendingOrders['total']; ?></div>
                </div>
                <div class="stat-card animate-slide-up animate-slide-up-delay-4">
                    <div class="flex justify-between items-start mb-3">
                        <div class="stat-label">Total Pendapatan</div>
                        <div class="stat-icon"><i class="fas fa-coins"></i></div>
                    </div>
                    <div class="stat-value text-sm"><?php echo format_rupiah($revenue['total'] ?: 0); ?></div>
                </div>
                <div class="stat-card animate-slide-up animate-slide-up-delay-5">
                    <div class="flex justify-between items-start mb-3">
                        <div class="stat-label">Pelanggan</div>
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                    </div>
                    <div class="stat-value"><?php echo $customers['total']; ?></div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md border border-gold/10 p-6 lg:p-8">
                <h2 class="text-xl font-semibold text-navy mb-4">
                    <i class="fas fa-clock text-gold-dark mr-2"></i>Akses Cepat
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="<?php echo url('admin/products'); ?>" class="btn-premium-outline text-center text-sm py-3">
                        <i class="fas fa-plus mr-2"></i>Tambah Produk
                    </a>
                    <a href="<?php echo url('admin/categories'); ?>" class="btn-navy text-center text-sm py-3">
                        <i class="fas fa-tags mr-2"></i>Kelola Kategori
                    </a>
                    <a href="<?php echo url('admin/transactions'); ?>" class="btn-premium-outline text-center text-sm py-3">
                        <i class="fas fa-list mr-2"></i>Lihat Transaksi
                    </a>
                    <a href="<?php echo url('admin/reports'); ?>" class="btn-navy text-center text-sm py-3">
                        <i class="fas fa-chart-line mr-2"></i>Lihat Laporan
                    </a>
                </div>
            </div>
        </main>
    </div>
    <script src="<?php echo url('assets/js/admin_sidebar.js'); ?>?v=2"></script>
</body>
</html>
<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ' . url('admin/login'));
    exit();
}

$daily = $conn->query("SELECT DATE(date) as tanggal, COUNT(*) as jumlah, SUM(total) as total FROM transactions WHERE DATE(date) >= CURDATE() - INTERVAL 6 DAY GROUP BY DATE(date) ORDER BY tanggal DESC");
$monthly = $conn->query("SELECT MONTH(date) as bulan, YEAR(date) as tahun, COUNT(*) as jumlah, SUM(total) as total FROM transactions GROUP BY MONTH(date), YEAR(date) ORDER BY tahun DESC, bulan DESC LIMIT 12");
$total_revenue = $conn->query("SELECT SUM(total) as total FROM transactions")->fetch_assoc();
$total_transactions = $conn->query("SELECT COUNT(*) as total FROM transactions")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-cream">
    <div class="flex min-h-screen">
        <?php $active_page = 'reports'; include '../includes/admin_sidebar.php'; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-10">
            <div class="flex items-center gap-3 mb-8">
                <button id="adminBurger" class="admin-burger" aria-label="Buka menu"><i class="fas fa-bars"></i></button>
                <div>
                    <h1 class="section-title text-2xl sm:text-3xl">Laporan Penjualan</h1>
                    <p class="section-subtitle mt-2">Analisis data penjualan toko Anda</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-10">
                <div class="stat-card">
                    <div class="flex justify-between items-start mb-3">
                        <div class="stat-label">Total Pendapatan</div>
                        <div class="stat-icon"><i class="fas fa-coins"></i></div>
                    </div>
                    <div class="stat-value">Rp <?php echo number_format($total_revenue['total'] ?: 0, 0, ',', '.'); ?></div>
                </div>
                <div class="stat-card">
                    <div class="flex justify-between items-start mb-3">
                        <div class="stat-label">Total Transaksi</div>
                        <div class="stat-icon"><i class="fas fa-receipt"></i></div>
                    </div>
                    <div class="stat-value"><?php echo $total_transactions['total']; ?></div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white rounded-xl shadow-md border border-gold/10 p-6 lg:p-8">
                    <h2 class="text-xl font-semibold text-navy mb-6 flex items-center">
                        <i class="fas fa-calendar-week text-gold-dark mr-3"></i>
                        7 Hari Terakhir
                    </h2>
                    <table class="table-premium w-full">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Transaksi</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $daily->fetch_assoc()): ?>
                            <tr>
                                <td class="font-medium text-navy"><?php echo date('d M Y', strtotime($row['tanggal'])); ?></td>
                                <td><span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-medium"><?php echo $row['jumlah']; ?> pesanan</span></td>
                                <td class="text-right font-semibold text-gold-dark">Rp <?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="bg-white rounded-xl shadow-md border border-gold/10 p-6 lg:p-8">
                    <h2 class="text-xl font-semibold text-navy mb-6 flex items-center">
                        <i class="fas fa-calendar-alt text-gold-dark mr-3"></i>
                        Penjualan Bulanan
                    </h2>
                    <table class="table-premium w-full">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th>Transaksi</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            while($row = $monthly->fetch_assoc()): 
                            ?>
                            <tr>
                                <td class="font-medium text-navy"><?php echo $months[(int)$row['bulan']] . ' ' . $row['tahun']; ?></td>
                                <td><span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-medium"><?php echo $row['jumlah']; ?> pesanan</span></td>
                                <td class="text-right font-semibold text-gold-dark">Rp <?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <script src="<?php echo url('assets/js/admin_sidebar.js'); ?>?v=2"></script>
</body>
</html>

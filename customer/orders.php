<?php
session_start();
include '../includes/db.php';

if (empty($_SESSION['customer_id'])) {
    header('Location: ' . url('customer/login'));
    exit();
}

$customer_id = (int)$_SESSION['customer_id'];

$orders = [];
$stmt = $conn->prepare("SELECT id, total, status, date FROM transactions WHERE customer_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $row['items'] = [];
    $stmt2 = $conn->prepare("SELECT d.qty, d.price, p.name FROM transaction_details d LEFT JOIN products p ON p.id = d.product_id WHERE d.transaction_id = ?");
    $stmt2->bind_param("i", $row['id']);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    while ($it = $res2->fetch_assoc()) {
        $row['items'][] = $it;
    }
    $orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Majesty Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="container mx-auto px-4 lg:px-8 py-12">
        <div class="text-center mb-10">
            <h1 class="section-title text-3xl lg:text-4xl">Pesanan Saya</h1>
            <p class="section-subtitle mt-4">Riwayat dan status pesanan akun <span class="text-gold-dark font-semibold"><?php echo e($_SESSION['customer_name']); ?></span></p>
        </div>

        <?php if (empty($orders)): ?>
            <div class="max-w-lg mx-auto text-center py-16">
                <div class="w-20 h-20 mx-auto rounded-full bg-gray-100 flex items-center justify-center mb-6">
                    <i class="fas fa-box-open text-3xl text-gray-400"></i>
                </div>
                <h2 class="text-xl font-semibold text-gray-500 mb-3">Belum Ada Pesanan</h2>
                <p class="text-gray-400 mb-8">Anda belum memiliki riwayat pesanan.</p>
                <a href="<?php echo url('customer/products'); ?>" class="btn-premium">
                    <i class="fas fa-store mr-2"></i> Mulai Belanja
                </a>
            </div>
        <?php else: ?>
            <div class="max-w-3xl mx-auto">
                <?php foreach ($orders as $order): ?>
                    <div class="bg-white rounded-xl shadow-md border border-gold/10 mb-6 overflow-hidden">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-6 py-4 border-b border-gray-100">
                            <div class="font-mono font-bold text-navy text-lg">#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></div>
                            <div class="text-sm text-gray-500">
                                <i class="fas fa-calendar-day mr-1 text-gold-dark"></i><?php echo date('d M Y H:i', strtotime($order['date'])); ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 px-6 py-3 bg-cream/60 border-b border-gray-100 flex-wrap">
                            <span class="text-sm font-semibold text-navy"><i class="fas fa-circle-info mr-1 text-gold-dark"></i>Status Pesanan:</span>
                            <span class="status-badge status-<?php echo e($order['status']); ?>"><?php echo e($order['status']); ?></span>
                        </div>
                        <div class="px-6 py-4">
                            <?php $item_count = count($order['items']); $i = 0; foreach ($order['items'] as $item): $i++; ?>
                                <div class="flex justify-between items-center py-2 <?php echo $i < $item_count ? 'border-b border-gray-100' : ''; ?>">
                                    <div>
                                        <p class="font-medium text-gray-800 text-sm"><?php echo e($item['name'] ?: 'Produk tidak tersedia'); ?></p>
                                        <p class="text-xs text-gray-400"><?php echo (int)$item['qty']; ?> x <?php echo format_rupiah($item['price']); ?></p>
                                    </div>
                                    <p class="font-bold text-gray-700 text-sm"><?php echo format_rupiah($item['price'] * $item['qty']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="flex justify-between items-center px-6 py-4 bg-cream border-t border-gray-100">
                            <span class="font-semibold text-navy">Total Bayar</span>
                            <span class="font-bold text-xl text-gold-dark"><?php echo format_rupiah($order['total']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
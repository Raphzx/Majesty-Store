<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ' . url('admin/login'));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $status = trim($_POST['status']);
    $allowed = ['Baru', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];
    if (in_array($status, $allowed)) {
        $stmt = $conn->prepare("UPDATE transactions SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();

        if ($status === 'Dibatalkan') {
            $stmt = $conn->prepare("SELECT product_id, qty FROM transaction_details WHERE transaction_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $details = $stmt->get_result();
            while ($d = $details->fetch_assoc()) {
                $stmt2 = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
                $stmt2->bind_param("ii", $d['qty'], $d['product_id']);
                $stmt2->execute();
            }
        }
    }
    header('Location: ' . url('admin/transactions'));
    exit();
}

$result = $conn->query("SELECT * FROM transactions ORDER BY date DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .status-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; }
        .status-Baru { background: #eff6ff; color: #2563eb; }
        .status-Diproses { background: #fef3c7; color: #d97706; }
        .status-Dikirim { background: #ede9fe; color: #7c3aed; }
        .status-Selesai { background: #dcfce7; color: #16a34a; }
        .status-Dibatalkan { background: #fee2e2; color: #dc2626; }
        .status-select {
            padding: 0.35rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;
            font-size: 0.8rem; outline: none; background: white; cursor: pointer;
        }
        .dark .status-select {
            background: #1b2635; border-color: #2f4057; color: #dfe6ef;
        }
        .dark .status-select option {
            background: #1b2635; color: #dfe6ef;
        }
    </style>
</head>
<body class="bg-cream">
    <div class="flex min-h-screen">
        <?php $active_page = 'transactions'; include '../includes/admin_sidebar.php'; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-10">
            <div class="flex items-center gap-3 mb-8">
                <button id="adminBurger" class="admin-burger" aria-label="Buka menu"><i class="fas fa-bars"></i></button>
                <div>
                    <h1 class="section-title text-2xl sm:text-3xl">Daftar Transaksi</h1>
                    <p class="section-subtitle mt-2">Riwayat transaksi pelanggan</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md border border-gold/10 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table-premium w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Pelanggan</th>
                                <th>Email</th>
                                <th>Jumlah Item</th>
                                <th>Total</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while($row = $result->fetch_assoc()) {
                                $stmt = $conn->prepare("SELECT SUM(qty) as total_items FROM transaction_details WHERE transaction_id = ?");
                                $stmt->bind_param("i", $row['id']);
                                $stmt->execute();
                                $items = $stmt->get_result()->fetch_assoc();
                                $totalItems = $items['total_items'] ?: 0;
                                echo '<tr>';
                                echo '<td><span class="font-mono text-sm text-gray-500">#' . str_pad($row['id'], 4, '0', STR_PAD_LEFT) . '</span></td>';
                                echo '<td><span class="font-medium text-navy">' . e($row['customer_name']) . '</span></td>';
                                echo '<td class="text-gray-500">' . e($row['customer_email']) . '</td>';
                                echo '<td><span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">' . $totalItems . ' item</span></td>';
                                echo '<td><span class="font-bold text-gold-dark">' . format_rupiah($row['total']) . '</span></td>';
                                echo '<td class="text-gray-500 text-sm">' . date('d M Y H:i', strtotime($row['date'])) . '</td>';
                                echo '<td>';
                                echo '<form method="POST" action="' . url('admin/transactions') . '" class="flex items-center gap-2">';
                                echo '<input type="hidden" name="id" value="' . $row['id'] . '">';
                                echo '<input type="hidden" name="update_status" value="1">';
                                echo '<select name="status" class="status-select" onchange="this.form.submit()">';
                                foreach (['Baru', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'] as $s) {
                                    $selected = ($s === $row['status']) ? 'selected' : '';
                                    echo '<option value="' . $s . '" ' . $selected . '>' . $s . '</option>';
                                }
                                echo '</select>';
                                echo '</form>';
                                echo '</td>';
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <script src="<?php echo url('assets/js/admin_sidebar.js'); ?>?v=2"></script>
</body>
</html>
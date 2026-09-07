<?php
session_start();
include '../includes/db.php';

$cart = $_SESSION['cart'] ?? [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = trim($_POST['customer_name']);
    $customer_email = trim($_POST['customer_email']);
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $customer_address = trim($_POST['customer_address'] ?? '');

    if (empty($cart)) {
        header('Location: ' . url('customer/cart'));
        exit();
    }

    $total = 0;
    $items = [];
    foreach ($cart as $product_id => $qty) {
        $stmt = $conn->prepare("SELECT id, price, stock FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        if (!$product) continue;
        $qty = max(1, min((int)$qty, (int)$product['stock']));
        $items[] = ['id' => $product['id'], 'qty' => $qty, 'price' => (float)$product['price']];
        $total += $product['price'] * $qty;
    }

    if (empty($items)) {
        header('Location: ' . url('customer/cart'));
        exit();
    }

    $customer_id = !empty($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : null;

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO transactions (customer_id, customer_name, customer_email, total, status, date) VALUES (?, ?, ?, ?, 'Baru', NOW())");
        if ($customer_id) {
            $stmt->bind_param("issd", $customer_id, $customer_name, $customer_email, $total);
        } else {
            $null = null;
            $stmt->bind_param("issd", $null, $customer_name, $customer_email, $total);
        }
        $stmt->execute();
        $transaction_id = $stmt->insert_id;

        foreach ($items as $item) {
            $stmt2 = $conn->prepare("INSERT INTO transaction_details (transaction_id, product_id, qty, price) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("iiid", $transaction_id, $item['id'], $item['qty'], $item['price']);
            $stmt2->execute();

            $stmt3 = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
            $stmt3->bind_param("iii", $item['qty'], $item['id'], $item['qty']);
            $stmt3->execute();
        }

        $conn->commit();
        unset($_SESSION['cart']);
        header('Location: ' . url('customer/checkout?success=1&order=' . $transaction_id));
        exit();
    } catch (Exception $ex) {
        $conn->rollback();
        $error = "Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.";
    }
}

$success = isset($_GET['success']) && $_GET['success'] == 1;
$order_id = isset($_GET['order']) ? (int)$_GET['order'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Toko Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .input-wrapper { position: relative; }
        .input-icon {
            position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);
            color: #9ca3af; pointer-events: none; z-index: 10; font-size: 1rem;
            width: 1.25rem; text-align: center;
        }
        .checkout-input {
            width: 100%; height: 3rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;
            padding-left: 3rem; padding-right: 1rem; outline: none; transition: all 0.2s;
        }
        textarea.checkout-input {
            height: auto; padding: 0.75rem 1rem; resize: vertical;
        }
        .form-premium .checkout-input {
            padding-left: 3rem;
            padding-right: 1rem;
        }
        .form-premium textarea.checkout-input {
            height: auto; padding: 0.75rem 1rem;
        }
        .checkout-input:focus { border-color: #eab308; box-shadow: 0 0 0 3px rgba(234,179,8,0.15); }
        label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #1e293b; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="container mx-auto px-4 lg:px-8 py-12">
        <?php if ($success): ?>
            <div class="max-w-lg mx-auto text-center py-16">
                <div class="w-20 h-20 mx-auto rounded-full bg-green-100 flex items-center justify-center mb-6">
                    <i class="fas fa-check-circle text-4xl text-green-500"></i>
                </div>
                <h1 class="text-3xl font-bold text-navy mb-3">Pesanan Berhasil!</h1>
                <p class="text-gray-500 mb-2">Terima kasih atas pembelian Anda.</p>
                <p class="text-gray-400 mb-8">Nomor pesanan: <span class="font-bold text-gold-dark">#<?php echo str_pad($order_id, 4, '0', STR_PAD_LEFT); ?></span></p>
                <a href="<?php echo url('customer/products'); ?>" class="btn-premium">
                    <i class="fas fa-store mr-2"></i> Lanjut Belanja
                </a>
            </div>
        <?php elseif (isset($error)): ?>
            <div class="max-w-lg mx-auto text-center py-16">
                <div class="w-20 h-20 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-6">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-500"></i>
                </div>
                <h1 class="text-3xl font-bold text-navy mb-3">Checkout Gagal</h1>
                <p class="text-gray-500 mb-8"><?php echo e($error); ?></p>
                <a href="<?php echo url('customer/cart'); ?>" class="btn-premium">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Keranjang
                </a>
            </div>
        <?php elseif (empty($cart)): ?>
            <div class="max-w-lg mx-auto text-center py-16">
                <div class="text-7xl text-gray-300 mb-6"><i class="fas fa-shopping-cart"></i></div>
                <h1 class="text-2xl font-semibold text-gray-500 mb-3">Keranjang Kosong</h1>
                <p class="text-gray-400 mb-8">Tidak ada produk untuk di-checkout</p>
                <a href="<?php echo url('customer/products'); ?>" class="btn-premium">
                    <i class="fas fa-store mr-2"></i> Lihat Produk
                </a>
            </div>
        <?php else: ?>
            <div class="max-w-3xl mx-auto">
                <div class="text-center mb-10">
                    <h1 class="section-title text-3xl lg:text-4xl">Checkout</h1>
                    <p class="section-subtitle mt-4">Lengkapi data diri Anda untuk melanjutkan pembayaran</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-6 gap-8">
                    <div class="lg:col-span-3 bg-white rounded-xl shadow-lg border border-gold/10 p-8">
                        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-6 rounded-full bg-gradient-to-br from-gold/10 to-gold/5">
                            <i class="fas fa-credit-card text-2xl text-gold-dark"></i>
                        </div>

                        <form method="POST" class="form-premium">
                            <div class="mb-4">
                                <label for="name">Nama Lengkap <span class="text-red-400">*</span></label>
                                <div class="input-wrapper relative">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" name="customer_name" id="name" required placeholder="Masukkan nama lengkap Anda" class="checkout-input" value="<?php echo e($_SESSION['customer_name'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="email">Email <span class="text-red-400">*</span></label>
                                <div class="input-wrapper relative">
                                    <i class="fas fa-envelope input-icon"></i>
                                    <input type="email" name="customer_email" id="email" required placeholder="contoh@email.com" class="checkout-input" value="<?php echo e($_SESSION['customer_email'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="phone">No. HP</label>
                                <div class="input-wrapper relative">
                                    <i class="fas fa-phone input-icon"></i>
                                    <input type="text" name="customer_phone" id="phone" placeholder="08xxxxxxxxxx" class="checkout-input">
                                </div>
                            </div>
                            <div class="mb-6">
                                <label for="address">Alamat Pengiriman</label>
                                <textarea name="customer_address" id="address" rows="3" placeholder="Alamat lengkap pengiriman" class="checkout-input" style="height:auto;"></textarea>
                            </div>

                            <button type="submit" class="w-full text-center text-sm py-3.5 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-semibold rounded-lg transition duration-300 shadow-md">
                                <i class="fas fa-check-circle mr-2"></i> Konfirmasi & Bayar
                            </button>

                            <a href="<?php echo url('customer/cart'); ?>" class="block text-center mt-4 text-gray-500 hover:text-gray-700 transition text-sm">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Keranjang
                            </a>
                        </form>
                    </div>

                    <div class="lg:col-span-3 bg-white rounded-xl shadow-lg border border-gold/10 p-6 h-fit">
                        <h2 class="text-lg font-bold text-navy mb-4"><i class="fas fa-list mr-2 text-gold-dark"></i>Ringkasan Pesanan</h2>
                        <?php
                        foreach ($cart as $product_id => $qty) {
                            $stmt = $conn->prepare("SELECT name, price, stock FROM products WHERE id = ?");
                            $stmt->bind_param("i", $product_id);
                            $stmt->execute();
                            $p = $stmt->get_result()->fetch_assoc();
                            if (!$p) continue;
                            $q = max(1, min((int)$qty, (int)$p['stock']));
                            echo '<div class="flex justify-between items-center py-3 border-b border-gray-100">';
                            echo '<div>';
                            echo '<p class="font-medium text-gray-800 text-sm">' . e($p['name']) . '</p>';
                            echo '<p class="text-xs text-gray-400">' . $q . ' x ' . format_rupiah($p['price']) . '</p>';
                            echo '</div>';
                            echo '<p class="font-bold text-gold-dark text-sm">' . format_rupiah($p['price'] * $q) . '</p>';
                            echo '</div>';
                        }
                        ?>
                        <?php
                        $grandTotal = 0;
                        foreach ($cart as $product_id => $qty) {
                            $stmt = $conn->prepare("SELECT price, stock FROM products WHERE id = ?");
                            $stmt->bind_param("i", $product_id);
                            $stmt->execute();
                            $p = $stmt->get_result()->fetch_assoc();
                            if (!$p) continue;
                            $grandTotal += $p['price'] * max(1, min((int)$qty, (int)$p['stock']));
                        }
                        ?>
                        <div class="flex justify-between items-center mt-4">
                            <span class="font-bold text-navy text-lg">Total Bayar</span>
                            <span class="font-bold text-2xl text-gold-dark"><?php echo format_rupiah($grandTotal); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
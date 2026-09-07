<?php
session_start();
include '../includes/db.php';

$cart = $_SESSION['cart'] ?? [];

if (isset($_POST['add'])) {
    $product_id = (int)$_POST['add'];
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    $cart[$product_id] = ($cart[$product_id] ?? 0) + $qty;
    $_SESSION['cart'] = $cart;
    header('Location: ' . url('customer/cart'));
    exit();
}

if (isset($_GET['add'])) {
    $product_id = (int)$_GET['add'];
    $cart[$product_id] = ($cart[$product_id] ?? 0) + 1;
    $_SESSION['cart'] = $cart;
    header('Location: ' . url('customer/cart'));
    exit();
}

if (isset($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];
    unset($cart[$product_id]);
    $_SESSION['cart'] = $cart;
    header('Location: ' . url('customer/cart'));
    exit();
}

if (isset($_POST['update_qty'])) {
    foreach ($_POST['qty'] as $pid => $q) {
        $pid = (int)$pid;
        $q = max(0, (int)$q);
        if ($q <= 0) {
            unset($cart[$pid]);
        } else {
            $cart[$pid] = $q;
        }
    }
    $_SESSION['cart'] = $cart;
    header('Location: ' . url('customer/cart'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - Majesty Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="container mx-auto px-4 lg:px-8 py-12">
        <div class="mb-12">
            <h1 class="section-title text-3xl lg:text-4xl">Keranjang Belanja</h1>
            <p class="section-subtitle mt-4">Review pesanan Anda sebelum checkout</p>
        </div>

        <?php if (empty($cart)): ?>
            <div class="text-center py-20">
                <div class="text-7xl text-gray-300 mb-6"><i class="fas fa-shopping-cart"></i></div>
                <h2 class="text-2xl font-semibold text-gray-500 mb-3">Keranjang Anda Kosong</h2>
                <p class="text-gray-400 mb-8">Belum ada produk yang ditambahkan ke keranjang</p>
                <a href="<?php echo url('customer/products'); ?>" class="btn-premium">
                    <i class="fas fa-arrow-left mr-2"></i> Mulai Belanja
                </a>
            </div>
        <?php else: ?>
            <form method="POST" action="<?php echo url('customer/cart'); ?>">
                <div class="bg-white rounded-xl shadow-md border border-gold/10 p-6 lg:p-8">
                    <table class="cart-table w-full">
                        <thead>
                            <tr>
                                <th class="text-left pb-3">Produk</th>
                                <th class="text-left pb-3">Harga</th>
                                <th class="text-center pb-3 w-32">Qty</th>
                                <th class="text-right pb-3">Subtotal</th>
                                <th class="text-right pb-3 w-20">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total = 0;
                            foreach ($cart as $product_id => $qty) {
                                $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                                $stmt->bind_param("i", $product_id);
                                $stmt->execute();
                                $product = $stmt->get_result()->fetch_assoc();
                                if (!$product) continue;
                                $maxQty = max(1, (int)$product['stock']);
                                $qty = max(1, min($qty, $maxQty));
                                $subtotal = $product['price'] * $qty;
                                $total += $subtotal;
                                echo '<tr>';
                                echo '<td class="py-4">';
                                echo '<span class="font-medium text-navy">' . e($product['name']) . '</span>';
                                echo '</td>';
                                echo '<td class="py-4 font-semibold text-gray-600" data-label="Harga">Rp ' . rupiah_only($product['price']) . '</td>';
                                echo '<td class="py-4 text-center" data-label="Jumlah">';
                                echo '<input type="number" name="qty[' . (int)$product_id . ']" value="' . $qty . '" min="1" max="' . $maxQty . '" class="w-20 px-2 py-2 border border-gray-300 rounded-lg text-center">';
                                echo '</td>';
                                echo '<td class="py-4 text-right font-bold text-gold-dark" data-label="Subtotal">Rp ' . rupiah_only($subtotal) . '</td>';
                                echo '<td class="py-4 text-right" data-label="Hapus">';
                                echo '<a href="' . url('customer/cart?remove=' . $product_id) . '" class="text-red-400 hover:text-red-600 transition" onclick="return confirm(\'Hapus item ini?\')">';
                                echo '<i class="fas fa-trash-alt"></i>';
                                echo '</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                            ?>
                            <tr class="cart-total-row border-t-2 border-gold/20">
                                <td colspan="3" class="pt-4 text-lg font-bold text-navy">Total Belanja</td>
                                <td class="pt-4 text-right font-bold text-2xl text-gold-dark">Rp <?php echo rupiah_only($total); ?></td>
                                <td class="pt-4"></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-8 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4">
                        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                            <a href="<?php echo url('customer/products'); ?>" class="btn-premium-outline text-sm w-full sm:w-auto text-center">
                                <i class="fas fa-arrow-left mr-2"></i> Lanjut Belanja
                            </a>
                            <button type="submit" name="update_qty" value="1" class="btn-navy text-sm w-full sm:w-auto text-center">
                                <i class="fas fa-sync-alt mr-2"></i> Perbarui Qty
                            </button>
                        </div>
                        <a href="<?php echo url('customer/checkout'); ?>" class="btn-premium w-full sm:w-auto text-center">
                            <i class="fas fa-credit-card mr-2"></i> Checkout Sekarang
                        </a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
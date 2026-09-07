<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$category_id = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : 0;

$query = "SELECT p.*, c.name AS category_name FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          WHERE 1=1";
$params = [];
$types = "";

if ($search !== '') {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $types .= "ss";
}
if ($category_id > 0) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}
$query .= " AND p.stock > 0 ORDER BY p.id DESC";

$stmt = $conn->prepare($query);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - Majesty Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .product-img {
            width: 100%;
            height: 128px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        @media (min-width: 640px) {
            .product-img {
                height: 200px;
            }
        }
        .card-premium:hover .product-img {
            transform: scale(1.05);
        }
        .img-container {
            overflow: hidden;
            border-radius: 0.75rem 0.75rem 0 0;
        }
        .search-wrap { position: relative; }
        .search-wrap i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #9ca3af; }
        .search-input {
            width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #e2e8f0; border-radius: 0.5rem; outline: none; transition: all 0.2s;
        }
        .search-input:focus { border-color: #eab308; box-shadow: 0 0 0 3px rgba(234,179,8,0.15); }
        .cat-filter select {
            padding: 0.6rem 2.5rem 0.6rem 1rem; border: 1px solid #e2e8f0;
            border-radius: 0.5rem; outline: none; background: white;
        }
    </style>
</head>
<body>
    <main class="container mx-auto px-4 lg:px-8 py-12">
        <div class="mb-10">
            <h1 class="section-title text-3xl lg:text-4xl">Koleksi Produk</h1>
            <p class="section-subtitle mt-4">Temukan produk pilihan terbaik untuk Anda</p>
        </div>

        <form method="GET" action="<?php echo url('customer/products'); ?>" class="flex flex-col md:flex-row gap-4 mb-10">
            <div class="search-wrap flex-1">
                <i class="fas fa-search"></i>
                <input type="text" name="q" value="<?php echo e($search); ?>" placeholder="Cari produk..." class="search-input">
            </div>
            <div class="cat-filter">
                <select name="category" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $category_id ? 'selected' : ''; ?>>
                            <?php echo e($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn-premium-outline px-6 py-2 text-sm">Cari</button>
            <?php if ($search !== '' || $category_id > 0): ?>
                <a href="<?php echo url('customer/products'); ?>" class="btn-navy px-6 py-2 text-sm text-center">Reset</a>
            <?php endif; ?>
        </form>

        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6">
            <?php
            $delay = 0;
            while($row = $result->fetch_assoc()) {
                $delay++;
                echo '<div class="card-premium bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden animate-slide-up" style="animation-delay: ' . ($delay * 0.05) . 's">';

                echo '<div class="img-container relative bg-gray-100">';
                if ($row['image'] && $row['image'] != '' && file_exists(dirname(__DIR__) . "/uploads/" . $row['image'])) {
                    echo '<img src="' . url('uploads/' . $row['image']) . '" alt="' . e($row['name']) . '" class="product-img w-full h-32 sm:h-48 object-cover">';
                } else {
                    echo '<div class="w-full h-32 sm:h-48 flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">';
                    echo '<i class="fas fa-image text-4xl sm:text-5xl text-gray-400"></i>';
                    echo '</div>';
                }
                echo '</div>';

                echo '<div class="p-3 sm:p-5">';
                echo '<div class="mb-1 sm:mb-2">';
                echo '<span class="text-[10px] sm:text-xs font-semibold text-yellow-600 uppercase tracking-wider"><i class="fas fa-tag mr-1"></i>' . e($row['category_name'] ?: 'Premium') . '</span>';
                echo '</div>';
                echo '<h3 class="text-sm sm:text-lg font-bold text-gray-800 mb-1 sm:mb-2 line-clamp-2">' . e($row['name']) . '</h3>';
                echo '<p class="text-gray-500 text-xs sm:text-sm mb-2 sm:mb-4 leading-relaxed line-clamp-2 hidden sm:block">' . e(substr($row['description'], 0, 80)) . '...</p>';
                echo '<div class="flex justify-between items-end mb-2 sm:mb-4">';
                echo '<div>';
                echo '<p class="text-base sm:text-2xl font-bold text-yellow-600">Rp ' . rupiah_only($row['price']) . '</p>';
                echo '<p class="text-[10px] sm:text-xs text-gray-400 mt-1"><i class="fas fa-box mr-1"></i>Stok: ' . (int)$row['stock'] . '</p>';
                echo '</div>';
                echo '</div>';
                echo '<form method="POST" action="' . url('customer/cart') . '" class="flex gap-1 sm:gap-2">';
                echo '<input type="hidden" name="add" value="' . (int)$row['id'] . '">';
                echo '<input type="number" name="qty" value="1" min="1" max="' . (int)$row['stock'] . '" class="w-12 sm:w-20 px-2 sm:px-3 py-2 border border-gray-300 rounded-lg text-center text-sm">';
                echo '<button type="submit" class="flex-1 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white text-center py-2 sm:py-3 rounded-lg transition duration-300 font-semibold text-xs sm:text-base">';
                echo '<i class="fas fa-shopping-cart mr-1"></i>Tambah';
                echo '</button>';
                echo '</form>';
                echo '</div>';
                echo '</div>';
            }
            if ($delay === 0) {
                echo '<div class="col-span-full text-center py-16">';
                echo '<div class="text-6xl text-gray-300 mb-4"><i class="fas fa-box-open"></i></div>';
                echo '<p class="text-xl text-gray-400">' . ($search !== '' ? 'Produk tidak ditemukan' : 'Belum ada produk tersedia') . '</p>';
                echo '<p class="text-gray-300 mt-2">Silakan coba kata kunci atau kategori lain</p>';
                echo '</div>';
            }
            ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
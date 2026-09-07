<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ' . url('admin/login'));
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("UPDATE products SET category_id = NULL WHERE category_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header('Location: ' . url('admin/categories'));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save'])) {
    $name = trim($_POST['name']);
    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
    }
    header('Location: ' . url('admin/categories'));
    exit();
}

$result = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) as total_products FROM categories c ORDER BY c.name");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-cream">
    <div class="flex min-h-screen">
        <?php $active_page = 'categories'; include '../includes/admin_sidebar.php'; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-10">
            <div class="flex justify-between items-center mb-8 gap-4">
                <div class="flex items-center gap-3">
                    <button id="adminBurger" class="admin-burger" aria-label="Buka menu"><i class="fas fa-bars"></i></button>
                    <div>
                        <h1 class="section-title text-2xl sm:text-3xl">Kelola Kategori</h1>
                        <p class="section-subtitle mt-2">Atur kategori produk toko Anda</p>
                    </div>
                </div>
                <button onclick="showForm()" class="btn-premium text-sm py-2.5 px-5">
                    <i class="fas fa-plus mr-2"></i>Tambah Kategori
                </button>
            </div>

            <div id="formModal" class="modal-premium fixed inset-0 hidden items-center justify-center z-50">
                <div class="modal-content bg-white rounded-xl shadow-xl max-w-sm w-full mx-4 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-navy" id="modalTitle">Tambah Kategori</h2>
                        <button onclick="hideForm()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                    </div>
                    <form method="POST" class="form-premium">
                        <div class="mb-4">
                            <label for="categoryName" class="block text-gray-700 font-medium mb-2">Nama Kategori</label>
                            <input type="text" name="name" id="categoryName" required placeholder="Masukkan nama kategori" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="hideForm()" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">Batal</button>
                            <button type="submit" name="save" class="px-6 py-2.5 bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-lg hover:from-yellow-600 hover:to-yellow-700 transition">
                                <i class="fas fa-save mr-2"></i>Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md border border-gold/10 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Produk</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4"><span class="font-mono text-sm text-gray-500"><?php echo $row['id']; ?></span></td>
                                    <td class="px-6 py-4">
                                        <span class="font-medium text-navy">
                                            <i class="fas fa-tag text-gold-dark mr-2"></i><?php echo e($row['name']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-medium"><?php echo (int)$row['total_products']; ?> produk</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus kategori ini? Produk dalam kategori akan menjadi tanpa kategori.')" class="text-red-400 hover:text-red-600 transition" title="Hapus"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        function showForm() {
            document.getElementById('categoryName').value = '';
            document.getElementById('formModal').classList.remove('hidden');
            document.getElementById('formModal').classList.add('flex');
        }

        function hideForm() {
            document.getElementById('formModal').classList.add('hidden');
            document.getElementById('formModal').classList.remove('flex');
        }

        window.onclick = function(event) {
            const modal = document.getElementById('formModal');
            if (event.target === modal) {
                hideForm();
            }
        }
    </script>
    <script src="<?php echo url('assets/js/admin_sidebar.js'); ?>?v=2"></script>
</body>
</html>
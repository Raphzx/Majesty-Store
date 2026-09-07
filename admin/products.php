<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ' . url('admin/login'));
    exit();
}

$uploadsDir = dirname(__DIR__) . '/uploads';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if ($row['image'] && file_exists($uploadsDir . '/' . $row['image'])) {
            unlink($uploadsDir . '/' . $row['image']);
        }
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header('Location: ' . url('admin/products'));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
    $image = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $newname = time() . '_' . uniqid() . '.' . $ext;
            $destination = $uploadsDir . '/' . $newname;

            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0777, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $image = $newname;
            }
        }
    }

    if (isset($_POST['id']) && $_POST['id'] != '') {
        $id = (int)$_POST['id'];
        if ($image) {
            $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                if ($row['image'] && file_exists($uploadsDir . '/' . $row['image'])) {
                    unlink($uploadsDir . '/' . $row['image']);
                }
            }

            $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, category_id=?, image=? WHERE id=?");
            if ($category_id) {
                $stmt->bind_param("ssdiisi", $name, $description, $price, $stock, $category_id, $image, $id);
            } else {
                $nullCat = null;
                $stmt->bind_param("ssdiisi", $name, $description, $price, $stock, $nullCat, $image, $id);
            }
        } else {
            $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, category_id=? WHERE id=?");
            if ($category_id) {
                $stmt->bind_param("ssdiis", $name, $description, $price, $stock, $category_id, $id);
            } else {
                $nullCat = null;
                $stmt->bind_param("ssdiis", $name, $description, $price, $stock, $nullCat, $id);
            }
        }
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category_id, image) VALUES (?, ?, ?, ?, ?, ?)");
        if ($category_id) {
            $stmt->bind_param("ssdiis", $name, $description, $price, $stock, $category_id, $image);
        } else {
            $nullCat = null;
            $stmt->bind_param("ssdiis", $name, $description, $price, $stock, $nullCat, $image);
        }
        $stmt->execute();
    }

    header('Location: ' . url('admin/products'));
    exit();
}

$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .image-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 0.5rem;
            border: 2px solid #e2e8f0;
            margin-top: 0.5rem;
        }
        .preview-container {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 0.375rem;
        }
    </style>
</head>
<body class="bg-cream">
    <div class="flex min-h-screen">
        <?php $active_page = 'products'; include '../includes/admin_sidebar.php'; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-10">
            <div class="flex justify-between items-center mb-8 gap-4">
                <div class="flex items-center gap-3">
                    <button id="adminBurger" class="admin-burger" aria-label="Buka menu"><i class="fas fa-bars"></i></button>
                    <div>
                        <h1 class="section-title text-2xl sm:text-3xl">Kelola Produk</h1>
                        <p class="section-subtitle mt-2">Atur produk toko Anda</p>
                    </div>
                </div>
                <button onclick="showForm()" class="btn-premium text-sm py-2.5 px-5">
                    <i class="fas fa-plus mr-2"></i>Tambah Produk
                </button>
            </div>

            <div id="formModal" class="modal-premium fixed inset-0 hidden items-center justify-center z-50">
                <div class="modal-content bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="modal-title text-xl font-bold text-navy" id="modalTitle">Tambah Produk</h2>
                        <button onclick="hideForm()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                    </div>
                    <form method="POST" enctype="multipart/form-data" class="form-premium">
                        <input type="hidden" name="id" id="productId">
                        <div class="mb-4">
                            <label for="productName" class="block text-gray-700 font-medium mb-2">Nama Produk</label>
                            <input type="text" name="name" id="productName" required placeholder="Masukkan nama produk" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                        <div class="mb-4">
                            <label for="productCategory" class="block text-gray-700 font-medium mb-2">Kategori</label>
                            <select name="category_id" id="productCategory" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                <option value="">-- Tanpa Kategori --</option>
                                <?php while ($cat = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo e($cat['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="productDesc" class="block text-gray-700 font-medium mb-2">Deskripsi</label>
                            <textarea name="description" id="productDesc" required rows="3" placeholder="Masukkan deskripsi produk" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="productPrice" class="block text-gray-700 font-medium mb-2">Harga (Rp)</label>
                                <input type="number" min="0" name="price" id="productPrice" required placeholder="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                            <div>
                                <label for="productStock" class="block text-gray-700 font-medium mb-2">Stok</label>
                                <input type="number" min="0" name="stock" id="productStock" required placeholder="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="productImage" class="block text-gray-700 font-medium mb-2">Gambar Produk</label>
                            <input type="file" name="image" id="productImage" accept="image/*" onchange="previewImage(this)" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            <div id="imagePreview" class="preview-container mt-2"></div>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gambar</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            $result = $conn->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
                            while($row = $result->fetch_assoc()) {
                                $jsonName = json_encode($row['name']);
                                $jsonDesc = json_encode($row['description']);
                                $jsonImage = json_encode($row['image']);
                                ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <?php if ($row['image'] && file_exists($uploadsDir . '/' . $row['image'])): ?>
                                            <img src="<?php echo url('uploads/' . $row['image']); ?>" alt="<?php echo e($row['name']); ?>" class="product-image w-12 h-12 object-cover rounded-lg">
                                        <?php else: ?>
                                            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center"><i class="fas fa-image text-gray-400"></i></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4"><span class="font-medium text-navy"><?php echo e($row['name']); ?></span></td>
                                    <td class="px-6 py-4">
                                        <?php if ($row['category_name']): ?>
                                            <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-medium"><?php echo e($row['category_name']); ?></span>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400">Tanpa kategori</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4"><span class="font-semibold text-gold-dark">Rp <?php echo rupiah_only($row['price']); ?></span></td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 <?php echo $row['stock'] > 0 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'; ?> rounded-full text-xs font-medium"><?php echo (int)$row['stock']; ?></span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button onclick='editProduct(<?php echo $row['id']; ?>, <?php echo $jsonName; ?>, <?php echo $jsonDesc; ?>, <?php echo $row['price']; ?>, <?php echo $row['stock']; ?>, <?php echo $row['category_id'] ?: 'null'; ?>, <?php echo $jsonImage; ?>)' class="text-yellow-600 hover:text-yellow-700 transition mx-2" title="Edit"><i class="fas fa-edit"></i></button>
                                        <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus produk ini?')" class="text-red-400 hover:text-red-600 transition mx-2" title="Hapus"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="<?php echo url('assets/js/admin_sidebar.js'); ?>?v=2"></script>

    <script>
        let currentImage = '';

        function previewImage(input) {
            const previewDiv = document.getElementById('imagePreview');
            previewDiv.innerHTML = '';

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview';
                    previewDiv.appendChild(img);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function showForm() {
            document.getElementById('modalTitle').textContent = 'Tambah Produk';
            document.getElementById('formModal').classList.remove('hidden');
            document.getElementById('formModal').classList.add('flex');
        }

        function hideForm() {
            document.getElementById('formModal').classList.add('hidden');
            document.getElementById('formModal').classList.remove('flex');
            clearForm();
        }

        function editProduct(id, name, desc, price, stock, categoryId, image) {
            document.getElementById('productId').value = id;
            document.getElementById('productName').value = name;
            document.getElementById('productDesc').value = desc;
            document.getElementById('productPrice').value = price;
            document.getElementById('productStock').value = stock;
            document.getElementById('productCategory').value = categoryId || '';
            currentImage = image;

            const previewDiv = document.getElementById('imagePreview');
            previewDiv.innerHTML = '';
            if (image && image !== 'null' && image !== '') {
                const img = document.createElement('img');
                img.src = '<?php echo url('uploads'); ?>/' + image;
                img.className = 'image-preview';
                const caption = document.createElement('p');
                caption.className = 'text-xs text-gray-500 mt-1';
                caption.textContent = 'Gambar saat ini';
                previewDiv.appendChild(img);
                previewDiv.appendChild(caption);
            }

            document.getElementById('modalTitle').textContent = 'Edit Produk';
            document.getElementById('formModal').classList.remove('hidden');
            document.getElementById('formModal').classList.add('flex');
        }

        function clearForm() {
            document.getElementById('productId').value = '';
            document.getElementById('productName').value = '';
            document.getElementById('productDesc').value = '';
            document.getElementById('productPrice').value = '';
            document.getElementById('productStock').value = '';
            document.getElementById('productCategory').value = '';
            const imageInput = document.getElementById('productImage');
            if (imageInput) imageInput.value = '';
            document.getElementById('imagePreview').innerHTML = '';
            currentImage = '';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('formModal');
            if (event.target === modal) {
                hideForm();
            }
        }
    </script>
</body>
</html>
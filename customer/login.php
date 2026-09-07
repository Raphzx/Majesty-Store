<?php
session_start();
include '../includes/db.php';

if (!empty($_SESSION['customer_id'])) {
    header('Location: ' . url('customer/products'));
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'login';

    if ($action == 'login') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['customer_id'] = $row['id'];
                $_SESSION['customer_name'] = $row['name'];
                $_SESSION['customer_email'] = $row['email'];
                header('Location: ' . url(''));
                exit();
            } else {
                $error = "Email atau password salah!";
            }
        } else {
            $error = "Email atau password salah!";
        }
    } elseif ($action == 'register') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $password = $_POST['password'];
        $confirm = $_POST['confirm_password'];

        if ($password !== $confirm) {
            $error = "Konfirmasi password tidak cocok!";
        } elseif (strlen($password) < 6) {
            $error = "Password minimal 6 karakter!";
        } else {
            $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = "Email sudah terdaftar, silakan login.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO customers (name, email, phone, address, password) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $name, $email, $phone, $address, $hash);
                if ($stmt->execute()) {
                    $success = "Pendaftaran berhasil! Silakan login.";
                    $activeTab = 'login';
                } else {
                    $error = "Gagal mendaftar. Coba lagi.";
                }
            }
        }
    }
}
$activeTab = isset($activeTab) ? $activeTab : 'login';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Daftar - Toko Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
            z-index: 20;
            font-size: 1rem;
            width: 1.25rem;
            text-align: center;
        }
        .auth-input {
            width: 100%;
            height: 3rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding-left: 3rem;
            padding-right: 1rem;
            outline: none;
            transition: all 0.2s;
        }
        textarea.auth-input {
            height: auto;
            padding: 0.75rem 1rem;
        }
        .form-premium .auth-input,
        .form-premium input.auth-input {
            padding-left: 3rem;
            padding-right: 1rem;
        }
        .form-premium textarea.auth-input {
            height: auto;
            padding: 0.75rem 1rem;
            padding-right: 1rem;
        }
        .auth-input:focus {
            border-color: #eab308;
            box-shadow: 0 0 0 3px rgba(234,179,8,0.15);
        }
        .auth-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #1e293b;
        }
        .tab-btn { color: #6b7280; border-bottom: 2px solid transparent; }
        .tab-btn.active { color: #1e293b; border-bottom-color: #eab308; }
    </style>
</head>
<body class="min-h-screen flex flex-col" style="background: linear-gradient(135deg, #0f1b2d 0%, #1a2d47 50%, #243b5a 100%);">
    <?php include '../includes/header.php'; ?>
    <div class="auth-card animate-fade-in w-full max-w-md mx-auto my-16 flex-1 flex items-center justify-center">
        <div class="login-card w-full">
            <div class="login-logo">
                <i class="fa-solid fa-crown"></i>
            </div>
            <h1 class="login-title text-2xl">Akun Pelanggan</h1>
            <p class="login-subtitle">Masuk atau buat akun baru untuk berbelanja</p>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6 text-sm flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo e($error); ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg mb-6 text-sm flex items-center">
                    <i class="fas fa-check-circle mr-2"></i> <?php echo e($success); ?>
                </div>
            <?php endif; ?>

            <div class="flex border-b border-gray-200 mb-6">
                <button type="button" id="tabLogin" onclick="switchTab('login')" class="tab-btn active flex-1 py-3 text-sm font-semibold">Login</button>
                <button type="button" id="tabRegister" onclick="switchTab('register')" class="tab-btn flex-1 py-3 text-sm font-semibold">Daftar</button>
            </div>

            <form method="POST" id="formLogin" class="form-premium">
                <input type="hidden" name="action" value="login">
                <div class="mb-5">
                    <label class="auth-label">Email</label>
                    <div class="relative">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" required placeholder="contoh@email.com" class="auth-input">
                    </div>
                </div>
                <div class="mb-6">
                    <label class="auth-label">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" required placeholder="Masukkan password" class="auth-input">
                    </div>
                </div>
                <button type="submit" class="btn-premium w-full text-sm py-3.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg transition">
                    <i class="fa-solid fa-right-to-bracket mr-2"></i> Masuk
                </button>
            </form>

            <form method="POST" id="formRegister" class="form-premium hidden">
                <input type="hidden" name="action" value="register">
                <div class="mb-4">
                    <label class="auth-label">Nama Lengkap</label>
                    <div class="relative">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="name" required placeholder="Nama lengkap" class="auth-input">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="auth-label">Email</label>
                    <div class="relative">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" required placeholder="contoh@email.com" class="auth-input">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="auth-label">No. HP</label>
                    <div class="relative">
                        <i class="fas fa-phone input-icon"></i>
                        <input type="text" name="phone" placeholder="08xxxxxxxxxx" class="auth-input">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="auth-label">Alamat</label>
                    <textarea name="address" rows="2" placeholder="Alamat lengkap" class="auth-input"></textarea>
                </div>
                <div class="mb-4">
                    <label class="auth-label">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" required placeholder="Minimal 6 karakter" class="auth-input">
                    </div>
                </div>
                <div class="mb-6">
                    <label class="auth-label">Konfirmasi Password</label>
                    <div class="relative">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="confirm_password" required placeholder="Ulangi password" class="auth-input">
                    </div>
                </div>
                <button type="submit" class="btn-premium w-full text-sm py-3.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg transition">
                    <i class="fa-solid fa-user-plus mr-2"></i> Daftar
                </button>
            </form>

            <a href="<?php echo url(''); ?>" class="block text-center mt-6 text-gray-400 hover:text-yellow-600 transition text-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Beranda
            </a>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        function switchTab(tab) {
            const isLogin = tab === 'login';
            document.getElementById('formLogin').classList.toggle('hidden', !isLogin);
            document.getElementById('formRegister').classList.toggle('hidden', isLogin);
            document.getElementById('tabLogin').classList.toggle('active', isLogin);
            document.getElementById('tabRegister').classList.toggle('active', !isLogin);
        }
        <?php if ($activeTab === 'register'): ?>
            switchTab('register');
        <?php endif; ?>
    </script>
</body>
</html>
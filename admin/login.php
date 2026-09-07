<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = md5($_POST['password']);
    
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['admin'] = true;
        header('Location: ' . url('admin/dashboard'));
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Toko Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
        input {
            padding-left: 3rem !important;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center" style="background: linear-gradient(135deg, #0f1b2d 0%, #1a2d47 50%, #243b5a 100%);">
    <button id="themeToggle" class="theme-toggle fixed top-4 right-4 z-50" aria-label="Mode Terang/Gelap" aria-pressed="false">
        <i class="fas fa-moon" id="themeToggleIcon"></i>
    </button>
    <div class="login-card animate-fade-in">
        <div class="login-logo">
            <i class="fa-solid fa-crown"></i>
        </div>
        <h1 class="login-title text-2xl">Admin Panel</h1>
        <p class="login-subtitle">Masuk untuk mengelola toko Anda</p>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6 text-sm flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="form-premium">
            <div class="mb-5">
                <label for="username" class="block text-gray-700 mb-2">Username</label>
                <div class="relative">
                    <i class="fas fa-user input-icon"></i>
                    <input 
                        type="text" 
                        name="username" 
                        id="username" 
                        required 
                        placeholder="Masukkan username"
                        class="w-full h-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 pl-10 pr-4"
                    >
                </div>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 mb-2">Password</label>
                <div class="relative">
                    <i class="fas fa-lock input-icon"></i>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        required 
                        placeholder="Masukkan password"
                        class="w-full h-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 pl-10 pr-4"
                    >
                </div>
            </div>
            <button type="submit" class="btn-premium w-full text-sm py-3.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg transition">
                <i class="fa-solid fa-right-to-bracket mr-2"></i> Masuk
            </button>
        </form>
        
        <a href="<?php echo url(''); ?>" class="block text-center mt-6 text-gray-400 hover:text-yellow-600 transition text-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Beranda
        </a>
    </div>
    <script src="<?php echo url('assets/js/theme.js'); ?>"></script>
</body>
</html>
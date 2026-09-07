<?php require_once 'includes/db.php'; ensure_session(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Majesty Store - Beranda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="hero-section">
        <div class="container mx-auto px-4 lg:px-8 hero-content">
            <div class="max-w-2xl">
                <p class="text-gold-light font-medium tracking-wider uppercase text-sm mb-4 animate-fade-in">Where Elegance Meets Quality</p>
                <h1 class="mb-6 animate-slide-up">Selamat Datang di <span>Majesty Store</span></h1>
                <p class="mb-8 animate-slide-up animate-slide-up-delay-1">Belanja mudah, cepat, dan terpercaya dari rumah Anda. Temukan produk berkualitas terbaik untuk kebutuhan sehari-hari.</p>
                <div class="flex flex-wrap gap-4 animate-slide-up animate-slide-up-delay-2">
                    <a href="<?php echo url('customer/products'); ?>" class="btn-premium">
                        <i class="fas fa-shopping-bag mr-2"></i> Lihat Produk
                    </a>
                    <?php if (!empty($_SESSION['customer_id'])): ?>
                        <a href="<?php echo url('customer/cart'); ?>" class="btn-premium-outline">
                            <i class="fas fa-shopping-cart mr-2"></i> <?php echo e($_SESSION['customer_name']); ?>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo url('customer/login'); ?>" class="btn-premium-outline">
                            <i class="fas fa-user mr-2"></i> Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <main class="container mx-auto px-4 lg:px-8 py-16">
        <div class="text-center mb-16">
            <h2 class="section-title text-3xl lg:text-4xl">Mengapa Memilih Kami?</h2>
            <p class="section-subtitle mt-4">Kami berkomitmen memberikan pengalaman belanja terbaik untuk Anda</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            <div class="feature-card animate-slide-up">
                <div class="feature-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <h3 class="text-xl">Produk Berkualitas</h3>
                <p>Kurasi produk terbaik dengan standar kualitas tinggi untuk memastikan kepuasan Anda.</p>
            </div>
            <div class="feature-card animate-slide-up animate-slide-up-delay-1">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="text-xl">Transaksi Aman</h3>
                <p>Sistem pembayaran yang aman dan terpercaya dengan perlindungan data berlapis.</p>
            </div>
            <div class="feature-card animate-slide-up animate-slide-up-delay-2">
                <div class="feature-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h3 class="text-xl">Pengiriman Cepat</h3>
                <p>Logistik handal dengan jangkauan luas untuk pengiriman tepat waktu ke seluruh Indonesia.</p>
            </div>
        </div>

        <div class="text-center animate-fade-in">
            <h2 class="section-title text-3xl lg:text-4xl mb-4">Siap Berbelanja?</h2>
            <p class="section-subtitle mb-8">Jelajahi koleksi produk kami dan temukan yang Anda butuhkan</p>
            <a href="<?php echo url('customer/products'); ?>" class="btn-premium text-lg px-10 py-4">
                <i class="fas fa-store mr-2"></i> Mulai Belanja
            </a>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>

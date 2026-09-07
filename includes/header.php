<?php require_once dirname(__DIR__) . '/includes/db.php'; ensure_session(); ?>
<header class="navbar-premium">
    <nav class="container mx-auto px-4 lg:px-8">
        <div class="flex justify-between items-center" style="min-height: 70px;">
            <div class="logo flex items-center" style="margin-left: -20px;">
                <a href="<?php echo url(''); ?>">
                    <img src="<?php echo url('assets/images/logo.png'); ?>" alt="MAJESTY" class="logo-king">
                </a>
            </div>

            <div id="navLinks" class="nav-links hidden lg:flex items-center space-x-1 lg:space-x-6">
                <a href="<?php echo url(''); ?>" class="nav-link px-3 py-2 text-sm lg:text-base">Beranda</a>
                <a href="<?php echo url('customer/products'); ?>" class="nav-link px-3 py-2 text-sm lg:text-base">Produk</a>
                <a href="<?php echo url('customer/cart'); ?>" class="nav-link px-3 py-2 text-sm lg:text-base relative">
                    Keranjang
                    <?php
                    if (!empty($_SESSION['cart'])) {
                        $qty = array_sum(array_map(function($item){ return isset($item['qty']) ? (int)$item['qty'] : 1; }, $_SESSION['cart']));
                        echo '<span class="cart-badge">' . $qty . '</span>';
                    }
                    ?>
                </a>
                <?php if (!empty($_SESSION['customer_id'])): ?>
                    <a href="<?php echo url('customer/orders'); ?>" class="nav-link px-3 py-2 text-sm lg:text-base">Pesanan</a>
                    <span class="nav-link px-3 py-2 text-sm lg:text-base text-gold-light">
                        <i class="fas fa-user mr-1"></i><?php echo e($_SESSION['customer_name']); ?>
                    </span>
                    <a href="<?php echo url('customer/logout'); ?>" class="btn-premium-outline text-sm lg:text-base ml-2 lg:ml-4 px-4 lg:px-6 py-1.5 lg:py-2">Logout</a>
                <?php else: ?>
                    <a href="<?php echo url('customer/login'); ?>" class="btn-premium-outline text-sm lg:text-base ml-2 lg:ml-4 px-4 lg:px-6 py-1.5 lg:py-2">Login</a>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-2">
                <button id="themeToggle" class="theme-toggle" aria-label="Mode Terang/Gelap" aria-pressed="false">
                    <i class="fas fa-moon" id="themeToggleIcon"></i>
                </button>
                <button id="navToggle" class="nav-burger flex lg:hidden" aria-label="Menu" aria-expanded="false">
                    <i class="fas fa-bars" id="navToggleIcon"></i>
                </button>
            </div>
        </div>
    </nav>

    <div id="navMobileMenu" class="nav-mobile hidden lg:hidden">
        <a href="<?php echo url(''); ?>" class="nav-link"><i class="fas fa-home w-6"></i>Beranda</a>
        <a href="<?php echo url('customer/products'); ?>" class="nav-link"><i class="fas fa-box w-6"></i>Produk</a>
        <a href="<?php echo url('customer/cart'); ?>" class="nav-link"><i class="fas fa-shopping-cart w-6"></i>Keranjang
            <?php
            if (!empty($_SESSION['cart'])) {
                $qty = array_sum(array_map(function($item){ return isset($item['qty']) ? (int)$item['qty'] : 1; }, $_SESSION['cart']));
                echo '<span class="cart-badge cart-badge-inline">' . $qty . '</span>';
            }
            ?>
        </a>
        <?php if (!empty($_SESSION['customer_id'])): ?>
            <a href="<?php echo url('customer/orders'); ?>" class="nav-link"><i class="fas fa-box-open w-6"></i>Pesanan Saya</a>
            <a href="<?php echo url('customer/logout'); ?>" class="nav-link"><i class="fas fa-sign-out-alt w-6"></i>Logout (<?php echo e($_SESSION['customer_name']); ?>)</a>
        <?php else: ?>
            <a href="<?php echo url('customer/login'); ?>" class="nav-link"><i class="fas fa-user w-6"></i>Login</a>
        <?php endif; ?>
    </div>
</header>

<style>
.navbar-premium a,
.navbar-premium a:focus,
.navbar-premium a:active,
.navbar-premium a:visited,
.navbar-premium .nav-link,
.navbar-premium .nav-link:focus,
.navbar-premium .nav-link:active {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
}

.logo-king {
    max-height: 55px;
    width: auto;
    display: block;
    
    filter: brightness(0) saturate(100%) invert(73%) sepia(98%) saturate(2500%) hue-rotate(1deg) brightness(103%) contrast(101%);
    
    transition: all 0.3s ease;
}

.logo:hover .logo-king {
    transform: scale(1.05);
    filter: brightness(0) saturate(100%) invert(73%) sepia(98%) saturate(3500%) hue-rotate(0deg) brightness(108%) contrast(102%);
}

@keyframes goldGlow {
    0% { 
        filter: brightness(0) saturate(100%) invert(73%) sepia(98%) saturate(2000%) hue-rotate(1deg) brightness(100%) contrast(100%);
    }
    100% { 
        filter: brightness(0) saturate(100%) invert(73%) sepia(98%) saturate(3500%) hue-rotate(1deg) brightness(115%) contrast(105%);
    }
}

.logo-king {
    animation: goldGlow 2s ease-in-out infinite alternate;
}

.logo:hover .logo-king {
    animation: none;
}

.cart-badge {
    position: absolute;
    top: -8px;
    right: -12px;
    background: linear-gradient(135deg, #FFD700, #FFA500);
    color: #000;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 10px;
    font-weight: bold;
    min-width: 18px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.cart-badge-inline {
    position: static;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 20px;
    margin-left: 8px;
    padding: 0 6px;
}

.nav-burger {
    background: transparent;
    border: 2px solid var(--gold);
    color: var(--gold);
    border-radius: 8px;
    width: 42px;
    height: 42px;
    font-size: 1.1rem;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}
.nav-burger:hover {
    background: var(--gold);
    color: var(--navy);
}

.nav-mobile {
    background: linear-gradient(180deg, var(--navy-light) 0%, var(--navy) 100%);
    border-top: 1px solid rgba(201,168,76,0.2);
    box-shadow: 0 12px 30px rgba(15, 27, 45, 0.35);
    padding-bottom: 12px;
}
.nav-mobile .nav-link {
    display: block;
    padding: 16px 24px;
    color: rgba(255,255,255,0.8);
    border-bottom: 1px solid rgba(201,168,76,0.12);
    font-weight: 500;
    transition: background 0.25s ease;
}
.nav-mobile .nav-link:last-child {
    border-bottom: none;
    padding-bottom: 20px;
}
.nav-mobile .nav-link:hover {
    background: rgba(201,168,76,0.08);
    color: var(--gold-light);
}
.nav-mobile .nav-link i {
    color: var(--gold);
    width: 24px;
    text-align: center;
    margin-right: 10px;
}

@media (max-width: 768px) {
    .logo-king {
        max-height: 38px;
    }
    
    .logo {
        margin-left: -6px !important;
    }
}
</style>

<script src="<?php echo url('assets/js/theme.js'); ?>"></script>
<script>
(function() {
    const btn = document.getElementById('navToggle');
    const menu = document.getElementById('navMobileMenu');
    const icon = document.getElementById('navToggleIcon');
    if (!btn || !menu) return;

    function toggle() {
        const open = menu.classList.contains('hidden');
        if (open) {
            menu.classList.remove('hidden');
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-times');
            btn.setAttribute('aria-expanded', 'true');
        } else {
            menu.classList.add('hidden');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
            btn.setAttribute('aria-expanded', 'false');
        }
    }
    btn.addEventListener('click', toggle);
})();
</script>

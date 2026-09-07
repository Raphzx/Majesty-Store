<?php require_once dirname(__DIR__) . '/includes/db.php'; ?>
<footer class="footer-premium mt-16">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 mb-8">
            <div class="col-span-2 lg:col-span-1">
                <div class="footer-logo mb-3"><a href="<?php echo url(''); ?>">Majesty Store</a></div>
                <p class="footer-text leading-relaxed">Platform belanja online terpercaya dengan produk-produk berkualitas terbaik untuk kebutuhan Anda.</p>
                <div class="flex gap-3 mt-4">
                    <a href="#" class="footer-social" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="footer-social" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="footer-social" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" class="footer-social" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            <div>
                <h4 class="footer-heading">Navigasi</h4>
                <ul class="space-y-2">
                    <li><a href="<?php echo url(''); ?>" class="footer-link"><i class="fas fa-chevron-right text-[10px] mr-1"></i> Beranda</a></li>
                    <li><a href="<?php echo url('customer/products'); ?>" class="footer-link"><i class="fas fa-chevron-right text-[10px] mr-1"></i> Produk</a></li>
                    <li><a href="<?php echo url('customer/cart'); ?>" class="footer-link"><i class="fas fa-chevron-right text-[10px] mr-1"></i> Keranjang</a></li>
                </ul>
            </div>
            <div>
                <h4 class="footer-heading">Kontak</h4>
                <ul class="space-y-3 footer-text">
                    <li class="flex items-start gap-2">
                        <i class="fas fa-envelope mt-0.5 text-gold"></i>
                        <span>info@majesty-store.com</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-phone mt-0.5 text-gold"></i>
                        <span>+62 878 2942 6521</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-location-dot mt-0.5 text-gold"></i>
                        <span>Banjarmasin, Indonesia</span>
                    </li>
                </ul>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="flex flex-col md:flex-row justify-between items-center gap-3">
            <p class="footer-bottom">&copy; <?php echo date('Y'); ?> Majesty Store. Semua hak dilindungi.</p>
            <div class="flex gap-4">
                <a href="#" class="footer-link text-xs">Kebijakan Privasi</a>
                <a href="#" class="footer-link text-xs">Syarat & Ketentuan</a>
            </div>
        </div>
    </div>
</footer>
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<footer class="site-footer">
    <div class="footer-container">
        <!-- Kolom 1: Brand & Deskripsi Singkat -->
        <div class="footer-col">
            <div class="site-branding" style="margin-bottom: 12px;">
                <span class="site-title">KadoKita</span>
            </div>
            <p>Gift hampers ready-made dan custom yang praktis, terjangkau, dan bisa disesuaikan dengan budgetmu.</p>
        </div>

        <!-- Kolom 2: Navigasi -->
        <div class="footer-col">
            <h4>Navigasi</h4>
            <ul>
                <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></li>
                <li><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Katalog</a></li>
                <li><a href="<?php echo esc_url( home_url( '/custom-hampers' ) ); ?>">Custom Hampers</a></li>
                <li><a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">Pesanan</a></li>
            </ul>
        </div>

        <!-- Kolom 3: Bantuan -->
        <div class="footer-col">
            <h4>Help</h4>
            <ul>
    <li>
        <a href="<?php echo esc_url( home_url('/contact-us/') ); ?>">
            Contact Us
        </a>
    </li>
    <li>
        <a href="<?php echo esc_url( home_url('/faq/') ); ?>">
            FAQ
        </a>
    </li>
</ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; 2026 KadoKita. All rights reserved.</p>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
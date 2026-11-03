
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
    <div class="header-container">

        <!-- Logo dan Nama Brand -->
        <div class="site-branding">
            <?php
            if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
                the_custom_logo();
            } else {
                echo '<img src="' . esc_url( get_template_directory_uri() . '/assets/images/Logo KadoKita (2).png' ) . '" alt="KadoKita Logo" class="site-logo">';
            }
            ?>

            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-title">
    <?php echo esc_html( get_option( 'kk_nama_toko', 'KadoKita' ) ); ?>
</a>
        </div>

        <!-- Tombol Hamburger untuk Tampilan Mobile -->
        <button
            class="menu-toggle"
            type="button"
            aria-label="Buka menu navigasi"
            aria-expanded="false"
            aria-controls="primary-navigation"
        >
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Menu Navigasi -->
        <nav class="main-navigation" id="primary-navigation">
            <ul>
                <li>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                        Home
                    </a>
                </li>

                <li>
                    <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
                        Katalog
                    </a>
                </li>

                <li>
                    <a href="<?php echo esc_url( home_url( '/custom-hampers' ) ); ?>">
                        Custom Hampers
                    </a>
                </li>

                <li>
                    <a href="<?php echo esc_url(
                    is_user_logged_in()
                    ? home_url('/dashboard-customer/')
                    : wc_get_page_permalink('myaccount')
                    ); ?>">
                    Order
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Ikon Akun dan Keranjang -->
        <div class="header-icons">

            
<a
    href="<?php
        if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
            echo esc_url( home_url( '/dashboard-admin-kadokita/' ) );
        } elseif ( is_user_logged_in() ) {
            echo esc_url( home_url( '/dashboard-customer/' ) );
        } else {
            echo esc_url( wc_get_page_permalink( 'myaccount' ) );
        }
    ?>"
    title="Akun Saya"
>

                <svg
                    width="18"
                    height="18"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Masuk</span>
            </a>

            <a
                href="<?php echo esc_url( wc_get_cart_url() ); ?>"
                title="Keranjang Belanja"
            >
                <svg
                    width="18"
                    height="18"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>

                <?php
                if ( class_exists( 'WooCommerce' ) && WC()->cart ) :
                ?>
                    <span class="cart-count">
                        <?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?>
                    </span>
                <?php endif; ?>
            </a>

        </div>
    </div>
</header>

<!-- JavaScript Hamburger Menu -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.main-navigation');

    if (!toggle || !nav) {
        return;
    }

    toggle.addEventListener('click', function () {
        const isOpen = nav.classList.toggle('is-open');

        toggle.setAttribute('aria-expanded', String(isOpen));
        toggle.setAttribute(
            'aria-label',
            isOpen ? 'Tutup menu navigasi' : 'Buka menu navigasi'
        );
    });

    nav.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            nav.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', 'Buka menu navigasi');
        });
    });
});
</script>
</body>
</html>

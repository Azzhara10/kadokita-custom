<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Keluar jika diakses langsung
}

function kadokita_setup() {
    // Dukungan judul otomatis
    add_theme_support( 'title-tag' );
    
    // Dukungan Featured Image / Thumbnail produk
    add_theme_support( 'post-thumbnails' );

    // Dukungan WooCommerce resmi
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'kadokita_setup' );

function kadokita_scripts() {
    // Memuat file style.css utama tema
    wp_enqueue_style(
        'kadokita-style',
        get_stylesheet_uri(),
        array(),
        '1.0.0'
    );

    
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
        array(),
        '6.5.0'
    );

    // CSS khusus Dashboard Customer
    if ( is_page_template( 'page-dashboard-customer.php' ) ) {
        wp_enqueue_style(
            'kk-dashboard-customer',
            get_template_directory_uri() . '/dashboard-customer.css',
            array( 'kadokita-style' ),
            '1.0.0'
        );
    }
}

add_action( 'wp_enqueue_scripts', 'kadokita_scripts' );


/**
 * KadoKita: sembunyikan produk Custom Hampers
 * dari halaman Katalog utama.
 */
add_action( 'pre_get_posts', 'kadokita_filter_shop_products' );

function kadokita_filter_shop_products( $query ) {

    if (
        is_admin() ||
        ! $query->is_main_query() ||
        ! function_exists( 'is_shop' ) ||
        ! is_shop()
    ) {
        return;
    }

    $custom_parent = get_term_by(
        'slug',
        'custom-gift-hampers',
        'product_cat'
    );

    if ( ! $custom_parent || is_wp_error( $custom_parent ) ) {
        return;
    }

    // Ambil kategori utama dan seluruh subkategorinya.
    $excluded_ids = array( (int) $custom_parent->term_id );

    $children = get_term_children(
        $custom_parent->term_id,
        'product_cat'
    );

    if ( ! is_wp_error( $children ) ) {
        $excluded_ids = array_merge(
            $excluded_ids,
            array_map( 'intval', $children )
        );
    }

    // Keluarkan kategori Custom Hampers dari katalog utama.
    $tax_query = (array) $query->get( 'tax_query' );

    $tax_query[] = array(
        'taxonomy'         => 'product_cat',
        'field'            => 'term_id',
        'terms'            => array_unique( $excluded_ids ),
        'operator'         => 'NOT IN',
        'include_children' => false,
    );

    $query->set( 'tax_query', $tax_query );
}






/**
 * KadoKita: proses pilihan Custom Hampers
 * dan tambahkan produk ke keranjang WooCommerce.
 */
add_action( 'template_redirect', 'kadokita_process_custom_hampers' );

function kadokita_process_custom_hampers() {

    if (
        ! isset( $_POST['kadokita_custom_hampers_submit'] ) ||
        ! is_page( 'custom-hampers' )
    ) {
        return;
    }

    if (
        ! isset( $_POST['kadokita_custom_hampers_nonce'] ) ||
        ! wp_verify_nonce(
            sanitize_text_field(
                wp_unslash( $_POST['kadokita_custom_hampers_nonce'] )
            ),
            'kadokita_custom_hampers'
        )
    ) {
        wp_die( 'Permintaan tidak valid. Silakan kembali dan coba lagi.' );
    }

    if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
        wp_die( 'Keranjang WooCommerce belum tersedia. Silakan coba lagi.' );
    }

    $allowed_categories = array(
        'accessories',
        'fashion',
        'greeting-cards',
        'lifestyle',
        'polaroid-photos',
        'self-care',
        'snacks',
        'stationery',
    );

    $selected_ids = isset( $_POST['kadokita_products'] )
        ? array_map(
            'absint',
            (array) wp_unslash( $_POST['kadokita_products'] )
        )
        : array();

    $selected_ids = array_values(
        array_unique(
            array_filter( $selected_ids )
        )
    );

    $packaging_id = isset( $_POST['kadokita_packaging'] )
        ? absint( $_POST['kadokita_packaging'] )
        : 0;

    if ( empty( $selected_ids ) ) {
        wp_safe_redirect(
            add_query_arg( 'custom_hampers_error', 'empty', get_permalink() )
        );
        exit;
    }

    if ( ! $packaging_id || ! has_term( 'packaging', 'product_cat', $packaging_id ) ) {
        wp_safe_redirect(
            add_query_arg( 'custom_hampers_error', 'packaging', get_permalink() )
        );
        exit;
    }

    $products_to_add = array();

    foreach ( $selected_ids as $product_id ) {

        $product = wc_get_product( $product_id );

        if (
            ! $product ||
            ! $product->is_purchasable() ||
            ! $product->is_in_stock() ||
            ! has_term(
                $allowed_categories,
                'product_cat',
                $product_id
            )
        ) {
            wp_safe_redirect(
                add_query_arg( 'custom_hampers_error', 'product', get_permalink() )
            );
            exit;
        }

        $products_to_add[] = $product_id;
    }

    $packaging = wc_get_product( $packaging_id );

    if (
        ! $packaging ||
        ! $packaging->is_purchasable() ||
        ! $packaging->is_in_stock()
    ) {
        wp_safe_redirect(
            add_query_arg( 'custom_hampers_error', 'packaging', get_permalink() )
        );
        exit;
    }

    // Identitas untuk membedakan pilihan Custom Hampers ini di keranjang.
    $group_id = wp_generate_uuid4();
    $added = 0;

    foreach ( $products_to_add as $product_id ) {

        
$quantity = isset( $_POST['kadokita_quantities'][ $product_id ] )
    ? absint( wp_unslash( $_POST['kadokita_quantities'][ $product_id ] ) )
    : 1;

$quantity = max( 1, min( 99, $quantity ) );

$result = WC()->cart->add_to_cart(
    $product_id,
    $quantity,
    0,
    array(),
    array(
        'kadokita_custom_hampers' => $group_id,
        'kadokita_custom_label'   => 'Custom Hampers',
    )
);


        if ( $result ) {
            $added++;
        }
    }

    // Kemasan ikut dimasukkan sebagai item terpisah.
    $packaging_result = WC()->cart->add_to_cart(
        $packaging_id,
        1,
        0,
        array(),
        array(
            'kadokita_custom_hampers' => $group_id,
            'kadokita_custom_label'   => 'Kemasan Custom Hampers',
        )
    );

    if ( $packaging_result && $added > 0 ) {
        wp_safe_redirect( wc_get_cart_url() );
        exit;
    }

    wp_safe_redirect(
        add_query_arg( 'custom_hampers_error', 'failed', get_permalink() )
    );
    exit;
}



/* Notifikasi Add to Cart KadoKita */
add_action( 'wp_footer', function () {
    if ( is_admin() ) {
        return;
    }
    ?>
    <div class="kk-cart-notification" id="kk-cart-notification"
         role="status" aria-live="polite">
        <div class="kk-cart-notification-icon">✓</div>

        <div>
            <p class="kk-cart-notification-title">
                Berhasil ditambahkan!
            </p>
            <p class="kk-cart-notification-message">
                Produk berhasil ditambahkan ke keranjang kamu.
            </p>
        </div>

        <button type="button"
                class="kk-cart-notification-close"
                id="kk-cart-notification-close"
                aria-label="Tutup notifikasi">&times;</button>
    </div>

    <script>
    jQuery(function ($) {
        const notification = $('#kk-cart-notification');
        let notificationTimer;

        function showCartNotification() {
            clearTimeout(notificationTimer);

            notification.addClass('is-visible');

            notificationTimer = setTimeout(function () {
                notification.removeClass('is-visible');
            }, 3500);
        }

        // Muncul setelah WooCommerce berhasil menambahkan produk via AJAX.
        $(document.body).on('added_to_cart', function () {
            showCartNotification();
        });

        // Tombol untuk menutup notifikasi secara manual.
        $('#kk-cart-notification-close').on('click', function () {
            clearTimeout(notificationTimer);
            notification.removeClass('is-visible');
        });
    });
    </script>
    <?php
} );



/* Sembunyikan deskripsi produk di halaman keranjang */
add_filter( 'woocommerce_get_item_data', function ( $item_data, $cart_item ) {
    return array_filter( $item_data, function ( $item ) {
        $label = isset( $item['key'] ) ? strtolower( wp_strip_all_tags( $item['key'] ) ) : '';
        $value = isset( $item['value'] ) ? strtolower( wp_strip_all_tags( $item['value'] ) ) : '';

        return ! (
            strpos( $label, 'deskripsi' ) !== false ||
            strpos( $label, 'description' ) !== false
        );
    } );
}, 10, 2 );



/* Arahkan pengguna yang belum login ke halaman login sebelum checkout */
add_action( 'template_redirect', function () {
    if (
        function_exists( 'is_checkout' ) &&
        is_checkout() &&
        ! is_user_logged_in() &&
        ! is_wc_endpoint_url( 'order-received' )
    ) {
        $login_url = add_query_arg(
            'redirect_to',
            rawurlencode( wc_get_checkout_url() ),
            wc_get_page_permalink( 'myaccount' )
        );

        wp_safe_redirect( $login_url );
        exit;
    }
} );

/* Setelah login berhasil, arahkan pengguna kembali ke checkout */
add_filter( 'woocommerce_login_redirect', function ( $redirect, $user ) {
    if (
        isset( $_GET['redirect_to'] ) &&
        is_string( $_GET['redirect_to'] ) &&
        strpos(
            wp_unslash( $_GET['redirect_to'] ),
            wc_get_checkout_url()
        ) === 0
    ) {
        return wc_get_checkout_url();
    }

    return $redirect;
}, 10, 2 );



/* Redirect setelah login WooCommerce */
add_filter( 'woocommerce_login_redirect', function ( $redirect, $user ) {

    if ( ! $user || is_wp_error( $user ) ) {
        return $redirect;
    }

    // Administrator menuju Dashboard Admin.
    if ( in_array( 'administrator', (array) $user->roles, true ) ) {
        return home_url( '/dashboard-admin-kadokita/' );
    }

    // Customer menuju Dashboard Customer.
    return home_url( '/dashboard-customer/' );

}, 20, 2 );


/* Arahkan pengguna baru ke Checkout setelah mendaftar */
add_filter( 'woocommerce_registration_redirect', function ( $redirect ) {
    if (
        isset( $_GET['redirect_to'] ) &&
        is_string( $_GET['redirect_to'] )
    ) {
        $target = wp_validate_redirect(
            wp_unslash( $_GET['redirect_to'] ),
            ''
        );

        if (
            $target &&
            strpos( $target, wc_get_checkout_url() ) === 0
        ) {
            return wc_get_checkout_url();
        }
    }

    return $redirect;
} );





/* Tampilan Login dan Register KadoKita */
add_action( 'wp_footer', function () {
    if (
        ! function_exists( 'is_account_page' ) ||
        ! is_account_page() ||
        is_user_logged_in()
    ) {
        return;
    }
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const columns = document.querySelector(
            '.woocommerce-account .u-columns'
        );

        if (!columns || columns.dataset.kkAuthReady) return;

        const login = columns.querySelector('.u-column1');
        const register = columns.querySelector('.u-column2');

        if (!login || !register) return;

        columns.dataset.kkAuthReady = 'true';

        const container = columns.closest('.woocommerce');

        const header = document.createElement('div');
        header.className = 'kk-auth-header';
        
header.innerHTML = `
    <h1 class="kk-auth-brand">KadoKita</h1>
    <p class="kk-auth-subtitle">
        Masuk atau buat akun untuk melanjutkan pesananmu.
    </p>
`;


        const tabs = document.createElement('div');
        tabs.className = 'kk-auth-tabs';
        tabs.innerHTML = `
            <button type="button"
                class="kk-auth-tab is-active"
                data-panel="login">Login</button>
            <button type="button"
                class="kk-auth-tab"
                data-panel="register">Register</button>
        `;

        container.insertBefore(header, columns);
        columns.parentNode.insertBefore(tabs, columns);

        login.classList.add('kk-auth-panel', 'is-active');
        register.classList.add('kk-auth-panel');

        function activateTab(panelName) {
            const showLogin = panelName === 'login';

            tabs.querySelectorAll('.kk-auth-tab').forEach(function (tab) {
                tab.classList.toggle(
                    'is-active',
                    tab.dataset.panel === panelName
                );
            });

            login.classList.toggle('is-active', showLogin);
            register.classList.toggle('is-active', !showLogin);
        }

        tabs.addEventListener('click', function (event) {
            const button = event.target.closest('.kk-auth-tab');
            if (button) activateTab(button.dataset.panel);
        });

        if (new URLSearchParams(window.location.search).has('register')) {
            activateTab('register');
        }
    });
    </script>
    <?php
} );

/* Hilangkan keterangan password pada form Register KadoKita */
add_filter( 'gettext', function ( $translated, $text, $domain ) {
    if (
        $domain === 'woocommerce' &&
        $text === 'A link to set a new password will be sent to your email address.'
    ) {
        return '';
    }

    return $translated;
}, 20, 3 );





/* Redirect setelah customer berhasil registrasi */
add_filter( 'woocommerce_registration_redirect', function ( $redirect ) {
    return home_url( '/dashboard-customer/' );
} );
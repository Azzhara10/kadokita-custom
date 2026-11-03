
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<main class="site-main kk-catalog"
      style="padding: 40px 20px; max-width: 1200px; margin: 0 auto;">

    <?php if ( is_shop() ) : ?>

        <!-- Pilihan kategori hanya tampil di halaman katalog utama -->
        <section class="kk-catalog-categories">
            <h2>Kategori Hampers</h2>
            <p>Pilih kategori sesuai momen spesialmu.</p>

            <div class="kk-category-list">
                <?php
                $categories = array(
                    'birthday-hampers'     => 'Birthday',
                    'graduation-hampers'   => 'Graduation',
                    'appreciation-hampers' => 'Appreciation',
                );

                foreach ( $categories as $slug => $label ) :
                    $term = get_term_by(
                        'slug',
                        $slug,
                        'product_cat'
                    );

                    if ( ! $term || is_wp_error( $term ) ) {
                        continue;
                    }

                    $link = get_term_link( $term );

                    if ( is_wp_error( $link ) ) {
                        continue;
                    }
                ?>
                    <a class="kk-category-link"
                       href="<?php echo esc_url( $link ); ?>">
                        <?php echo esc_html( $label ); ?>
                        <span aria-hidden="true">→</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

    <?php endif; ?>

    <!-- Judul halaman WooCommerce -->
    <header class="woocommerce-products-header"
            style="margin-bottom: 30px;">

        <?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
            <h1 class="woocommerce-products-header__title page-title"
                style="font-size: 26px; font-weight: 600; color: var(--dark-brown);">
                <?php woocommerce_page_title(); ?>
            </h1>
        <?php endif; ?>

        <?php do_action( 'woocommerce_archive_description' ); ?>

    </header>

    <!-- Daftar produk tetap menggunakan WooCommerce -->
    <?php
    if ( woocommerce_product_loop() ) {

        do_action( 'woocommerce_before_shop_loop' );

        woocommerce_product_loop_start();

        if ( wc_get_loop_prop( 'total' ) ) {
            while ( have_posts() ) {
                the_post();

                do_action( 'woocommerce_shop_loop' );

                wc_get_template_part( 'content', 'product' );
            }
        }

        woocommerce_product_loop_end();

        do_action( 'woocommerce_after_shop_loop' );

    } else {
        do_action( 'woocommerce_no_products_found' );
    }
    ?>

</main>

<style>
.kk-catalog-categories {
    margin-bottom: 35px;
}

.kk-catalog-categories h2 {
    margin: 0 0 8px;
    color: #3E3033;
    font-size: 23px;
}

.kk-catalog-categories p {
    margin: 0 0 18px;
    color: #6C5A5E;
    font-size: 14px;
}

.kk-category-list {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.kk-category-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 12px 20px;
    border: 1px solid #DFA6B8;
    border-radius: 8px;
    background: #FFF8F0;
    color: #8F3D56;
    font-size: 14px;
    text-decoration: none;
    transition: background .2s, color .2s;
}

.kk-category-link:hover {
    background: #8F3D56;
    color: #FFF8F0;
}

.kk-catalog ul.products li.product .button {
    background: #8F3D56;
    color: #FFF8F0;
    border-radius: 6px;
}

.kk-catalog ul.products li.product .button:hover {
    background: #713047;
    color: #FFF8F0;
}

@media (max-width: 600px) {
    .kk-category-list {
        gap: 8px;
    }

    .kk-category-link {
        padding: 10px 14px;
        font-size: 13px;
    }
}
</style>

<?php get_footer(); ?>

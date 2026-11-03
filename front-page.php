
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<main class="site-main">

    <!-- 1. Hero Section -->
    <section class="hero-section">
        <div class="hero-container">

            <div class="hero-content">
                <h1>Hadiah Spesial, Pilihanmu Sendiri</h1>

                <p>
                    Temukan gift hampers untuk setiap momen, atau buat sendiri
                    sesuai keinginan dan budget kamu.
                </p>

                <div class="hero-buttons">
                    <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"
                       class="btn-burgundy">
                        Lihat Hampers
                    </a>

                    <a href="<?php echo esc_url( home_url( '/custom-hampers' ) ); ?>"
                       class="btn-outline">
                        Buat Hampers
                    </a>
                </div>
            </div>

            <div class="hero-image">
                <img
                    src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/hero-box(1).jpg' ); ?>"
                    alt="Kotak Hampers KadoKita"
                >
            </div>

        </div>
    </section>


    <!-- 2. Kategori Ready-Made Hampers -->
    <section class="categories-section">

        <div class="section-title">
            <h2>Ready-Made Hampers</h2>
            <p>Pilih hampers sesuai momen spesialmu</p>
        </div>

        <div class="category-grid-3">

            <!-- Birthday Hampers -->
            <div class="category-card">
                <a href="<?php echo esc_url( get_term_link( 'birthday-hampers', 'product_cat' ) ); ?>">

                    <svg width="48" height="48"
                         fill="none" stroke="#8F3D56"
                         stroke-width="2" viewBox="0 0 24 24"
                         aria-hidden="true">
                        <path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
                    </svg>

                    <h3>Birthday</h3>
                    <p>Rayakan hari spesial dengan hadiah berkesan</p>

                </a>
            </div>


            <!-- Graduation Hampers -->
            <div class="category-card">
                <a href="<?php echo esc_url( get_term_link( 'graduation-hampers', 'product_cat' ) ); ?>">

                    <svg width="48" height="48"
                         fill="none" stroke="#8F3D56"
                         stroke-width="2" viewBox="0 0 24 24"
                         aria-hidden="true">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5zM6 12v5c0 2 3 3 6 3s6-1 6-3v-5"/>
                    </svg>

                    <h3>Graduation</h3>
                    <p>Hadiah untuk merayakan momen kelulusan</p>

                </a>
            </div>


            <!-- Appreciation Hampers -->
            <div class="category-card">
                <a href="<?php echo esc_url( get_term_link( 'appreciation-hampers', 'product_cat' ) ); ?>">

                    <svg width="48" height="48"
                         fill="none" stroke="#8F3D56"
                         stroke-width="2" viewBox="0 0 24 24"
                         aria-hidden="true">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>

                    <h3>Appreciation</h3>
                    <p>Tunjukkan rasa terima kasih dan perhatian</p>

                </a>
            </div>

        </div>
    </section>


    <!-- 3. Rekomendasi Ready-Made Hampers -->
    <section class="products-section">

        <div class="section-title" style="text-align: left;">
            <h2>Rekomendasi Ready-Made Hampers</h2>
        </div>

        <?php
        echo do_shortcode(
    '[products limit="4" columns="4" category="birthday-hampers,graduation-hampers,appreciation-hampers" orderby="date" order="DESC"]'
);
        ?>

    </section>


    <!-- 4. Banner Custom Hampers -->
    <section class="custom-cta-section">

        <div class="custom-cta-box">

            <div class="custom-cta-text">
                <h3>Ingin buat hampers sesuai budget &amp; selera?</h3>

                <p>
                    Pilih sendiri isi hampers mulai dari berbagai pilihan
                    hadiah dan snack hingga kartu ucapan custom.
                    Atur budget sesuai keinginan tanpa perlu khawatir!
                </p>
            </div>

            <div>
                <a href="<?php echo esc_url( home_url( '/custom-hampers' ) ); ?>"
                   class="btn-burgundy"
                   style="display: inline-flex; align-items: center; gap: 8px;">

                    <svg width="16" height="16"
                         fill="none" stroke="currentColor"
                         stroke-width="2" viewBox="0 0 24 24"
                         aria-hidden="true">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>

                    Mulai Buat Custom Hampers

                </a>
            </div>

        </div>
    </section>

</main>

<?php
get_footer();
?>

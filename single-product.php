<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<main class="site-main" style="padding: 40px 20px; max-width: 1200px; margin: 0 auto;">
    <?php
        while ( have_posts() ) :
            the_post();
            wc_get_template_part( 'content', 'single-product' );
        endwhile;
    ?>
</main>

<?php
get_footer();
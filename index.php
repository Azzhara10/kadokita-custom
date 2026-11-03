<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<main class="site-main" style="padding: 40px 20px; max-width: 1200px; margin: 0 auto; min-height: 60vh;">
    <div class="content-area">
        <?php
        if ( have_posts() ) :
            while ( have_posts() ) :
                the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="margin-bottom: 40px;">
                    <header class="entry-header" style="margin-bottom: 15px;">
                        <h2 style="font-size: 22px; font-weight: 600; color: var(--dark-brown);">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                    </header>
                    <div class="entry-content" style="font-size: 14px; color: #555;">
                        <?php the_excerpt(); ?>
                    </div>
                </article>
                <?php
            endwhile;

            the_posts_pagination();
        else :
            ?>
            <p><?php esc_html_e( 'Maaf, tidak ada konten yang ditemukan.', 'kadokita-custom' ); ?></p>
            <?php
        endif;
        ?>
    </div>
</main>

<?php
get_footer();
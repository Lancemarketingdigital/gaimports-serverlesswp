<?php
/**
 * O template para exibir páginas estáticas (Single Page).
 *
 * @package Fast_WebX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$enable_sidebar = fwx_get_option( 'sidebar_pages', 0 );
$layout_class   = $enable_sidebar ? 'has-sidebar' : 'no-sidebar';
?>

<div class="fwx-container fwx-single-layout <?php echo esc_attr( $layout_class ); ?>">
    <main id="primary" class="site-main fwx-page-content">
        <?php
        while ( have_posts() ) :
            the_post();
            ?>
            <?php 
            if ( fwx_get_option( 'show_page_breadcrumb', 0 ) ) :
                fwx_breadcrumbs(); 
            endif;
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                
                <?php if ( get_post_meta( get_the_ID(), '_fwx_hide_title', true ) !== '1' ) : ?>
                    <header class="entry-header">
                        <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                    </header>
                <?php endif; ?>

                <?php
                if ( ! fwx_get_option( 'hide_page_thumb', 0 ) && has_post_thumbnail() ) : ?>
                    <div class="entry-thumbnail fwx-hero-thumb">
                        <?php the_post_thumbnail( 'full', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
                    </div>
                <?php endif; ?>

                <div class="entry-content">
                    <?php
                    // Páginas não geram TOC por padrão para foco em conteúdo
                    the_content();
                    
                    wp_link_pages( array(
                        'before' => '<div class="page-links">' . esc_html( fwx_t( 'single_pages' ) ),
                        'after'  => '</div>',
                    ) );
                    ?>
                </div><!-- .entry-content -->
            </article>
            <?php
        endwhile;
        ?>
    </main>

    <?php 
    $show_page_sidebar = $enable_sidebar;
    if ( fwx_get_option( 'hide_sidebar_mobile', 0 ) && wp_is_mobile() ) {
        $show_page_sidebar = false;
    }
    if ( $show_page_sidebar ) : 
    ?>
        <aside id="secondary" class="widget-area fwx-sidebar">
            <?php fwx_render_sidebar(); ?>
        </aside>
    <?php endif; ?>
</div>

<?php
get_footer();

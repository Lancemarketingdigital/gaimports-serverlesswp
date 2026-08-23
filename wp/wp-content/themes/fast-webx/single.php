<?php
/**
 * O template para exibir posts individuais (Single Post).
 * Hiper-Otimizado para retenção e SEO.
 *
 * @package Fast_WebX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$enable_sidebar = fwx_get_option( 'sidebar_posts', 0 );
$layout_class   = $enable_sidebar ? 'has-sidebar' : 'no-sidebar';
$hide_thumb     = fwx_get_option( 'hide_single_thumb', 0 );
$reading_bar    = fwx_get_option( 'enable_reading_progress', 0 );
$single_layout  = fwx_get_option( 'single_post_layout', 'layout-1' );

// Se for layout-2, a classe base muda ligeiramente
if ( 'layout-2' === $single_layout ) {
    $layout_class .= ' fwx-single-layout-2';
}

?>

<!-- Reading Progress Bar via CSS -->
<?php if ( $reading_bar ) : ?>
    <div class="fwx-reading-progress"></div>
<?php endif; ?>

<?php
while ( have_posts() ) :
    the_post();
    
    // ANÚNCIO: TOPO ABSOLUTO (Ads 1)
    // Injetado antes de qualquer processamento de layout para respeitar a hierarquia do painel.
    do_action('fwx_before_post_top');
    
    // IMPACT HERO para Layout 2
    if ( 'layout-2' === $single_layout ) :
        ?>
        <header class="fwx-single-header-2">
            <?php 
            $categories = get_the_category();
            if ( ! empty( $categories ) ) {
                echo '<a href="' . esc_url( get_category_link( $categories[0]->term_id ) ) . '" class="fwx-cat-label fwx-cat-id-' . esc_attr( $categories[0]->term_id ) . '" rel="category tag">' . esc_html( $categories[0]->name ) . '</a>';
            }
            if ( fwx_get_option( 'show_single_breadcrumb', 0 ) ) :
                fwx_breadcrumbs();
            endif;
            ?>
            <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
            <?php if ( has_excerpt() && fwx_get_option( 'show_single_excerpt', 0 ) ) : ?>
                <div class="entry-excerpt"><?php the_excerpt(); ?></div>
            <?php endif; ?>
            <div class="entry-meta">
                <span class="fwx-author"><?php echo esc_html( fwx_t( 'single_author_prefix' ) ); ?> <?php the_author(); ?></span>
                <span class="fwx-date"><?php echo get_the_date(); ?></span>
                <span class="fwx-reading-time"><?php echo fwx_get_reading_time( get_the_content() ); ?></span>
            </div>
        </header>

        <?php if ( ! $hide_thumb ) : ?>
            <div class="fwx-hero-wide-thumb">
                <?php fwx_the_post_thumbnail( null, 'full', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
            </div>
        <?php endif; ?>
        <?php
    endif;
?>

<div class="fwx-container fwx-single-layout <?php echo esc_attr( $layout_class ); ?>">
    <main id="primary" class="site-main fwx-single-content">
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            
            <?php if ( 'layout-1' === $single_layout ) : ?>
                <header class="entry-header">
                    <?php 
                    $categories = get_the_category();
                    if ( ! empty( $categories ) ) {
                        echo '<a href="' . esc_url( get_category_link( $categories[0]->term_id ) ) . '" class="fwx-cat-label fwx-cat-id-' . esc_attr( $categories[0]->term_id ) . '" rel="category tag">' . esc_html( $categories[0]->name ) . '</a>';
                    }
                    if ( fwx_get_option( 'show_single_breadcrumb', 0 ) ) :
                        fwx_breadcrumbs();
                    endif;
                    ?>
                    
                    <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                    
                    <div class="entry-meta">
                        <span class="fwx-author"><?php echo esc_html( fwx_t( 'single_author_prefix' ) ); ?> <?php the_author(); ?></span> | 
                        <span class="fwx-date"><?php echo get_the_date(); ?></span> | 
                        <span class="fwx-reading-time"><?php echo fwx_get_reading_time( get_the_content() ); ?></span>
                    </div>

                    <?php if ( has_excerpt() && fwx_get_option( 'show_single_excerpt', 0 ) ) : ?>
                        <div class="entry-excerpt"><?php the_excerpt(); ?></div>
                    <?php endif; ?>
                </header>

                <?php if ( ! $hide_thumb ) : ?>
                    <div class="entry-thumbnail fwx-hero-thumb">
                        <?php fwx_the_post_thumbnail( null, 'fwx-single-thumb', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
                    </div>
                <?php endif; ?>
            <?php endif; // Fim do layout-1 header ?>

                <div class="entry-content">
                    <?php
                    // O TOC automático é injetado via filtro `the_content` (Fase 3).
                    
                    // Injeta o Leitor de Áudio (TTS) se ativado
                    if ( fwx_get_option( 'enable_tts', 0 ) ) {
                        echo '<div class="fwx-tts-wrapper">';
                        echo '    <div id="fwx-tts-player" class="fwx-tts-player">';
                        echo '        <button id="fwx-tts-play" class="fwx-tts-control" aria-label="Play/Pause">';
                        echo '            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>';
                        echo '        </button>';
                        echo '        <button id="fwx-tts-stop" class="fwx-tts-control fwx-tts-stop" aria-label="Stop">';
                        echo '            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect></svg>';
                        echo '        </button>';
                        echo '        <div class="fwx-tts-progress-container">';
                        echo '            <div id="fwx-tts-progress-fill" class="fwx-tts-progress-fill"></div>';
                        echo '        </div>';
                        echo '        <div class="fwx-tts-label">Ouvir</div>';
                        echo '    </div>';
                        echo '</div>';
                    }

                    the_content();
                    
                    wp_link_pages( array(
                        'before' => '<div class="page-links">' . esc_html( fwx_t( 'single_pages' ) ),
                        'after'  => '</div>',
                    ) );
                    
                    if ( fwx_get_option( 'show_single_tags', 0 ) ) {
                        the_tags( '<div class="fwx-post-tags"><strong>' . esc_html( fwx_t( 'single_tags' ) ) . '</strong>', '', '</div>' );
                    }
                    ?>
                </div><!-- .entry-content -->

                <footer class="entry-footer">
                    <!-- Share Buttons (Zero JS) -->
                    <?php echo do_shortcode( '[fwx_share_buttons]' ); ?>

                    <!-- Author Box E-E-A-T -->
                    <?php echo do_shortcode( '[fwx_author_box]' ); ?>
                </footer><!-- .entry-footer -->
                
            </article>
            <?php fwx_related_posts(); ?>
            <?php
            
            // Comentários (Carregado apenas se habilitado globalmente e no post)
            if ( ! fwx_get_option( 'disable_comments', 0 ) ) :
                if ( comments_open() || get_comments_number() ) :
                    comments_template();
                endif;
            endif;
            ?>
    </main>

    <?php 
    $show_single_sidebar = $enable_sidebar;
    if ( fwx_get_option( 'hide_sidebar_mobile', 0 ) && wp_is_mobile() ) {
        $show_single_sidebar = false;
    }
    if ( $show_single_sidebar ) : 
    ?>
        <aside id="secondary" class="widget-area fwx-sidebar">
            <?php fwx_render_sidebar(); ?>
        </aside>
    <?php endif; ?>
</div>

<?php endwhile; ?>

<?php
get_footer();

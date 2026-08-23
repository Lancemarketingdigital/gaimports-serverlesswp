<?php
/**
 * Footer do Fast WebX
 *
 * @package Fast_WebX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

	<?php 
	$hide_footer = get_post_meta( get_the_ID(), '_fwx_hide_footer', true );
	if ( '1' !== $hide_footer ) : 
	?>
	<footer id="colophon" class="site-footer">
        <div class="footer-top fwx-container">
            <div class="footer-col col-brand">
                <h3 class="footer-title"><?php echo esc_html( fwx_t( 'footer_about' ) ); ?></h3>
                <p class="footer-description">
                    <?php 
                    $footer_desc = fwx_get_option( 'footer_desc', '<strong>[nome_site]</strong><br>[fwx_site_desc]' );
                    $footer_desc = str_replace( '[nome_site]', get_bloginfo( 'name' ), $footer_desc );
                    echo do_shortcode( $footer_desc ); 
                    ?>
                </p>
            </div>

            <div class="footer-col col-categories">
                <h3 class="footer-title"><?php echo esc_html( fwx_t( 'footer_links' ) ); ?></h3>
                <?php if ( has_nav_menu( 'menu-footer-2' ) ) : ?>
                    <?php
                    wp_nav_menu( array(
                        'theme_location' => 'menu-footer-2',
                        'container'      => false,
                        'menu_class'     => 'footer-links',
                        'depth'          => 1,
                        'fallback_cb'    => false,
                    ) );
                    ?>
                <?php else : ?>
                    <ul class="footer-links">
                        <?php
                        $categories = get_categories( array(
                            'orderby' => 'count',
                            'order'   => 'DESC',
                            'number'  => 6,
                        ) );
                        foreach ( $categories as $category ) {
                            echo '<li><a href="' . esc_url( get_category_link( $category->term_id ) ) . '">' . esc_html( $category->name ) . '</a></li>';
                        }
                        ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="footer-col col-nav">
                <h3 class="footer-title"><?php echo esc_html( fwx_t( 'footer_site' ) ); ?></h3>
                <?php if ( has_nav_menu( 'menu-footer' ) ) : ?>
                    <?php
                    wp_nav_menu( array(
                        'theme_location' => 'menu-footer',
                        'container'      => false,
                        'menu_class'     => 'footer-links',
                        'depth'          => 1,
                        'fallback_cb'    => false,
                    ) );
                    ?>
                <?php else : ?>
                    <ul class="footer-links">
                        <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( fwx_t( 'footer_home' ) ); ?></a></li>
                        <li><a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><?php echo esc_html( fwx_t( 'footer_setup_menu' ) ); ?></a></li>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="footer-col col-social">
                <h3 class="footer-title"><?php echo esc_html( fwx_t( 'footer_follow' ) ); ?></h3>
                <?php 
                $footer_social = fwx_get_option( 'footer_social', '[fwx_social_links]' );
                echo do_shortcode( $footer_social ); 
                ?>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="fwx-container footer-bottom-inner">
                <div class="footer-copyright">
                    <?php 
                    $copyright = fwx_get_option( 'footer_text', fwx_t( 'footer_text_default' ) );
                    $copyright = str_replace( '[ano]', date( 'Y' ), $copyright );
                    $copyright = str_replace( '[nome_site]', get_bloginfo( 'name' ), $copyright );
                    echo wp_kses_post( do_shortcode( $copyright ) );
                    ?>
                </div>
                <div class="footer-aux">
                    <?php echo wp_kses_post( fwx_get_option( 'footer_aux_text', fwx_t( 'footer_aux_text_default' ) ) ); ?>
                </div>
            </div>
        </div>
	</footer><!-- #colophon -->
	<?php endif; ?>
</div><!-- #page -->

    <!-- Exit Intent Popup -->
    <?php if ( fwx_get_option( 'enable_exit_intent', 0 ) ) : ?>
    <div id="fwx-exit-popup" class="fwx-exit-popup" aria-hidden="true" data-frequency="<?php echo esc_attr( fwx_get_option( 'exit_intent_frequency', 'always' ) ); ?>">
        <div class="fwx-exit-popup-overlay"></div>
        <div class="fwx-exit-popup-content">
            <button class="fwx-exit-popup-close" aria-label="<?php echo esc_attr( fwx_t( 'footer_close' ) ); ?>">&times;</button>
            <div class="fwx-exit-popup-body">
                <h2 class="fwx-exit-popup-title"><?php echo esc_html( fwx_get_option( 'exit_intent_title', fwx_t( 'exit_intent_title_default' ) ) ); ?></h2>
                <div class="fwx-exit-popup-desc">
                    <?php echo do_shortcode( fwx_get_option( 'exit_intent_desc', fwx_t( 'exit_intent_desc_default' ) ) ); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ( fwx_get_option( 'enable_back_to_top', 0 ) ) : ?>
    <button id="fwx-back-to-top" class="fwx-back-to-top" aria-label="<?php echo esc_attr( fwx_t( 'footer_back_to_top' ) ); ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
    </button>
    <?php endif; ?>

    <!-- Auto-Responsividade para Tabelas -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tables = document.querySelectorAll('.entry-content table');
        tables.forEach(table => {
            if(table.parentElement.classList.contains('wp-block-table') || table.parentElement.classList.contains('fwx-table-responsive')) return;
            const wrapper = document.createElement('div');
            wrapper.className = 'fwx-table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        });
    });
    </script>

<?php wp_footer(); ?>
</body>
</html>

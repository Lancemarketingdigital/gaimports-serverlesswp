<?php
/**
 * Header do Fast WebX
 * Focado extrema leveza e 100/100 no PageSpeed.
 *
 * @package Fast_WebX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

	<?php 
	$hide_header = get_post_meta( get_the_ID(), '_fwx_hide_header', true );
	if ( '1' !== $hide_header ) : 
	?>
	<header id="masthead" class="site-header">
		<div class="fwx-container header-inner">
			<div class="site-branding">
				<?php
				$dark_logo_url = fwx_get_option( 'custom_logo_dark', '' );
				
				if ( has_custom_logo() ) :
					$custom_logo_id = get_theme_mod( 'custom_logo' );
					$logo_html = wp_get_attachment_image( $custom_logo_id, 'full', false, array(
						'class'    => 'custom-logo fwx-light-logo',
						'itemprop' => 'logo',
						'fetchpriority' => 'high',
					) );
					
					echo '<a href="' . esc_url( home_url( '/' ) ) . '" rel="home" aria-label="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . ' - Ir para a Página Inicial">';
					echo $logo_html;
					if ( ! empty( $dark_logo_url ) ) {
						$dark_logo_id = attachment_url_to_postid($dark_logo_url);
						$width = '';
						$height = '';
						
						if ($dark_logo_id) {
							$meta = wp_get_attachment_metadata($dark_logo_id);
							$width = isset($meta['width']) ? $meta['width'] : '';
							$height = isset($meta['height']) ? $meta['height'] : '';
						}
						
						if (empty($width) || empty($height)) {
							$light_meta = wp_get_attachment_metadata($custom_logo_id);
							$width = isset($light_meta['width']) ? $light_meta['width'] : '';
							$height = isset($light_meta['height']) ? $light_meta['height'] : '';
						}
						
						if (empty($width) || empty($height)) {
							$upload_dir = wp_upload_dir();
							$base_url = $upload_dir['baseurl'];
							$base_dir = $upload_dir['basedir'];
							if (strpos($dark_logo_url, $base_url) === 0) {
								$file_path = str_replace($base_url, $base_dir, $dark_logo_url);
								if (file_exists($file_path)) {
									$size = @getimagesize($file_path);
									if ($size) {
										$width = $size[0];
										$height = $size[1];
									}
								}
							}
						}
						
						$dimensions_attr = '';
						if (!empty($width) && !empty($height)) {
							$dimensions_attr = 'width="' . esc_attr($width) . '" height="' . esc_attr($height) . '"';
						}
						
						echo '<img src="' . esc_url( $dark_logo_url ) . '" ' . $dimensions_attr . ' class="custom-logo fwx-dark-logo" alt="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '" style="display:none;" fetchpriority="high">';
					}
					echo '</a>';
				else :
					?>
					<div class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></div>
					<?php
					$fastwebx_description = get_bloginfo( 'description', 'display' );
					if ( $fastwebx_description || is_customize_preview() ) :
						?>
						<p class="site-description screen-reader-text"><?php echo $fastwebx_description; ?></p>
					<?php endif;
				endif;
				?>
			</div><!-- .site-branding -->

            <?php 
            // Variáveis globais do CTA
            $cta_enable = fwx_get_option('header_cta_enable', 0);
            $cta_text   = fwx_get_option('header_cta_text', 'Fale Conosco');
            $cta_url    = fwx_get_option('header_cta_url', '#');
            ?>

            <button id="fwx-mobile-menu-toggle" class="fwx-mobile-btn" aria-label="<?php echo esc_attr( fwx_t( 'header_open_menu' ) ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>

			<nav id="site-navigation" class="main-navigation">
				<button id="fwx-mobile-menu-close" class="fwx-mobile-btn-close" aria-label="<?php echo esc_attr( fwx_t( 'header_close_menu' ) ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
				</button>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'menu-primary',
					'menu_id'        => 'primary-menu',
					'fallback_cb'    => false,
                    'depth'          => 3,
				) );
				?>

                <!-- Botão CTA no Mobile (dentro do menu) -->
                <?php if ( $cta_enable && !empty($cta_text) ) : ?>
                <div class="fwx-mobile-cta-wrapper">
                    <a href="<?php echo esc_url($cta_url); ?>" class="fwx-header-cta-btn">
                        <?php echo esc_html($cta_text); ?>
                    </a>
                </div>
                <?php endif; ?>
			</nav><!-- #site-navigation -->

            <!-- Grupo de Ações do Topo -->
            <div class="fwx-header-actions" style="display:flex; align-items:center; gap:10px;">
                <!-- Botão CTA opcional (Desktop) -->
                <?php if ( $cta_enable && !empty($cta_text) ) : ?>
                <a href="<?php echo esc_url($cta_url); ?>" class="fwx-header-cta-btn">
                    <?php echo esc_html($cta_text); ?>
                </a>
                <?php endif; ?>

                <!-- Botão de Busca -->
                <?php if ( fwx_get_option( 'enable_header_search', 0 ) ) : ?>
                <button id="fwx-search-toggle" class="fwx-theme-toggle" aria-label="<?php echo esc_attr( fwx_t( 'header_open_search' ) ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
                <?php endif; ?>

                <!-- Botão Dark Mode: fora do nav para não entrar no drawer mobile -->
                <?php if ( fwx_get_option( 'enable_header_mode_toggle', 0 ) ) : ?>
                <button id="fwx-dark-mode-toggle" class="fwx-theme-toggle" aria-label="<?php echo esc_attr( fwx_t( 'header_toggle_dark_mode' ) ); ?>">
                    <svg class="fwx-icon-sun" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                    <svg class="fwx-icon-moon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                </button>
                <?php endif; ?>
            </div>

            <div id="fwx-mobile-overlay" class="fwx-overlay"></div>
		</div>
	</header><!-- #masthead -->
	<?php endif; ?>


<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php echo esc_html( fwx_t( 'header_skip_link' ) ); ?></a>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('fwx-mobile-menu-toggle');
        const closeBtn = document.getElementById('fwx-mobile-menu-close');
        const nav = document.getElementById('site-navigation');
        const overlay = document.getElementById('fwx-mobile-overlay');

        function openMenu() {
            nav.classList.add('mobile-open');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeMenu() {
            nav.classList.remove('mobile-open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        if(toggleBtn && nav) {
            toggleBtn.addEventListener('click', openMenu);
            closeBtn.addEventListener('click', closeMenu);
            overlay.addEventListener('click', closeMenu);
        }

        // Submenu Toggle Mobile
        const menuItemsWithChildren = document.querySelectorAll('.menu-item-has-children > a');
        
        menuItemsWithChildren.forEach(item => {
            // Adiciona ícone de seta
            const chevron = document.createElement('span');
            chevron.className = 'fwx-submenu-indicator';
            chevron.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>';
            item.appendChild(chevron);

            item.addEventListener('click', function(e) {
                if (window.innerWidth <= 992) {
                    e.preventDefault();
                    const parent = this.parentElement;
                    const submenu = parent.querySelector('.sub-menu');
                    
                    if (parent.classList.contains('active')) {
                        parent.classList.remove('active');
                        if (submenu) {
                            // Define altura fixa antes de fechar para a transição funcionar
                            submenu.style.maxHeight = submenu.scrollHeight + 'px';
                            // Força reflow
                            void submenu.offsetWidth;
                            submenu.style.maxHeight = '0px';
                        }
                    } else {
                        // Fecha outros submenus no mesmo nível (opcional)
                        // parent.parentElement.querySelectorAll('.menu-item-has-children').forEach(el => el.classList.remove('active'));
                        
                        parent.classList.add('active');
                        if (submenu) {
                            submenu.style.maxHeight = submenu.scrollHeight + 'px';
                            // Após a transição (300ms), libera a altura para acomodar submenus internos (3º nível)
                            setTimeout(() => {
                                if(parent.classList.contains('active')) {
                                    submenu.style.maxHeight = 'none';
                                }
                            }, 300);
                        }
                    }
                }
            });
        });

        // Fullscreen Search JS
        const searchToggle = document.getElementById('fwx-search-toggle');
        const searchOverlay = document.getElementById('fwx-fullscreen-search');
        const searchClose = document.getElementById('fwx-search-close');
        const searchInput = document.getElementById('fwx-search-input');

        function openSearch() {
            searchOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            setTimeout(() => searchInput.focus(), 100);
        }

        function closeSearch() {
            searchOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        if(searchToggle && searchOverlay) {
            searchToggle.addEventListener('click', openSearch);
            searchClose.addEventListener('click', closeSearch);
            searchOverlay.addEventListener('click', function(e) {
                if(e.target === searchOverlay) closeSearch();
            });
            document.addEventListener('keydown', function(e) {
                if(e.key === 'Escape' && searchOverlay.classList.contains('active')) {
                    closeSearch();
                }
            });
        }
    });
    </script>

    <!-- Overlay da Busca Tela Cheia -->
    <div id="fwx-fullscreen-search" class="fwx-fs-search-overlay">
        <button id="fwx-search-close" class="fwx-fs-search-close" aria-label="<?php echo esc_attr( fwx_t( 'header_close_search' ) ); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
        <div class="fwx-fs-search-content">
            <form role="search" method="get" class="fwx-fs-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <input type="hidden" name="post_type" value="post" />
                <input type="search" id="fwx-search-input" class="fwx-fs-search-input" placeholder="<?php echo esc_attr( fwx_t( 'header_search_placeholder' ) ); ?>" value="<?php echo get_search_query(); ?>" name="s" autocomplete="search" />
                <button type="submit" class="fwx-fs-search-submit" aria-label="<?php echo esc_attr( fwx_t( 'header_search_submit' ) ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
            </form>
            <p class="fwx-fs-search-help"><?php echo esc_html( fwx_t( 'header_search_help' ) ); ?></p>
        </div>
    </div>

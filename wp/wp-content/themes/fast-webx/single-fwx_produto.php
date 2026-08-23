<?php
/**
 * Template Single do Post Type Loja (Detalhe do Produto)
 *
 * Exibe a p�gina individual de um produto com imagem em destaque,
 * descri��o, pre�o, badge e bot�o CTA configur�vel.
 * Suporta sidebar condicional via op��o `sidebar_loja_single` do painel
 * (Conte�do > Sidebar Inteligente).
 *
 * @package Fast_WebX
 * @since   3.1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

if ( have_posts() ) :
    the_post();

    $post_id      = get_the_ID();
    $preco        = get_post_meta( $post_id, '_fwx_produto_preco', true );
    $preco_de     = get_post_meta( $post_id, '_fwx_produto_preco_de', true );
    $link         = get_post_meta( $post_id, '_fwx_produto_link', true );
    $badge        = get_post_meta( $post_id, '_fwx_produto_badge', true );
    $link_target  = get_post_meta( $post_id, '_fwx_produto_link_target', true );

    $show_price   = fwx_get_option( 'loja_show_price', 0 );
    $cta_text     = fwx_get_option( 'loja_cta_text', 'Ver Produto' );

    $cta_url      = ! empty( $link ) ? $link : '';
    $target_attr  = ( $link_target === '1' ) ? ' target="_blank" rel="noopener noreferrer"' : '';

    $categorias   = get_the_terms( $post_id, 'fwx_categoria_produto' );

    // Sidebar condicional � controlada pelo painel (Conte�do > Sidebar Inteligente)
    $enable_sidebar   = fwx_get_option( 'sidebar_loja_single', 0 );
    $layout_class     = $enable_sidebar ? 'has-sidebar' : 'no-sidebar';
    ?>

<main id="primary" class="site-main fwx-container fwx-loja-single">

    <div class="fwx-archive-layout <?php echo esc_attr( $layout_class ); ?>">

        <div class="fwx-archive-content">

            <article id="produto-<?php the_ID(); ?>" <?php post_class( 'fwx-produto-single-wrap' ); ?>>

                <!-- Coluna de Imagem -->
                <div class="fwx-produto-single-gallery">
                    <?php 
                    $img_1 = get_post_meta( $post_id, '_fwx_produto_img_1', true );
                    $img_2 = get_post_meta( $post_id, '_fwx_produto_img_2', true );
                    $img_3 = get_post_meta( $post_id, '_fwx_produto_img_3', true );
                    $gallery = array_filter( array( $img_1, $img_2, $img_3 ) );

                    if ( has_post_thumbnail() ) : 
                        $thumb_id  = get_post_thumbnail_id();
                        $thumb_medium_url = wp_get_attachment_image_url( $thumb_id, 'fwx-loja-square' );
                        $thumb_large_url = wp_get_attachment_image_url( $thumb_id, 'fwx-loja-square-large' );
                        ?>
                        <div class="fwx-produto-single-thumb">
                            <a href="<?php echo esc_url( $thumb_large_url ); ?>" class="fwx-produto-single-link" title="<?php echo esc_attr( get_the_title() ); ?>">
                                <?php echo wp_get_attachment_image( $thumb_id, 'fwx-loja-square', false, array(
                                    'loading'       => 'eager',
                                    'fetchpriority' => 'high',
                                    'decoding'      => 'auto',
                                    'class'         => 'fwx-produto-single-img',
                                    'alt'           => esc_attr( get_the_title() ),
                                ) ); ?>
                            </a>
                            <div class="fwx-produto-single-video-container" style="display: none; width: 100%; height: 100%; aspect-ratio: 1/1;"></div>
                            <?php if ( ! empty( $badge ) ) : ?>
                                <span class="fwx-produto-badge fwx-produto-badge-large"><?php echo esc_html( $badge ); ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ( ! empty( $gallery ) ) : ?>
                            <div class="fwx-produto-single-gallery-nav" aria-label="<?php esc_attr_e( 'Galeria do produto', 'fast-webx' ); ?>">
                                <div class="fwx-gallery-nav-item active" data-large="<?php echo esc_url( $thumb_large_url ); ?>" data-src="<?php echo esc_url( $thumb_medium_url ); ?>" data-type="image">
                                    <?php echo wp_get_attachment_image( $thumb_id, 'fwx-loja-square-small', false, array( 'alt' => esc_attr( get_the_title() ) ) ); ?>
                                </div>
                                <?php foreach ( $gallery as $index => $img_url ) : 
                                    $is_video = fwx_is_video_url( $img_url );
                                    $type = $is_video ? 'video' : 'image';
                                    
                                    // Resolver IDs locais das URLs da galeria para obter tamanhos menores
                                    $img_id = attachment_url_to_postid( $img_url );
                                    $img_small_url = $img_url;
                                    $img_medium_url = $img_url;
                                    $img_large_url = $img_url;
                                    
                                    if ( $img_id ) {
                                        $img_small_url = wp_get_attachment_image_url( $img_id, 'fwx-loja-square-small' );
                                        $img_medium_url = wp_get_attachment_image_url( $img_id, 'fwx-loja-square' );
                                        $img_large_url = wp_get_attachment_image_url( $img_id, 'fwx-loja-square-large' );
                                    }
                                    ?>
                                    <div class="fwx-gallery-nav-item" data-large="<?php echo esc_url( $img_large_url ); ?>" data-src="<?php echo esc_url( $img_medium_url ); ?>" data-type="<?php echo $type; ?>">
                                        <?php if ( $is_video ) : ?>
                                            <div class="fwx-gallery-video-thumb-placeholder" style="position: relative; width: 100%; height: 100%; aspect-ratio: 1/1; overflow: hidden; background: #111; display: flex; align-items: center; justify-content: center; border-radius: var(--fwx-rad-4);">
                                                <svg viewBox="0 0 24 24" fill="currentColor" width="36" height="36" style="color: rgba(255,255,255,0.85); pointer-events: none;"><path d="M8 5v14l11-7z"/></svg>
                                            </div>
                                        <?php else : ?>
                                            <?php if ( $img_id ) : ?>
                                                <?php echo wp_get_attachment_image( $img_id, 'fwx-loja-square-small', false, array( 'alt' => esc_attr( sprintf( __( 'Imagem adicional %d', 'fast-webx' ), $index + 1 ) ) ) ); ?>
                                            <?php else : ?>
                                                <img src="<?php echo esc_url( $img_small_url ); ?>" alt="<?php echo esc_attr( sprintf( __( 'Imagem adicional %d', 'fast-webx' ), $index + 1 ) ); ?>" loading="lazy" />
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const items = document.querySelectorAll('.fwx-gallery-nav-item');
                                    const mainImg = document.querySelector('.fwx-produto-single-img');
                                    const mainLink = document.querySelector('.fwx-produto-single-link');
                                    const videoContainer = document.querySelector('.fwx-produto-single-video-container');

                                    if (items.length > 0) {
                                        items.forEach(item => {
                                            item.addEventListener('click', function() {
                                                const largeUrl = this.getAttribute('data-large');
                                                const srcUrl = this.getAttribute('data-src');
                                                const type = this.getAttribute('data-type');

                                                if (type === 'video') {
                                                    if (mainLink) mainLink.style.display = 'none';
                                                    if (videoContainer) {
                                                        videoContainer.style.display = 'block';
                                                        if (largeUrl.includes('youtube.com') || largeUrl.includes('youtu.be')) {
                                                            let videoId = '';
                                                            if (largeUrl.includes('youtu.be/')) {
                                                                videoId = largeUrl.split('youtu.be/')[1].split('?')[0];
                                                            } else if (largeUrl.includes('v=')) {
                                                                videoId = largeUrl.split('v=')[1].split('&')[0];
                                                            } else if (largeUrl.includes('embed/')) {
                                                                videoId = largeUrl.split('embed/')[1].split('?')[0];
                                                            }
                                                            videoContainer.innerHTML = '<iframe src="https://www.youtube.com/embed/' + videoId + '?autoplay=1" style="width:100%; height:100%; border:none; aspect-ratio:1/1;" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
                                                        } else if (largeUrl.includes('vimeo.com')) {
                                                            let videoId = largeUrl.split('vimeo.com/')[1].split('?')[0];
                                                            videoContainer.innerHTML = '<iframe src="https://player.vimeo.com/video/' + videoId + '?autoplay=1" style="width:100%; height:100%; border:none; aspect-ratio:1/1;" allow="autoplay; fullscreen" allowfullscreen></iframe>';
                                                        } else {
                                                            videoContainer.innerHTML = '<video src="' + largeUrl + '" controls autoplay style="width:100%; height:100%; object-fit:cover; aspect-ratio:1/1;"></video>';
                                                        }
                                                    }
                                                } else {
                                                    if (mainLink) {
                                                        mainLink.style.display = 'block';
                                                        mainLink.setAttribute('href', largeUrl);
                                                    }
                                                    if (mainImg) {
                                                        mainImg.setAttribute('src', srcUrl);
                                                        mainImg.setAttribute('srcset', '');
                                                    }
                                                    if (videoContainer) {
                                                        videoContainer.style.display = 'none';
                                                        videoContainer.innerHTML = '';
                                                    }
                                                }

                                                items.forEach(nav => nav.classList.remove('active'));
                                                this.classList.add('active');
                                            });
                                        });
                                    }
                                });
                            </script>
                        <?php endif; ?>

                    <?php else : ?>
                        <div class="fwx-produto-single-thumb fwx-produto-no-thumb">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="80" height="80" aria-hidden="true">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <path d="M3 9h18M9 21V9"/>
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Coluna de Dados -->
                <div class="fwx-produto-single-info">

                    <?php fwx_breadcrumbs(); ?>

                    <h1 class="fwx-produto-single-title"><?php the_title(); ?></h1>

                    <?php if ( $show_price && ! empty( $preco ) ) : ?>
                        <div class="fwx-produto-preco-wrap fwx-produto-preco-large">
                            <?php if ( ! empty( $preco_de ) ) : ?>
                                <span class="fwx-produto-preco-de"><?php echo esc_html( $preco_de ); ?></span>
                            <?php endif; ?>
                            <span class="fwx-produto-preco"><?php echo esc_html( $preco ); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php
                    // Excerpt como descri��o curta
                    $excerpt = get_the_excerpt();
                    if ( ! empty( $excerpt ) ) :
                        ?>
                        <div class="fwx-produto-single-excerpt">
                            <p><?php echo wp_kses_post( $excerpt ); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $cta_url ) ) : ?>
                        <a href="<?php echo esc_url( $cta_url ); ?>"<?php echo $target_attr; ?>
                           class="fwx-produto-cta fwx-produto-cta-large">
                            <?php echo esc_html( $cta_text ); ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18" aria-hidden="true">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </a>
                    <?php endif; ?>

                    <?php 
                    $loja_msg = fwx_get_option( 'loja_single_message', '' );
                    if ( ! empty( $loja_msg ) ) : 
                        ?>
                        <div class="fwx-produto-single-msg">
                            <?php echo wp_kses_post( $loja_msg ); ?>
                        </div>
                    <?php endif; ?>

                </div>

                <!-- Conte�do completo (editor) -->
                <?php
                $content = get_the_content();
                if ( ! empty( $content ) ) :
                    ?>
                    <div class="fwx-produto-single-content entry-content">
                        <?php the_content(); ?>
                    </div>
                <?php endif; ?>

                <?php fwx_related_produtos(); ?>

            </article>

        </div><!-- .fwx-archive-content -->

        <?php
        // --- Sidebar Condicional do Single Produto ---
        $show_sidebar = $enable_sidebar;
        if ( fwx_get_option( 'hide_sidebar_mobile', 0 ) && wp_is_mobile() ) {
            $show_sidebar = false;
        }
        if ( $show_sidebar ) :
        ?>
            <aside id="secondary" class="widget-area fwx-sidebar">
                <?php fwx_render_sidebar(); ?>
            </aside>
        <?php endif; ?>

    </div><!-- .fwx-archive-layout -->

</main>

    <?php
endif;

get_footer();
?>

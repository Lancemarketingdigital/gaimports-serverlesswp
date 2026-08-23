<?php
/**
 * Template de Arquivo do Post Type Loja (Catálogo de Produtos)
 *
 * Exibe o grid de cards de produtos com filtros por categoria e paginação.
 * Suporta sidebar condicional controlada pela opção `sidebar_loja` do painel
 * (Conteúdo > Sidebar Inteligente).
 *
 * @package Fast_WebX
 * @since   3.1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$catalog_title = fwx_get_option( 'loja_catalog_title', 'Nossa Loja' );
$cols          = absint( fwx_get_option( 'loja_columns', '3' ) );
if ( $cols < 1 || $cols > 6 ) {
    $cols = 3;
}

// Sidebar da Loja — controlada pelo painel (Conteúdo > Sidebar Inteligente)
$enable_loja_sidebar = fwx_get_option( 'sidebar_loja', 0 );
$loja_layout_class   = $enable_loja_sidebar ? 'has-sidebar' : 'no-sidebar';

// Categoria ativa (para filtro)
$cat_atual = get_queried_object();
$is_cat    = is_tax( 'fwx_categoria_produto' );

// Topo (Hero) conforme configurado no painel
$hero_layout = fwx_get_option( 'hero_layout', 'layout-1' );
$hero_class  = 'fwx-blog-hero fwx-loja-hero';
if ( 'layout-2' === $hero_layout ) {
    $hero_class .= ' fwx-blog-hero-layout-2';
}
?>

<header class="<?php echo esc_attr( $hero_class ); ?>">
    <div class="fwx-container fwx-blog-hero-inner">
        <span class="fwx-cat-label">
            <?php echo $is_cat ? esc_html( fwx_t( 'loja_catalog_badge_cat' ) ) : esc_html( fwx_t( 'loja_catalog_badge_store' ) ); ?>
        </span>
        <h1 class="fwx-blog-hero-title">
            <?php
            if ( $is_cat && ! is_wp_error( $cat_atual ) ) {
                echo esc_html( $cat_atual->name );
            } else {
                echo esc_html( $catalog_title );
            }
            ?>
        </h1>
        <?php if ( $is_cat && ! is_wp_error( $cat_atual ) && ! empty( $cat_atual->description ) ) : ?>
            <p class="fwx-blog-hero-desc"><?php echo esc_html( $cat_atual->description ); ?></p>
        <?php else : ?>
            <?php 
            $catalog_desc = fwx_get_option( 'loja_catalog_desc', '' ); 
            if ( ! empty( $catalog_desc ) ) : 
            ?>
                <p class="fwx-blog-hero-desc"><?php echo wp_kses_post( $catalog_desc ); ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</header>

<main id="primary" class="site-main fwx-container fwx-loja-archive">

    <?php
    // --- Filtros por Categoria ---
    $categorias = get_terms( array(
        'taxonomy'   => 'fwx_categoria_produto',
        'hide_empty' => true,
    ) );

    if ( ! empty( $categorias ) && ! is_wp_error( $categorias ) ) :
        $archive_url = get_post_type_archive_link( 'fwx_produto' );
        ?>
        <nav class="fwx-loja-filters" aria-label="<?php echo esc_attr( fwx_t( 'loja_filter_category' ) ); ?>">
            <a href="<?php echo esc_url( $archive_url ); ?>"
               class="fwx-loja-filter-btn<?php echo ! $is_cat ? ' active' : ''; ?>">
                <?php echo esc_html( fwx_t( 'loja_filter_all' ) ); ?>
            </a>
            <?php foreach ( $categorias as $cat ) : ?>
                <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"
                   class="fwx-loja-filter-btn<?php echo ( $is_cat && $cat_atual->term_id === $cat->term_id ) ? ' active' : ''; ?>">
                    <?php echo esc_html( $cat->name ); ?>
                    <span class="fwx-loja-filter-count"><?php echo absint( $cat->count ); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>

    <div class="fwx-archive-layout <?php echo esc_attr( $loja_layout_class ); ?>">

        <div class="fwx-archive-content">

            <?php if ( have_posts() ) : ?>

                <div class="fwx-loja-grid fwx-loja-cols-<?php echo $cols; ?>">
                    <?php
                    $i = 0;
                    while ( have_posts() ) :
                        the_post();
                        fwx_render_produto_card( get_the_ID(), $i > 2 );
                        $i++;
                    endwhile;
                    ?>
                </div>

                <?php
                // --- Botão "Mostrar Mais" (Load More) AJAX ---
                global $wp_query;
                if ( $wp_query->max_num_pages > 1 ) :
                    $data_attr = '';
                    if ( $is_cat && ! is_wp_error( $cat_atual ) ) {
                        $data_attr .= ' data-category="' . esc_attr( $cat_atual->term_id ) . '"';
                    }
                    ?>
                    <div class="fwx-load-more-wrapper">
                        <button id="fwx-loja-load-more-btn" class="fwx-btn-load-more" data-page="<?php echo get_query_var('paged') ? get_query_var('paged') : 1; ?>" data-max="<?php echo $wp_query->max_num_pages; ?>"<?php echo $data_attr; ?>>
                            <?php echo esc_html( fwx_t( 'archive_load_more' ) ); ?>
                        </button>
                    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const btn = document.getElementById('fwx-loja-load-more-btn');
                        if (!btn) return;
                        
                        btn.addEventListener('click', function() {
                            const page = parseInt(btn.getAttribute('data-page')) + 1;
                            const maxPage = parseInt(btn.getAttribute('data-max'));
                            
                            btn.innerHTML = '<?php echo esc_js( fwx_t( 'archive_loading' ) ); ?>';
                            btn.disabled = true;
                            
                            const formData = new FormData();
                            formData.append('action', 'fwx_load_more_produtos');
                            formData.append('page', page);
                            
                            const category = btn.getAttribute('data-category');
                            if (category) {
                                formData.append('category', category);
                            }
                            
                            formData.append('nonce', '<?php echo wp_create_nonce("fwx_loja_loadmore_nonce"); ?>');
                            
                            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                                method: 'POST',
                                body: formData
                            })
                            .then(res => res.text())
                            .then(html => {
                                if (html && html.trim() !== '') {
                                    const grid = document.querySelector('.fwx-loja-grid');
                                    if (grid) {
                                        grid.insertAdjacentHTML('beforeend', html);
                                        btn.setAttribute('data-page', page);
                                        btn.innerHTML = '<?php echo esc_js( fwx_t( 'archive_load_more' ) ); ?>';
                                        btn.disabled = false;
                                        
                                        if (page >= maxPage) {
                                            btn.style.display = 'none';
                                        }
                                    }
                                } else {
                                    btn.style.display = 'none';
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                btn.innerHTML = '<?php echo esc_js( fwx_t( 'archive_load_more' ) ); ?>';
                                btn.disabled = false;
                            });
                        });
                    });
                    </script>
                <?php endif; ?>

            <?php else : ?>
                <div class="fwx-loja-empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="64" height="64" aria-hidden="true">
                        <path d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <p><?php echo esc_html( fwx_t( 'loja_empty_category' ) ); ?></p>
                </div>
            <?php endif; ?>

        </div><!-- .fwx-archive-content -->

        <?php
        // --- Sidebar Condicional da Loja ---
        $show_loja_sidebar = $enable_loja_sidebar;
        if ( fwx_get_option( 'hide_sidebar_mobile', 0 ) && wp_is_mobile() ) {
            $show_loja_sidebar = false;
        }
        if ( $show_loja_sidebar ) :
        ?>
            <aside id="secondary" class="widget-area fwx-sidebar">
                <?php fwx_render_sidebar(); ?>
            </aside>
        <?php endif; ?>

    </div><!-- .fwx-archive-layout -->

</main>

<?php get_footer(); ?>

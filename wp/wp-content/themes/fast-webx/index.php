<?php
/**
 * O template principal.
 * Usado para a página inicial (Home/Blog) de listagem de artigos.
 *
 * @package Fast_WebX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Pega a opção de layout salvo no Admin. Padrão: layout-a.
$home_layout = fwx_get_option( 'home_layout', 'layout-a' );

// Sobrescrita especifica para categorias e busca, se assim configurado no painel.
if ( ( is_archive() || is_category() || is_search() ) && fwx_get_option( 'category_layout', 'global' ) !== 'global' ) {
    $home_layout = fwx_get_option( 'category_layout', 'global' );
}

$hero_layout = fwx_get_option( 'hero_layout', 'layout-1' );
$hero_class  = 'fwx-blog-hero';
if ( 'layout-2' === $hero_layout ) {
    $hero_class .= ' fwx-blog-hero-layout-2';
}
?>

<?php if ( is_archive() || is_search() || is_home() ) : ?>
    <header class="<?php echo esc_attr( $hero_class ); ?>">
        <div class="fwx-container fwx-blog-hero-inner">
            <?php
            if ( is_search() ) {
                echo '<span class="fwx-cat-label">' . esc_html( fwx_t( 'archive_search_label' ) ) . '</span>';
                echo '<h1 class="fwx-blog-hero-title">' . esc_html( sprintf( fwx_t( 'archive_search_results' ), get_search_query() ) ) . '</h1>';
            } elseif ( is_archive() ) {
                $prefix = is_category() ? fwx_t( 'archive_prefix_category' ) : ( is_tag() ? fwx_t( 'archive_prefix_tag' ) : fwx_t( 'archive_prefix_archive' ) );
                $cat_class = '';
                if ( is_category() ) {
                    $cat_id = get_queried_object_id();
                    $cat_class = ' fwx-cat-id-' . $cat_id;
                }
                echo '<span class="fwx-cat-label' . esc_attr( $cat_class ) . '">' . esc_html( $prefix ) . '</span>';
                $clean_title = trim( preg_replace( '/^.*?:\s*/', '', get_the_archive_title() ) );
                echo '<h1 class="fwx-blog-hero-title">' . wp_kses_post( $clean_title ) . '</h1>';
                $queried_obj = get_queried_object();
                $desc = '';
                if ( $queried_obj instanceof WP_Term ) {
                    $desc = $queried_obj->description;
                } elseif ( $queried_obj instanceof WP_User ) {
                    $desc = $queried_obj->description;
                }
                if ( ! empty( $desc ) ) {
                    echo '<p class="fwx-blog-hero-desc">' . esc_html( $desc ) . '</p>';
                }
            } elseif ( is_home() ) {
                echo '<span class="fwx-cat-label">' . esc_html( fwx_t( 'archive_posts_label' ) ) . '</span>';
                $title = is_front_page() ? get_bloginfo( 'name' ) : single_post_title( '', false );
                echo '<h1 class="fwx-blog-hero-title">' . esc_html( $title ) . '</h1>';
                echo '<p class="fwx-blog-hero-desc">' . esc_html( get_bloginfo( 'description' ) ) . '</p>';
            }
            ?>
        </div>
    </header>
<?php endif; ?>

<?php
$enable_archive_sidebar = fwx_get_option( 'sidebar_archive', 0 );
$archive_layout_class   = $enable_archive_sidebar ? 'has-sidebar' : 'no-sidebar';
?>

<main id="primary" class="site-main fwx-container fwx-home-<?php echo esc_attr( $home_layout ); ?>">

    <?php
    if ( have_posts() ) :
        $post_count = 0;

        // 1. GRADE HERO (TOPO) - Apenas para Layout F e na Primeira Página
        if ( 'layout-f' === $home_layout && ! is_paged() ) :
            ?>
            <div class="fwx-news-hero-section">
                <div class="fwx-news-hero-grid">
                    <?php
                    while ( have_posts() && $post_count < 4 ) :
                        the_post();
                        $post_count++;
                        
                        $hero_item_class = 'fwx-post-item';
                        $thumb_size = 'fwx-home-grid';

                        if ( 1 === $post_count ) {
                            $hero_item_class .= ' fwx-news-hero-main';
                            $thumb_size = 'fwx-home-featured';
                        } elseif ( 2 === $post_count ) {
                            $hero_item_class .= ' fwx-news-hero-medium';
                        } else {
                            $hero_item_class .= ' fwx-news-hero-small';
                        }
                        ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class( $hero_item_class ); ?>>
                            <div class="fwx-post-thumb">
                                <a href="<?php the_permalink(); ?>">
                                    <?php 
                                    $attr = array( 'loading' => 'eager' );
                                    if ( 1 === $post_count ) {
                                        $attr['fetchpriority'] = 'high';
                                    }
                                    fwx_the_post_thumbnail( null, $thumb_size, $attr ); 
                                    ?>
                                </a>
                                <div class="fwx-news-overlay">
                                    <?php 
                                    $categories = get_the_category();
                                    if ( ! empty( $categories ) ) {
                                        echo '<span class="fwx-cat-label fwx-cat-id-' . esc_attr( $categories[0]->term_id ) . '">' . esc_html( $categories[0]->name ) . '</span>';
                                    }
                                    ?>
                                    <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                                    <?php fwx_render_post_meta(); ?>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php
        endif;

        // 2. CONTEÚDO PRINCIPAL + SIDEBAR
        ?>
        <div class="fwx-archive-layout <?php echo esc_attr( $archive_layout_class ); ?>">

            <div class="fwx-archive-content">
                <div class="fwx-posts-grid fwx-grid-<?php echo esc_attr( $home_layout ); ?>">
                    <?php
                    $in_mixed_block = false;
                    $in_side_list = false;

                    while ( have_posts() ) :
                        the_post();
                        $post_count++;
                        
                        $item_class = 'fwx-post-item';
                        $thumb_size = 'fwx-home-grid';

                        if ( 'layout-f' === $home_layout ) {
                            // Lógica Layout F: Post 5 destaque, Posts 6-10 lista lateral
                            if ( 5 === $post_count && ! is_paged() ) {
                                echo '<div class="fwx-news-mixed-block">';
                                $in_mixed_block = true;
                                $item_class .= ' fwx-news-block-main';
                                $thumb_size = 'fwx-home-featured';
                            } elseif ( $post_count >= 6 && $post_count <= 9 && ! is_paged() ) {
                                if ( 6 === $post_count ) {
                                    echo '<div class="fwx-news-block-side-list">';
                                    $in_side_list = true;
                                }
                                $item_class .= ' fwx-news-block-side-item';
                                $thumb_size = 'fwx-news-list';
                            } elseif ( 10 === $post_count && ! is_paged() ) {
                                if ( $in_side_list ) { echo '</div>'; $in_side_list = false; }
                                if ( $in_mixed_block ) { echo '</div>'; $in_mixed_block = false; }
                                $item_class .= ' fwx-news-list-item';
                                $thumb_size = 'fwx-news-list';
                            } else {
                                $item_class .= ' fwx-news-list-item';
                                $thumb_size = 'fwx-news-list';
                            }
                        } else {
                            // Layouts padrão
                            if ( ( 'layout-c' === $home_layout || 'layout-d' === $home_layout ) && 1 === $post_count ) {
                                $item_class .= ' fwx-post-featured';
                                $thumb_size = 'fwx-home-featured';
                            } elseif ( 'layout-e' === $home_layout ) {
                                $thumb_size = 'fwx-home-featured';
                            }
                        }
                        ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class( $item_class ); ?>>
                            
                            <div class="fwx-post-thumb">
                                <a href="<?php the_permalink(); ?>">
                                    <?php 
                                    $attr = array( 'loading' => ( $post_count <= 5 ) ? 'eager' : 'lazy' );
                                    
                                    // Se for o primeiro post da página, ganha fetchpriority high
                                    if ( 1 === $post_count ) {
                                        $attr['fetchpriority'] = 'high';
                                    }

                                    // Otimização LCP/Performance para Layout E: 
                                    // Forçamos o navegador a entender que o card é pequeno (~410px), 
                                    // impedindo o download da imagem original (Full Size).
                                    if ( 'layout-e' === $home_layout ) {
                                        $attr['sizes'] = '(max-width: 600px) 100vw, (max-width: 1100px) 50vw, 410px';
                                    }

                                    fwx_the_post_thumbnail( null, $thumb_size, $attr ); 
                                    ?>
                                </a>
                            </div>

                            <div class="fwx-post-content">
                                <header class="entry-header">
                                    <?php 
                                    $categories = get_the_category();
                                    if ( ! empty( $categories ) ) {
                                        echo '<span class="fwx-cat-label fwx-cat-id-' . esc_attr( $categories[0]->term_id ) . '">' . esc_html( $categories[0]->name ) . '</span>';
                                    }
                                    
                                    $title_tag = ( 'layout-f' === $home_layout && $post_count >= 6 && $post_count <= 9 ) ? 'h3' : 'h2';
                                    the_title( '<' . $title_tag . ' class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></' . $title_tag . '>' ); 
                                    fwx_render_post_meta();
                                    ?>
                                </header>
                                
                                <?php if ( ! ( 'layout-f' === $home_layout && $post_count >= 6 && $post_count <= 9 ) ) : ?>
                                <div class="entry-summary">
                                    <?php the_excerpt(); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                        </article>
                        <?php
                    endwhile;

                    // Fechamento de segurança fora do while
                    if ( $in_side_list ) echo '</div>';
                    if ( $in_mixed_block ) echo '</div>';

                    // Botão Ver Mais DENTRO do grid para os novos itens herdarem o layout pai
                    global $wp_query;
                    if ( $wp_query->max_num_pages > 1 ) :
                        $data_attr = '';
                        if ( is_category() ) {
                            $data_attr .= ' data-category="' . get_queried_object_id() . '"';
                        } elseif ( is_tag() ) {
                            $data_attr .= ' data-tag="' . get_queried_object_id() . '"';
                        } elseif ( is_author() ) {
                            $data_attr .= ' data-author="' . get_queried_object_id() . '"';
                        } elseif ( is_search() ) {
                            $data_attr .= ' data-search="' . esc_attr( get_search_query() ) . '"';
                        }
                        ?>
                        <div class="fwx-load-more-wrapper">
                            <button id="fwx-load-more-btn" class="fwx-btn-load-more" data-page="<?php echo get_query_var('paged') ? get_query_var('paged') : 1; ?>" data-max="<?php echo $wp_query->max_num_pages; ?>"<?php echo $data_attr; ?>>
                                <?php echo esc_html( fwx_t( 'archive_load_more' ) ); ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </div><!-- .fwx-posts-grid -->
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const btn = document.getElementById('fwx-load-more-btn');
                        if(!btn) return;
                        
                        btn.addEventListener('click', function() {
                            const page = parseInt(btn.getAttribute('data-page')) + 1;
                            const maxPage = parseInt(btn.getAttribute('data-max'));
                            
                            btn.innerHTML = '<?php echo esc_js( fwx_t( 'archive_loading' ) ); ?>';
                            btn.disabled = true;
                            
                            const formData = new FormData();
                            formData.append('action', 'fwx_load_more_posts');
                            formData.append('page', page);
                            formData.append('layout', '<?php echo esc_js($home_layout); ?>');
                            
                            const category = btn.getAttribute('data-category');
                            const tag = btn.getAttribute('data-tag');
                            const author = btn.getAttribute('data-author');
                            const search = btn.getAttribute('data-search');
                            
                            if (category) formData.append('category', category);
                            if (tag) formData.append('tag', tag);
                            if (author) formData.append('author', author);
                            if (search) formData.append('search', search);
                            
                            formData.append('nonce', '<?php echo wp_create_nonce("fwx_loadmore_nonce"); ?>');
                            
                            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                                method: 'POST',
                                body: formData
                            })
                            .then(res => res.text())
                            .then(html => {
                                if(html && html.trim() !== '') {
                                    const wrapper = document.querySelector('.fwx-load-more-wrapper');
                                    wrapper.insertAdjacentHTML('beforebegin', html);
                                    
                                    btn.setAttribute('data-page', page);
                                    btn.innerHTML = '<?php echo esc_js( fwx_t( 'archive_load_more' ) ); ?>';
                                    btn.disabled = false;
                                    
                                    if(page >= maxPage) {
                                        wrapper.style.display = 'none';
                                    }
                                } else {
                                    document.querySelector('.fwx-load-more-wrapper').style.display = 'none';
                                }
                            })
                            .catch(() => {
                                btn.innerHTML = '<?php echo esc_js( fwx_t( 'archive_error' ) ); ?>';
                                btn.disabled = false;
                            });
                        });
                    });
                    </script>

            </div><!-- .fwx-archive-content -->

            <?php 
            $show_archive_sidebar = $enable_archive_sidebar;
            if ( fwx_get_option( 'hide_sidebar_mobile', 0 ) && wp_is_mobile() ) {
                $show_archive_sidebar = false;
            }
            if ( $show_archive_sidebar ) : 
            ?>
                <aside id="secondary" class="widget-area fwx-sidebar">
                    <?php fwx_render_sidebar(); ?>
                </aside>
            <?php endif; ?>

        </div><!-- .fwx-archive-layout -->

    <?php else : ?>
        <div class="fwx-container">
            <p><?php echo esc_html( fwx_t( 'archive_no_content' ) ); ?></p>
        </div>
    <?php endif; ?>
</main>

<?php
get_footer();

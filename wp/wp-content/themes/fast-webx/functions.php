<?php
/**
 * Fast WebX - Funções e Configurações Base
 * 
 * @package Fast_WebX
 * @version 3.1.3
 * 
 * @package Fast_WebX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Proteção contra acesso direto.
}

/**
 * Fonte de Verdade para o Versionamento do Tema
 */
if ( ! defined( 'FWX_VERSION' ) ) {
    define( 'FWX_VERSION', '3.1.3' );
}


/**
 * Inclusões Globais da Arquitetura do Fast WebX
 */
require_once get_template_directory() . '/inc/translations.php';
require_once get_template_directory() . '/inc/admin-panel.php';
require_once get_template_directory() . '/inc/seo-reading.php';
require_once get_template_directory() . '/inc/image-optimizer.php';
require_once get_template_directory() . '/inc/ads-manager.php';
require_once get_template_directory() . '/inc/lead-capture.php';
require_once get_template_directory() . '/inc/class-leads-table.php';
require_once get_template_directory() . '/inc/lgpd-banner.php';
require_once get_template_directory() . '/inc/duplicate-post.php';
require_once get_template_directory() . '/inc/interactivity.php';
require_once get_template_directory() . '/inc/author-authority.php';
require_once get_template_directory() . '/inc/core-tools.php';
require_once get_template_directory() . '/inc/page-options.php';
require_once get_template_directory() . '/inc/license.php';
require_once get_template_directory() . '/inc/admin-tweaks.php';
require_once get_template_directory() . '/inc/loja.php';

function fast_webx_setup() {
    // Declarações de suporte nativo
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'custom-logo' );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );

    // Registro de menus enxutos (Header e Footer)
    register_nav_menus( array(
		'menu-primary'  => esc_html__( 'Menu Principal', 'fast-webx' ),
        'menu-footer'   => esc_html__( 'Menu do Rodapé Coluna 3', 'fast-webx' ),
        'menu-footer-2' => esc_html__( 'Menu do Rodapé Coluna 2', 'fast-webx' ),
	) );

    // Habilita suporte a Resumo (Excerpt) em Páginas para SEO Nativo
    add_post_type_support( 'page', 'excerpt' );
}
add_action( 'after_setup_theme', 'fast_webx_setup' );

/**
 * Padronização de Resumos (Excerpts)
 * Força o limite de 25 palavras para evitar quebra de layout nos cards de post.
 * Aplica-se inclusive aos resumos manuais (custom excerpts), mas protege a exibição no single.php.
 */
function fast_webx_force_excerpt_length( $excerpt, $post = null ) {
    if ( is_single() && is_main_query() ) {
        return $excerpt;
    }
    
    if ( ! empty( $excerpt ) ) {
        $excerpt = wp_trim_words( $excerpt, 20, '...' );
    }
    return $excerpt;
}
add_filter( 'get_the_excerpt', 'fast_webx_force_excerpt_length', 999, 2 );

function fast_webx_custom_excerpt_length( $length ) {
    return 20;
}
add_filter( 'excerpt_length', 'fast_webx_custom_excerpt_length', 999 );

function fast_webx_custom_excerpt_more( $more ) {
    return '...';
}
add_filter( 'excerpt_more', 'fast_webx_custom_excerpt_more' );
/**
 * Registro de Área de Widgets (Sidebar)
 */
function fast_webx_widgets_init() {
    register_sidebar( array(
        'name'          => esc_html__( 'Sidebar Principal', 'fast-webx' ),
        'id'            => 'sidebar-1',
        'description'   => esc_html__( 'Adicione widgets aqui para aparecer na lateral dos artigos.', 'fast-webx' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ) );
}
add_action( 'widgets_init', 'fast_webx_widgets_init' );

/**
 * Verifica se o CSS de anúncios deve ser enfileirado
 */
function fwx_should_load_ads_css() {
    $options = get_option( 'fast_webx_options' );
    
    // 1. Se estivermos usando o template Blank, não carrega.
    if ( is_page_template( 'template-blank.php' ) ) {
        return false;
    }

    // 2. Se for produto da loja (single-fwx_produto) e a opção de ocultar ads na loja estiver ativa
    $disable_ads_loja = isset( $options['disable_ads_loja_single'] ) ? (int) $options['disable_ads_loja_single'] : 0;
    if ( is_singular( 'fwx_produto' ) && $disable_ads_loja ) {
        return false;
    }

    // 3. Se for um post singular, verifica se há algum bloco de anúncio ativo com conteúdo
    if ( is_single() ) {
        for ( $i = 1; $i <= 3; $i++ ) {
            $ad_content = isset( $options['ad_block_content_' . $i] ) ? $options['ad_block_content_' . $i] : '';
            $injection_point = isset( $options['ad_injection_point_' . $i] ) ? $options['ad_injection_point_' . $i] : 'none';
            
            if ( ! empty( $ad_content ) && 'none' !== $injection_point ) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Verifica se o CSS da Home LP deve ser enfileirado
 */
function fwx_should_load_home_css() {
    // 1. Se estivermos usando o template Blank, não carrega.
    if ( is_page_template( 'template-blank.php' ) ) {
        return false;
    }

    // 2. Se a página atual usar o template de Landing Page da Home
    if ( is_page_template( 'template-home.php' ) ) {
        return true;
    }

    return false;
}

/**
 * Verifica se o CSS específico de páginas deve ser enfileirado
 */
function fwx_should_load_page_css() {
    // 1. Se estivermos usando o template Blank ou o template Full Width, não carrega o pagina.css.
    if ( is_page_template( 'template-blank.php' ) || is_page_template( 'template-full-width.php' ) ) {
        return false;
    }

    // 2. Se for o template da Home LP, já carrega o home.css, não precisa do pagina.css.
    if ( is_page_template( 'template-home.php' ) ) {
        return false;
    }

    // 3. Se for uma página singular
    if ( is_page() ) {
        return true;
    }

    return false;
}

/**
 * Verifica se o CSS do Blog deve ser enfileirado
 */
function fwx_should_load_blog_css() {
    // 1. Se estivermos usando o template Blank, nunca carrega o blog.css.
    if ( is_page_template( 'template-blank.php' ) ) {
        return false;
    }

    // Se for página da loja (CPT fwx_produto ou taxonomia fwx_categoria_produto), não carrega o blog.css.
    if ( is_singular( 'fwx_produto' ) || is_post_type_archive( 'fwx_produto' ) || is_tax( 'fwx_categoria_produto' ) ) {
        return false;
    }

    // 2. Se for a página inicial do blog, listagens de arquivo (categoria, tag, autor, data), busca ou 404.
    if ( is_home() || is_archive() || is_search() || is_404() ) {
        // Se for busca mas o post_type filtrado for produto, não carrega
        if ( is_search() && get_query_var( 'post_type' ) === 'fwx_produto' ) {
            return false;
        }
        return true;
    }

    // 3. Se for post individual (singular do tipo post)
    if ( is_singular( 'post' ) ) {
        return true;
    }

    // 4. Se for página estática
    if ( is_page() ) {
        // Se a página for a home LP, não carrega.
        if ( is_page_template( 'template-home.php' ) ) {
            return false;
        }

        // Se usar shortcodes específicos do blog
        $post = get_post();
        if ( $post && ( has_shortcode( $post->post_content, 'fwx_author_box' ) || has_shortcode( $post->post_content, 'fwx_related_posts' ) ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Verifica se o CSS do Sumário (TOC) deve ser enfileirado
 */
function fwx_should_load_toc_css() {
    if ( ! is_singular() ) {
        return false;
    }

    // TOC individual e global só para o post type post nativo
    if ( is_singular( 'post' ) ) {
        return (bool) fwx_get_option( 'enable_toc', 0 );
    }

    if ( is_page() ) {
        return '1' === get_post_meta( get_the_ID(), '_fwx_enable_page_toc', true );
    }

    return false;
}

/**
 * Verifica se o CSS da Sidebar deve ser enfileirado
 */
function fwx_should_load_sidebar_css() {
    // 1. Se estivermos usando o template Blank, não carrega.
    if ( is_page_template( 'template-blank.php' ) ) {
        return false;
    }

    // 2. Se for dispositivo móvel e a opção de ocultar a sidebar no mobile estiver ativa, não carrega.
    if ( fwx_get_option( 'hide_sidebar_mobile', 0 ) && wp_is_mobile() ) {
        return false;
    }

    // 3. Suporte a shortcodes inline: se for post/página singular e tiver o shortcode da sidebar, enfileira preventivamente
    if ( is_singular() ) {
        $post = get_post();
        if ( $post && ( has_shortcode( $post->post_content, 'fwx_sidebar_author' ) || has_shortcode( $post->post_content, 'fwx_sidebar_related' ) ) ) {
            return true;
        }
    }

    // 4. Se for post singular do blog, retorna se a sidebar de posts está ativa
    if ( is_single() ) {
        if ( is_singular( 'fwx_produto' ) ) {
            return (bool) fwx_get_option( 'sidebar_loja_single', 0 );
        }
        return (bool) fwx_get_option( 'sidebar_posts', 0 );
    }

    // 5. Se for página estática, retorna se a sidebar de páginas está ativa
    if ( is_page() ) {
        // Se usar templates que nunca carregam a sidebar por padrão, retorna false
        if ( is_page_template( 'template-full-width.php' ) || is_page_template( 'template-wide.php' ) || is_page_template( 'template-home.php' ) ) {
            return false;
        }
        return (bool) fwx_get_option( 'sidebar_pages', 0 );
    }

    // 6. Se for listagens, busca ou home de posts, retorna se a sidebar de archives está ativa
    if ( is_home() || is_archive() || is_search() ) {
        if ( function_exists( 'fwx_is_loja_page' ) && fwx_is_loja_page() ) {
            return (bool) fwx_get_option( 'sidebar_loja', 0 );
        }
        return (bool) fwx_get_option( 'sidebar_archive', 0 );
    }

    return false;
}

/**
 * Verifica se o CSS de Cards (cards.css) deve ser enfileirado
 */
function fwx_should_load_cards_css() {
    // 1. Se estivermos usando o template Blank ou o template Home LP, não carrega cards.css.
    // NOTA: is_front_page() retorna true tanto em páginas estáticas LP quanto na home default de posts.
    // Por isso NÃO incluímos is_front_page() aqui sozinho — ele é tratado abaixo junto com is_home().
    if ( is_page_template( 'template-blank.php' ) || is_page_template( 'template-home.php' ) ) {
        return false;
    }

    // Se for a página inicial estática (LP) sem ser a home de posts, não carrega cards.css.
    // is_front_page() && ! is_home() = página estática definida como inicial (não a listagem de posts).
    if ( is_front_page() && ! is_home() ) {
        return false;
    }

    // Se a loja estiver ativada e for qualquer página da loja (archive, single, taxonomia ou helper fwx_is_loja_page), não carrega cards.css.
    if ( ( function_exists( 'fwx_is_loja_page' ) && fwx_is_loja_page() ) || is_singular( 'fwx_produto' ) || is_post_type_archive( 'fwx_produto' ) || is_tax( 'fwx_categoria_produto' ) ) {
        return false;
    }

    // Se o post type da query principal ou a query variable 'post_type' for fwx_produto, inibe cards.css.
    if ( get_query_var( 'post_type' ) === 'fwx_produto' || is_attachment() ) {
        return false;
    }

    // Se for uma página estática singular contendo o shortcode da loja [fwx_loja]
    if ( is_singular() ) {
        $post = get_post();
        if ( $post && has_shortcode( $post->post_content, 'fwx_loja' ) ) {
            return false;
        }
    }

    // 2. Se for listagens, busca, home de posts ou 404, sempre carrega.
    if ( is_home() || is_archive() || is_search() || is_404() ) {
        // Se for busca de produtos, também não carrega
        if ( is_search() && get_query_var( 'post_type' ) === 'fwx_produto' ) {
            return false;
        }
        return true;
    }

    return false;
}


/**
 * Enfileiramento Otimizado de Scripts e Estilos
 * Princípio: Zero Bloatware e Zero jQuery.
 */
function fast_webx_enqueue_scripts() {
	// Carrega o CSS global super leve.
	wp_enqueue_style( 'fast-webx-style', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );

    // Carrega o CSS de Anúncios condicionalmente.
    if ( fwx_should_load_ads_css() ) {
        wp_enqueue_style( 'fwx-ads', get_template_directory_uri() . '/ads.css', array( 'fast-webx-style' ), wp_get_theme()->get( 'Version' ) );
    }

    // Carrega o CSS da Home LP condicionalmente.
    if ( fwx_should_load_home_css() ) {
        wp_enqueue_style( 'fwx-home', get_template_directory_uri() . '/home.css', array( 'fast-webx-style' ), wp_get_theme()->get( 'Version' ) );
    }

    // Carrega o CSS de Páginas condicionalmente.
    if ( fwx_should_load_page_css() ) {
        wp_enqueue_style( 'fwx-page', get_template_directory_uri() . '/pagina.css', array( 'fast-webx-style' ), wp_get_theme()->get( 'Version' ) );
    }

    // Carrega o CSS da Sidebar condicionalmente.
    if ( fwx_should_load_sidebar_css() ) {
        wp_enqueue_style( 'fwx-sidebar', get_template_directory_uri() . '/sidebar.css', array( 'fast-webx-style' ), wp_get_theme()->get( 'Version' ) );
    }

    // Carrega o CSS de Cards condicionalmente.
    if ( fwx_should_load_cards_css() ) {
        wp_enqueue_style( 'fwx-cards', get_template_directory_uri() . '/cards.css', array( 'fast-webx-style' ), wp_get_theme()->get( 'Version' ) );
    }

    // Carrega o CSS do Blog condicionalmente.
    if ( fwx_should_load_blog_css() ) {
        wp_enqueue_style( 'fwx-blog', get_template_directory_uri() . '/blog.css', array( 'fast-webx-style' ), wp_get_theme()->get( 'Version' ) );
    }

    // Carrega o CSS do Sumário (TOC) condicionalmente.
    if ( fwx_should_load_toc_css() ) {
        wp_enqueue_style( 'fwx-toc', get_template_directory_uri() . '/toc.css', array( 'fast-webx-style' ), wp_get_theme()->get( 'Version' ) );
    }

    // Carrega o CSS de Link in Bio apenas se o shortcode estiver no post/página atual
    if ( is_singular() ) {
        $post = get_post();
        if ( $post && has_shortcode( $post->post_content, 'fwx_link_in_bio' ) ) {
            wp_enqueue_style( 'fwx-bio', get_template_directory_uri() . '/bio.css', array( 'fast-webx-style' ), wp_get_theme()->get( 'Version' ) );
        }
    }

	// Regra inegociável: Remover jQuery da interface de usuário (Front-end)
    if ( ! is_admin() ) {
        wp_deregister_script( 'jquery' );
    }

    // Carrega script de progresso apenas em posts de artigos nativos (singular do tipo post)
    if ( is_singular( 'post' ) ) {
        if ( fwx_get_option( 'enable_reading_progress', 0 ) ) {
            wp_enqueue_script( 'fwx-progress', get_template_directory_uri() . '/assets/js/progress.js', array(), wp_get_theme()->get( 'Version' ), true );
        }
        if ( fwx_get_option( 'enable_tts', 0 ) ) {
            wp_enqueue_script( 'fwx-tts', get_template_directory_uri() . '/assets/js/tts.js', array(), wp_get_theme()->get( 'Version' ), true );
        }
    }

    // Carrega script de lightbox em posts individuais (incluindo CPTs como produtos)
    if ( is_single() ) {
        if ( fwx_get_option( 'enable_lightbox', 0 ) ) {
            wp_enqueue_script( 'fwx-lightbox', get_template_directory_uri() . '/assets/js/lightbox.js', array(), wp_get_theme()->get( 'Version' ), true );
        }
    }

    // Carrega scripts principais (Vanilla JS)
    wp_enqueue_script( 'fast-webx-main', get_template_directory_uri() . '/assets/js/main.js', array(), wp_get_theme()->get( 'Version' ), true );

    // Blindagem final para remover estilos indesejados na Home LP.
    // IMPORTANTE: is_front_page() retorna true tanto na LP estática quanto na home default de posts.
    // A condição abaixo garante que o dequeue só acontece quando é de fato uma página estática (LP),
    // não quando a home é a listagem padrão de posts do WordPress (is_home() AND is_front_page()).
    $is_lp_template   = is_page_template( 'template-home.php' );
    $is_static_front  = is_front_page() && ! is_home(); // página estática na raiz, não a home de posts
    if ( $is_lp_template || $is_static_front ) {
        wp_dequeue_style( 'fwx-cards' );
        wp_dequeue_style( 'fwx-loja' );
    }
}
add_action( 'wp_enqueue_scripts', 'fast_webx_enqueue_scripts' );

/**
 * Blindagem Técnica para o Modelo Blank (v2.6.6)
 * Remove TODOS os estilos e scripts do WP/Tema quando o template Blank é usado.
 * Garante que Landing Pages externas tenham controle absoluto sobre o DOM.
 * 
 * O que é BLOQUEADO:
 * - CSS global do tema (style.css)
 * - CSS dinâmico (fast-webx-dynamic-css) que injeta variáveis com !important
 * - Scripts nativos do tema (main.js, progress.js, lightbox.js)
 * - Script de Dark Mode nativo (fwx_dark_mode_loader / toggle)
 * - Banner LGPD do tema
 * - Bloat do WordPress Core (block-library, global-styles)
 * 
 * O que é PRESERVADO:
 * - Scripts/Estilos dedicados por página (page-options Meta Box)
 * - wp_head() e wp_footer() para plugins essenciais
 */
function fast_webx_blank_template_cleaner() {
    if ( is_page_template( 'template-blank.php' ) ) {
        // --- 1. Remove Estilos e Scripts do Tema ---
        wp_dequeue_style( 'fast-webx-style' );
        wp_dequeue_style( 'fwx-bio' );
        wp_dequeue_style( 'fwx-ads' );
        wp_dequeue_style( 'fwx-home' );
        wp_dequeue_style( 'fwx-page' );
        wp_dequeue_style( 'fwx-sidebar' );
        wp_dequeue_style( 'fwx-cards' );
        wp_dequeue_script( 'fast-webx-main' );
        wp_dequeue_script( 'fwx-progress' );
        wp_dequeue_script( 'fwx-lightbox' );

        // --- 2. Remove Bloat Nativo do WordPress (Core) ---
        wp_dequeue_style( 'wp-block-library' );
        wp_dequeue_style( 'wp-block-library-theme' );
        wp_dequeue_style( 'global-styles' );
        wp_dequeue_style( 'classic-theme-styles' );

        // --- 3. Remove CSS Dinâmico do Tema (variáveis com !important) ---
        remove_action( 'wp_head', 'fast_webx_custom_css', 10 );

        // --- 4. Remove Scripts Nativos de Dark Mode ---
        remove_action( 'wp_head', 'fwx_dark_mode_loader', 5 );
        remove_action( 'wp_footer', 'fwx_dark_mode_toggle_script', 20 );

        // --- 5. Remove Banner LGPD ---
        remove_action( 'wp_footer', 'fwx_render_lgpd_banner', 100 );

        // --- 6. Remove global-styles-inline-css (CSS inline do editor de blocos / Gutenberg) ---
        wp_deregister_style( 'global-styles' );
        wp_deregister_style( 'wp-global-styles' );
        // WP 6.5+ registra via função própria no wp_enqueue_scripts
        remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
        remove_action( 'wp_footer', 'wp_enqueue_global_styles_assets_for_block_types' );

        // --- 7. Remove Speculation Rules (prefetch WP 6.5+ - desnecessário em LPs) ---
        remove_action( 'wp_footer', 'wp_print_speculation_rules' );
        add_filter( 'wp_speculation_rules_configuration', '__return_empty_array' );

        // --- 8. Remove bloat do <head> (RSS, JSON API, shortlink, robots, auto-sizes) ---
        remove_action( 'wp_head', 'feed_links', 2 );
        remove_action( 'wp_head', 'feed_links_extra', 3 );
        remove_action( 'wp_head', 'rsd_link' );
        remove_action( 'wp_head', 'wlwmanifest_link' );
        remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
        remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
        remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
        remove_action( 'wp_head', 'wp_robots', 1 );
        // Remove o inline CSS de auto-sizes de imagens (wp-img-auto-sizes-contain-inline-css)
        remove_action( 'wp_head', 'wp_img_tag_add_auto_sizes', 10 );
        add_filter( 'wp_img_tag_add_auto_sizes', '__return_false' );
    }
}
add_action( 'wp_enqueue_scripts', 'fast_webx_blank_template_cleaner', 999 );


/**
 * Limpeza agressiva no <head> para métrica 100/100
 */
function fast_webx_clean_head() {
    // Remove Emojis nativos do WP (Reduz requests HTTP e JS inline)
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );

    // Remove tags oEmbed, RSD, wlwmanifest e generator
    remove_action( 'wp_head', 'wp_generator' );
    remove_action( 'wp_head', 'wlwmanifest_link' );
    remove_action( 'wp_head', 'rsd_link' );
    remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
    remove_action( 'wp_head', 'wp_oembed_add_host_js' );
}
add_action( 'init', 'fast_webx_clean_head' );

/**
 * Adicionar Classes Dinâmicas no Body
 */
function fast_webx_body_classes( $classes ) {
    if ( fwx_get_option( 'hide_sidebar_mobile', 0 ) ) {
        $classes[] = 'fwx-mobile-hide-sidebar';
    }
    return $classes;
}
add_filter( 'body_class', 'fast_webx_body_classes' );

/**
 * Helper para obter opções do tema de forma centralizada.
 * Permite que valor 0 (zero) intencional do checkbox não resete pro fallback original.
 */
function fwx_get_option( $option_name, $default = '' ) {
    $options = get_option( 'fast_webx_options' );
    if ( isset( $options[ $option_name ] ) && $options[ $option_name ] !== '' ) {
        return $options[ $option_name ];
    }
    return $default;
}

/**
 * Injeção de Variáveis CSS e Modo Escuro Otimizada
 */
function fast_webx_custom_css() {
    $primary    = fwx_get_option( 'primary_color', '#0073aa' );
    $secondary  = fwx_get_option( 'secondary_color', '#00aa8c' );
    $text_color = fwx_get_option( 'text_color', '#222222' );
    $text_color_dark = fwx_get_option( 'text_color_dark', '#dddddd' );
    $link_color = fwx_get_option( 'link_color', '#0073aa' );
    $link_color_dark = fwx_get_option( 'link_color_dark', '#66b3cc' );
    $cat_text_color = fwx_get_option( 'cat_text_color', '' );
    $hero_color = fwx_get_option( 'hero_color', '#f7f7f7' );
    $hero_color_dark = fwx_get_option( 'hero_color_dark', '#222222' );
    $hero_text_color = fwx_get_option( 'hero_text_color', '#222222' );
    $hero_text_color_dark = fwx_get_option( 'hero_text_color_dark', '#dddddd' );

    $typography = fwx_get_option( 'typography', 'system' );
    $font_family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif';

    $letter_spacing = 'normal';
    $font_face_css = '';
    $theme_uri = get_template_directory_uri();

    switch ( $typography ) {
        case 'inter':
            $font_family = '"Inter", sans-serif';
            $font_face_css = "
@font-face {
    font-family: 'Inter';
    font-style: normal;
    font-weight: 400;
    font-display: swap;
    src: url('" . esc_url( $theme_uri . '/assets/fonts/inter-v20-latin-regular.woff2' ) . "') format('woff2');
}
@font-face {
    font-family: 'Inter';
    font-style: normal;
    font-weight: 700;
    font-display: swap;
    src: url('" . esc_url( $theme_uri . '/assets/fonts/inter-v20-latin-700.woff2' ) . "') format('woff2');
}";
            break;
        case 'montserrat':
            $font_family = '"Montserrat", sans-serif';
            $font_face_css = "
@font-face {
    font-family: 'Montserrat';
    font-style: normal;
    font-weight: 400;
    font-display: swap;
    src: url('" . esc_url( $theme_uri . '/assets/fonts/montserrat-v31-latin-regular.woff2' ) . "') format('woff2');
}
@font-face {
    font-family: 'Montserrat';
    font-style: normal;
    font-weight: 700;
    font-display: swap;
    src: url('" . esc_url( $theme_uri . '/assets/fonts/montserrat-v31-latin-700.woff2' ) . "') format('woff2');
}";
            break;
        case 'poppins':
            $font_family = '"Poppins", sans-serif';
            $font_face_css = "
@font-face {
    font-family: 'Poppins';
    font-style: normal;
    font-weight: 400;
    font-display: swap;
    src: url('" . esc_url( $theme_uri . '/assets/fonts/poppins-v24-latin-regular.woff2' ) . "') format('woff2');
}
@font-face {
    font-family: 'Poppins';
    font-style: normal;
    font-weight: 700;
    font-display: swap;
    src: url('" . esc_url( $theme_uri . '/assets/fonts/poppins-v24-latin-700.woff2' ) . "') format('woff2');
}";
            break;
        case 'questrial':
            $font_family = '"Questrial", sans-serif';
            $letter_spacing = '0.6px';
            $font_face_css = "
@font-face {
    font-family: 'Questrial';
    font-style: normal;
    font-weight: 400;
    font-display: swap;
    src: url('" . esc_url( $theme_uri . '/assets/fonts/questrial-v19-latin-regular.woff2' ) . "') format('woff2');
}";
            break;
        case 'roboto':
            $font_family = '"Roboto", sans-serif';
            $font_face_css = "
@font-face {
    font-family: 'Roboto';
    font-style: normal;
    font-weight: 400;
    font-display: swap;
    src: url('" . esc_url( $theme_uri . '/assets/fonts/roboto-v51-latin-regular.woff2' ) . "') format('woff2');
}
@font-face {
    font-family: 'Roboto';
    font-style: normal;
    font-weight: 700;
    font-display: swap;
    src: url('" . esc_url( $theme_uri . '/assets/fonts/roboto-v51-latin-700.woff2' ) . "') format('woff2');
}";
            break;
    }

    // Helper para converter HEX em RGB para uso em sombras/transparências
    list($r, $g, $b) = sscanf($primary, "#%02x%02x%02x");
    $primary_rgb = "$r, $g, $b";

    echo '<style id="fast-webx-dynamic-css">';
    if ( ! empty( $font_face_css ) ) {
        echo $font_face_css;
    }
    echo ":root {";
    echo "--fwx-primary: " . esc_attr( $primary ) . ";";
    echo "--fwx-primary-rgb: " . esc_attr( $primary_rgb ) . ";";
    echo "--fwx-secondary: " . esc_attr( $secondary ) . ";";
    echo "--fwx-text-color: " . esc_attr( $text_color ) . ";";
    echo "--fwx-link-color: " . esc_attr( $link_color ) . ";";
    if ( ! empty( $cat_text_color ) ) {
        echo "--fwx-cat-text-color: " . esc_attr( $cat_text_color ) . ";";
    }
    echo "--fwx-hero-color: " . esc_attr( $hero_color ) . ";";
    echo "--fwx-hero-text-color: " . esc_attr( $hero_text_color ) . ";";

    echo "--fwx-font-family: " . $font_family . ";";
    echo "--fwx-letter-spacing: " . $letter_spacing . ";";
    echo "--fwx-bg-color: #ffffff;";
    echo "--fwx-border-opacity: 0.05;";
    echo "}";
    
    // Força o letter-spacing em elementos de UI que costumam resetar herança
    if ( $typography === 'questrial' ) {
        echo "button, input, select, textarea, .main-navigation a, .footer-navigation a, .fwx-btn, .button, .btn, .wp-block-button__link, .entry-title a, .fwx-card-title, .fwx-post-card h3, label, .fwx-label, .site-title a, .fwx-sidebar-rel-title a, .fwx-related-post-title a, .fwx-lgpd-btn { letter-spacing: var(--fwx-letter-spacing) !important; }";
    }
    
    // Força o escuro com hierarquia altíssima
    echo "html[data-theme='dark'] {";
    echo "--fwx-bg-color: #1a1a1a !important;";
    echo "--fwx-text-color: " . esc_attr( $text_color_dark ) . " !important;";
    echo "--fwx-link-color: " . esc_attr( $link_color_dark ) . " !important;";
    echo "--fwx-hero-color: " . esc_attr( $hero_color_dark ) . " !important;";
    echo "--fwx-hero-text-color: " . esc_attr( $hero_text_color_dark ) . " !important;";
    echo "--fwx-border-opacity: 0.1 !important;";
    echo "}";
    
    if ( fwx_get_option( 'sticky_sidebar_widget', 0 ) ) {
        echo "@media (min-width: 993px) { #secondary .widget:last-child { position: sticky; top: 100px; z-index: 10; } }";
    }

    if ( fwx_get_option( 'enable_link_margin', 0 ) ) {
        // Aplica apenas em links dentro de parágrafos, listas e blocos de citação (texto corrido)
        // Exclui especificamente o Sumário (TOC) e Botões de Bio para não "sujar" o design
        echo ".entry-content p a:not(.fwx-link-tree-btn), .entry-content li:not(.fwx-toc-list li, .fwx-link-tree-btn) a, .entry-content blockquote a:not(.fwx-link-tree-btn) { border-bottom: 1.5px solid rgba(var(--fwx-primary-rgb), 0.5); padding-bottom: 1px; transition: var(--fwx-transition); font-weight: 700; }";
        echo ".entry-content p a:not(.fwx-link-tree-btn):hover, .entry-content li:not(.fwx-toc-list li, .fwx-link-tree-btn) a:hover, .entry-content blockquote a:not(.fwx-link-tree-btn):hover { border-bottom-color: transparent; }";
    }

    // Injeção de cores personalizadas de categorias
    $categories = get_categories( array( 'hide_empty' => false ) );
    if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
        foreach ( $categories as $cat ) {
            $cat_id = $cat->term_id;
            $cat_color = get_term_meta( $cat_id, 'category_color', true );
            if ( ! empty( $cat_color ) ) {
                echo ".fwx-cat-label.fwx-cat-id-" . esc_attr( $cat_id ) . " { background-color: " . esc_attr( $cat_color ) . "; }";
                echo ".fwx-cat-label.fwx-cat-id-" . esc_attr( $cat_id ) . ":hover { background-color: var(--fwx-secondary); color: var(--fwx-cat-text-color, #ffffff); transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }";
            }
        }
    }

    echo '</style>';
}
add_action( 'wp_head', 'fast_webx_custom_css', 10 );

/**
 * Filtro de Classes de Body (Sticky Header)
 */
function fwx_body_classes( $classes ) {
    if ( fwx_get_option( 'sticky_header', 0 ) ) {
        $classes[] = 'fwx-sticky-header';
    }
    return $classes;
}
add_filter( 'body_class', 'fwx_body_classes' );

/**
 * Script Bloqueador de FOUC para Modo Escuro
 * Checa o localStorage imediatamente no <head> para que o body já carregue com o layout correto.
 */
function fwx_dark_mode_loader() {
    ?>
    <script>
    (function() {
        const savedTheme = localStorage.getItem('fwx_theme');
        if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    })();
    </script>
    <?php
}
add_action( 'wp_head', 'fwx_dark_mode_loader', 5 );

/**
 * JS para o botão de Alternar Modo Escuro do Front-end
 */
function fwx_dark_mode_toggle_script() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('fwx-dark-mode-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                if (currentTheme === 'dark') {
                    document.documentElement.removeAttribute('data-theme');
                    localStorage.setItem('fwx_theme', 'light');
                } else {
                    document.documentElement.setAttribute('data-theme', 'dark');
                    localStorage.setItem('fwx_theme', 'dark');
                }
            });
        }
    });
    </script>
    <?php
}
add_action( 'wp_footer', 'fwx_dark_mode_toggle_script', 20 );

/**
 * Otimizações de Desempenho Condicionais (controladas pelo Painel)
 */
function fast_webx_performance_optimizations() {

    // Gravatar desativado (substituição gerida na rotina de pre_get_avatar)


    // Bloquear RSS feeds (redireciona para home)
    if ( fwx_get_option( 'perf_disable_rss', 0 ) ) {
        add_action( 'do_feed',       'fwx_disable_feed', 1 );
        add_action( 'do_feed_rdf',   'fwx_disable_feed', 1 );
        add_action( 'do_feed_rss',   'fwx_disable_feed', 1 );
        add_action( 'do_feed_rss2',  'fwx_disable_feed', 1 );
        add_action( 'do_feed_atom',  'fwx_disable_feed', 1 );
    }

    // Bloquear tamanhos extras de imagem do WP
    if ( fwx_get_option( 'perf_disable_extra_image_sizes', 0 ) ) {
        add_filter( 'intermediate_image_sizes_advanced', 'fwx_block_extra_image_sizes' );
    }

    // Desativar Heartbeat no front-end
    if ( fwx_get_option( 'perf_disable_heartbeat', 0 ) ) {
        add_action( 'init', function() {
            if ( ! is_admin() ) {
                wp_deregister_script( 'heartbeat' );
            }
        } );
    }
}
add_action( 'init', 'fast_webx_performance_optimizations', 5 );


function fwx_disable_feed() {
    wp_redirect( esc_url( home_url( '/' ) ), 301 );
    exit;
}

function fwx_block_extra_image_sizes( $sizes ) {
    // Remove tamanhos nativos do WP, mantém somente os do Fast WebX
    unset( $sizes['thumbnail'] );
    unset( $sizes['medium'] );
    unset( $sizes['medium_large'] );
    unset( $sizes['large'] );
    unset( $sizes['1536x1536'] );
    unset( $sizes['2048x2048'] );
    return $sizes;
}

/**
 * Breadcrumbs nativos (sem plugin, sem JS)
 * Schema-ready para SEO
 */
function fwx_breadcrumbs() {
    if ( is_front_page() ) {
        return; // Sem breadcrumb na home
    }
    if ( is_single() && ! fwx_get_option( 'show_single_breadcrumb', 0 ) ) {
        return;
    }
    if ( is_page() && ! fwx_get_option( 'show_page_breadcrumb', 0 ) ) {
        return;
    }

    $separator = '<span class="fwx-bc-sep" aria-hidden="true">›</span>';
    $items = array();
    $schema_items = array();
    $position = 1;

    // Home sempre primeiro
    $items[] = '<a href="' . esc_url( home_url('/') ) . '" class="fwx-bc-item">' . esc_html( fwx_t( 'footer_home' ) ) . '</a>';
    $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => fwx_t( 'footer_home' ), 'item' => home_url('/') );
    $position++;

    if ( is_single() ) {
        if ( is_singular( 'fwx_produto' ) ) {
            $archive_url = get_post_type_archive_link( 'fwx_produto' );
            $loja_label  = fwx_get_option( 'loja_catalog_title', 'Loja' );

            $terms = get_the_terms( get_the_ID(), 'fwx_categoria_produto' );
            if ( $terms && ! is_wp_error( $terms ) ) {
                $items[] = '<a href="' . esc_url( $archive_url ) . '" class="fwx-bc-item">' . esc_html( $loja_label ) . '</a>';
                $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => $loja_label, 'item' => $archive_url );
                $position++;

                $term = array_shift( $terms );
                $items[] = '<span class="fwx-bc-item fwx-bc-current">' . esc_html( $term->name ) . '</span>';
                $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => $term->name, 'item' => get_term_link( $term ) );
                $position++;
            } else {
                $items[] = '<span class="fwx-bc-item fwx-bc-current">' . esc_html( $loja_label ) . '</span>';
                $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => $loja_label, 'item' => $archive_url );
                $position++;
            }
        } else {
            // Se houver uma página de posts definida (Blog), adiciona ela
            $blog_page_id = get_option( 'page_for_posts' );
            $blog_title   = 'Blog';
            $blog_url     = '';
            if ( $blog_page_id ) {
                $blog_title = get_the_title( $blog_page_id );
                $blog_url   = get_permalink( $blog_page_id );
            }

            // Categoria do post
            $cats = get_the_category();
            if ( $cats ) {
                if ( $blog_url ) {
                    $items[] = '<a href="' . esc_url( $blog_url ) . '" class="fwx-bc-item">' . esc_html( $blog_title ) . '</a>';
                    $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => $blog_title, 'item' => $blog_url );
                    $position++;
                }

                $cat = $cats[0];
                $items[] = '<span class="fwx-bc-item fwx-bc-current">' . esc_html( $cat->name ) . '</span>';
                $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => $cat->name, 'item' => get_category_link( $cat->term_id ) );
                $position++;
            } else {
                if ( $blog_url ) {
                    $items[] = '<span class="fwx-bc-item fwx-bc-current">' . esc_html( $blog_title ) . '</span>';
                    $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => $blog_title, 'item' => $blog_url );
                    $position++;
                }
            }
        }
    } elseif ( is_home() && ! is_front_page() ) {
        $blog_page_id = get_option( 'page_for_posts' );
        $blog_title   = $blog_page_id ? get_the_title( $blog_page_id ) : 'Blog';
        $items[]      = '<span class="fwx-bc-item fwx-bc-current">' . esc_html( $blog_title ) . '</span>';
        $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => $blog_title, 'item' => get_permalink( $blog_page_id ) );
        $position++;
    } elseif ( is_post_type_archive( 'fwx_produto' ) ) {
        $loja_label  = fwx_get_option( 'loja_catalog_title', 'Loja' );
        $archive_url = get_post_type_archive_link( 'fwx_produto' );
        $items[] = '<span class="fwx-bc-item fwx-bc-current">' . esc_html( $loja_label ) . '</span>';
        $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => $loja_label, 'item' => $archive_url );
        $position++;
    } elseif ( is_tax( 'fwx_categoria_produto' ) ) {
        $archive_url = get_post_type_archive_link( 'fwx_produto' );
        $loja_label  = fwx_get_option( 'loja_catalog_title', 'Loja' );

        $items[] = '<a href="' . esc_url( $archive_url ) . '" class="fwx-bc-item">' . esc_html( $loja_label ) . '</a>';
        $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => $loja_label, 'item' => $archive_url );
        $position++;

        $term = get_queried_object();
        $items[] = '<span class="fwx-bc-item fwx-bc-current">' . esc_html( $term->name ) . '</span>';
        $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => $term->name, 'item' => get_term_link( $term ) );
        $position++;
    } elseif ( is_page() ) {
        $ancestors = get_post_ancestors( get_the_ID() );
        if ( $ancestors ) {
            foreach ( array_reverse( $ancestors ) as $anc_id ) {
                $items[] = '<a href="' . esc_url( get_permalink( $anc_id ) ) . '" class="fwx-bc-item">' . esc_html( get_the_title( $anc_id ) ) . '</a>';
                $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => get_the_title( $anc_id ), 'item' => get_permalink( $anc_id ) );
                $position++;
            }
        }
    } elseif ( is_category() ) {
        $blog_page_id = get_option( 'page_for_posts' );
        if ( $blog_page_id ) {
            $blog_title = get_the_title( $blog_page_id );
            $blog_url   = get_permalink( $blog_page_id );
            $items[]    = '<a href="' . esc_url( $blog_url ) . '" class="fwx-bc-item">' . esc_html( $blog_title ) . '</a>';
            $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => $blog_title, 'item' => $blog_url );
            $position++;
        }
        $cat_title = single_cat_title('', false);
        $items[] = '<span class="fwx-bc-item fwx-bc-current">' . esc_html( $cat_title ) . '</span>';
        $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => $cat_title, 'item' => get_category_link( get_queried_object_id() ) );
        $position++;
    } elseif ( is_tag() ) {
        $blog_page_id = get_option( 'page_for_posts' );
        if ( $blog_page_id ) {
            $blog_title = get_the_title( $blog_page_id );
            $blog_url   = get_permalink( $blog_page_id );
            $items[]    = '<a href="' . esc_url( $blog_url ) . '" class="fwx-bc-item">' . esc_html( $blog_title ) . '</a>';
            $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => $blog_title, 'item' => $blog_url );
            $position++;
        }
        $tag_title = single_tag_title('', false);
        $items[] = '<span class="fwx-bc-item fwx-bc-current">' . esc_html( $tag_title ) . '</span>';
        $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => $tag_title, 'item' => get_tag_link( get_queried_object_id() ) );
        $position++;
    } elseif ( is_author() ) {
        $blog_page_id = get_option( 'page_for_posts' );
        if ( $blog_page_id ) {
            $blog_title = get_the_title( $blog_page_id );
            $blog_url   = get_permalink( $blog_page_id );
            $items[]    = '<a href="' . esc_url( $blog_url ) . '" class="fwx-bc-item">' . esc_html( $blog_title ) . '</a>';
            $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => $blog_title, 'item' => $blog_url );
            $position++;
        }
        $author = get_queried_object();
        $author_name = $author ? $author->display_name : 'Autor';
        $items[] = '<span class="fwx-bc-item fwx-bc-current">' . esc_html( $author_name ) . '</span>';
        $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => $author_name, 'item' => get_author_posts_url( get_queried_object_id() ) );
        $position++;
    } elseif ( is_date() ) {
        $blog_page_id = get_option( 'page_for_posts' );
        if ( $blog_page_id ) {
            $blog_title = get_the_title( $blog_page_id );
            $blog_url   = get_permalink( $blog_page_id );
            $items[]    = '<a href="' . esc_url( $blog_url ) . '" class="fwx-bc-item">' . esc_html( $blog_title ) . '</a>';
            $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => $blog_title, 'item' => $blog_url );
            $position++;
        }
        $date_title = '';
        if ( is_year() ) {
            $date_title = get_the_date('Y');
        } elseif ( is_month() ) {
            $date_title = get_the_date('F Y');
        } elseif ( is_day() ) {
            $date_title = get_the_date('j F Y');
        }
        $items[] = '<span class="fwx-bc-item fwx-bc-current">' . esc_html( $date_title ) . '</span>';
        $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => $date_title, 'item' => get_year_link( get_the_date('Y') ) ); // simplificado
        $position++;
    } elseif ( is_search() ) {
        $items[] = '<span class="fwx-bc-item fwx-bc-current">' . esc_html( fwx_t( 'archive_search_label' ) ) . ': ' . esc_html( get_search_query() ) . '</span>';
        $schema_items[] = array( '@type' => 'ListItem', 'position' => $position, 'name' => fwx_t( 'archive_search_label' ) . ': ' . get_search_query(), 'item' => home_url() . '?s=' . urlencode( get_search_query() ) );
        $position++;
    }

    // Schema JSON-LD para Breadcrumbs
    $schema = array( '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $schema_items );
    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";

    echo '<nav class="fwx-breadcrumbs" aria-label="' . esc_attr( fwx_t( 'breadcrumbs_nav_label' ) ) . '">';
    echo implode( $separator, $items );
    echo '</nav>';
}

/**
 * Posts Relacionados (mesma categoria, excluindo post atual)
 */
function fwx_related_posts() {
    global $post;
    if ( ! fwx_get_option( 'enable_related_posts', 0 ) ) {
        return;
    }
    if ( ! is_single() ) {
        return;
    }

    $transient_key = 'fwx_related_main_' . get_the_ID();
    $cached_html = get_transient( $transient_key );
    if ( false !== $cached_html && fwx_get_option( 'perf_widget_transients', 0 ) ) {
        echo $cached_html;
        return;
    }

    $cats = wp_get_post_categories( get_the_ID() );
    if ( empty( $cats ) ) {
        return;
    }

    $args = array(
        'category__in'        => $cats,
        'post__not_in'        => array( get_the_ID() ),
        'posts_per_page'      => 3,
        'ignore_sticky_posts' => 1,
        'orderby'             => 'rand',
    );
    $related = get_posts( $args );

    if ( empty( $related ) ) {
        return;
    }

    ob_start();

    echo '<section class="fwx-related-posts">';
    echo '<h3 class="fwx-related-title">' . esc_html( fwx_t( 'single_related_title' ) ) . '</h3>';
    echo '<div class="fwx-related-grid">';

    foreach ( $related as $post ) {
        setup_postdata( $post );
        echo '<article class="fwx-related-item">';
        echo '<a href="' . esc_url( get_permalink( $post->ID ) ) . '" class="fwx-related-thumb" tabindex="-1" aria-hidden="true">';
        fwx_the_post_thumbnail( $post->ID, 'fwx-home-grid', array( 'loading' => 'lazy', 'decoding' => 'async' ) );
        echo '</a>';
        echo '<h4 class="fwx-related-post-title"><a href="' . esc_url( get_permalink( $post->ID ) ) . '">' . esc_html( get_the_title( $post->ID ) ) . '</a></h4>';
        fwx_render_post_meta();
        echo '</article>';
    }

    wp_reset_postdata();
    echo '</div></section>';
    $html = ob_get_clean();
    if ( fwx_get_option( 'perf_widget_transients', 0 ) ) {
        set_transient( $transient_key, $html, 12 * HOUR_IN_SECONDS );
    }
    echo $html;
}

/**
 * Verifica se a página atual pertence à seção da Loja (CPT ou taxonomia fwx_produto)
 */
function fwx_is_loja_page() {
    if ( ! fwx_get_option( 'loja_enabled', 0 ) ) {
        return false;
    }
    return is_post_type_archive( 'fwx_produto' ) || is_singular( 'fwx_produto' ) || is_tax( 'fwx_categoria_produto' );
}

/**
 * Widget Automático de Posts Relacionados na Sidebar
 * Injetado via hook no início do sidebar, se a opção estiver ativa
 */
function fwx_sidebar_related_posts() {
    if ( ! fwx_get_option( 'enable_sidebar_related', 0 ) ) {
        return;
    }

    $show = false;
    if ( is_singular( 'post' ) && fwx_get_option( 'related_widget_in_single_post', 0 ) ) {
        $show = true;
    } elseif ( is_singular( 'fwx_produto' ) && fwx_get_option( 'related_widget_in_single_product', 0 ) ) {
        $show = true;
    } elseif ( ( is_home() || is_archive() || is_search() ) && ! fwx_is_loja_page() && fwx_get_option( 'related_widget_in_archive_post', 0 ) ) {
        $show = true;
    } elseif ( fwx_is_loja_page() && fwx_get_option( 'related_widget_in_archive_product', 0 ) ) {
        $show = true;
    } elseif ( is_page() && ! fwx_is_loja_page() && fwx_get_option( 'related_widget_in_single_post', 0 ) ) {
        $show = true;
    }

    if ( ! $show ) {
        return;
    }

    global $wp_query;
    $exclude_ids = array();
    $transient_key = 'fwx_sidebar_recentes';

    // Se estivermos em um post único, excluí-lo
    if ( is_single() && get_the_ID() ) {
        $exclude_ids[] = get_the_ID();
        $transient_key = 'fwx_sidebar_recentes_' . get_the_ID();
    } 
    // Se estivermos numa listagem (home, categoria, busca), exclui os posts já mostrados na tela principal
    elseif ( ( is_home() || is_archive() || is_search() ) && ! empty( $wp_query->posts ) ) {
        $exclude_ids = wp_list_pluck( $wp_query->posts, 'ID' );
        $transient_key = 'fwx_sidebar_recentes_list_' . md5( implode( ',', $exclude_ids ) );
    }

    $cached_html = get_transient( $transient_key );
    if ( false !== $cached_html && fwx_get_option( 'perf_widget_transients', 0 ) ) {
        echo $cached_html;
        return;
    }

    $args = array(
        'post_type'           => 'post',
        'posts_per_page'      => 5,
        'ignore_sticky_posts' => 1,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'post_status'         => 'publish',
    );

    if ( ! empty( $exclude_ids ) ) {
        $args['post__not_in'] = $exclude_ids;
    }

    $posts = get_posts( $args );

    if ( empty( $posts ) ) {
        return;
    }

    ob_start();
    global $post;

    echo '<section class="widget fwx-sidebar-related">';
    echo '<h2 class="widget-title">' . esc_html( fwx_t( 'single_sidebar_recent' ) ) . '</h2>';

    foreach ( $posts as $post ) {
        setup_postdata( $post );
        echo '<article class="fwx-sidebar-rel-item">';
        echo '<a href="' . esc_url( get_permalink( $post->ID ) ) . '" class="fwx-sidebar-rel-thumb" tabindex="-1" aria-hidden="true">';
        fwx_the_post_thumbnail( $post->ID, 'fwx-compact-thumb', array( 'loading' => 'lazy', 'decoding' => 'async' ) );
        echo '</a>';
        echo '<div class="fwx-sidebar-rel-info">';
        echo '<h3 class="fwx-sidebar-rel-title"><a href="' . esc_url( get_permalink( $post->ID ) ) . '">' . esc_html( get_the_title( $post->ID ) ) . '</a></h3>';
        fwx_render_post_meta();
        echo '</div>';
        echo '</article>';
    }

    wp_reset_postdata();
    echo '</section>';

    $html = ob_get_clean();
    if ( fwx_get_option( 'perf_widget_transients', 0 ) ) {
        set_transient( $transient_key, $html, 6 * HOUR_IN_SECONDS );
    }
    echo $html;
}

/**
 * Renderização Centralizada da Sidebar (com Ordenação Configurável)
 * Controla a ordem: Posts Recentes do Tema vs Widgets do WordPress
 */
function fwx_render_sidebar() {
    // Clean DOM: Não renderiza a sidebar em dispositivos móveis se o usuário ativou a opção de ocultação no painel.
    if ( fwx_get_option( 'hide_sidebar_mobile', 0 ) && wp_is_mobile() ) {
        return;
    }

    $last_widget = fwx_get_option( 'sidebar_last_widget', 'wp_widgets' );

    // Carrega funções se não existirem (embora já devam estar em memória)
    if ( ! function_exists( 'fwx_sidebar_author_widget' ) ) {
        require_once get_template_directory() . '/inc/author-authority.php';
    }

    $widgets = array(
        'author'      => 'fwx_sidebar_author_widget',
        'lead'        => 'fwx_sidebar_lead_widget',
        'related'     => 'fwx_sidebar_related_posts',
        'categories'  => 'fwx_sidebar_categories',
        'loja_recent' => 'fwx_sidebar_recent_produtos',
        'wp_widgets'  => function() { dynamic_sidebar( 'sidebar-1' ); }
    );

    // Se o módulo loja estiver desativado, desativa totalmente o widget de produtos recentes e limpa o sticky dele
    if ( ! fwx_get_option( 'loja_enabled', 0 ) ) {
        unset( $widgets['loja_recent'] );
        if ( 'loja_recent' === $last_widget ) {
            $last_widget = 'wp_widgets';
        }
    }

    // Remove o widget escolhido como último da ordem normal
    $last_func = $widgets[$last_widget] ?? $widgets['wp_widgets'];
    unset($widgets[$last_widget]);

    // Renderiza os normais
    foreach ( $widgets as $key => $func ) {
        if ( is_callable( $func ) ) {
            call_user_func( $func );
        }
    }

    // Renderiza o último (que ficará sticky se ativo)
    if ( is_callable( $last_func ) ) {
        call_user_func( $last_func );
    }
}

/**
 * Widget Automático de Categorias na Sidebar (5 mais populares)
 */
function fwx_sidebar_categories() {
    if ( ! fwx_get_option( 'enable_sidebar_categories', 0 ) ) {
        return;
    }

    $show = false;
    $is_loja = false;
    if ( function_exists( 'fwx_is_loja_page' ) && fwx_is_loja_page() ) {
        $is_loja = true;
    }

    if ( is_singular( 'post' ) && fwx_get_option( 'categories_widget_in_single_post', 0 ) ) {
        $show = true;
    } elseif ( is_singular( 'fwx_produto' ) && fwx_get_option( 'categories_widget_in_single_product', 0 ) ) {
        $show = true;
    } elseif ( $is_loja && fwx_get_option( 'categories_widget_in_archive_product', 0 ) ) {
        $show = true;
    } elseif ( ! $is_loja && ( is_home() || is_archive() || is_search() ) && fwx_get_option( 'categories_widget_in_archive_post', 0 ) ) {
        $show = true;
    } elseif ( ! $is_loja && is_page() && fwx_get_option( 'categories_widget_in_single_post', 0 ) ) {
        $show = true;
    }

    if ( ! $show ) {
        return;
    }

    $transient_key = 'fwx_sidebar_categories';
    $html = '';

    if ( fwx_get_option( 'perf_widget_transients', 0 ) ) {
        $html = get_transient( $transient_key );
        if ( ! empty( $html ) ) {
            echo $html;
            return;
        }
    }

    $categories = get_categories( array(
        'orderby'    => 'count',
        'order'      => 'DESC',
        'number'     => 5,
        'hide_empty' => true,
    ) );

    if ( empty( $categories ) || is_wp_error( $categories ) ) {
        return;
    }

    ob_start();
    echo '<section class="widget fwx-sidebar-categories">';
    echo '<h2 class="widget-title">' . esc_html( fwx_t( 'categories_title' ) ) . '</h2>';
    echo '<div class="fwx-sidebar-categories-list">';
    foreach ( $categories as $cat ) {
        $cat_color = get_term_meta( $cat->term_id, 'category_color', true );
        $bg_color  = ! empty( $cat_color ) ? $cat_color : 'var(--fwx-primary)';
        $cat_link  = get_term_link( $cat );
        if ( is_wp_error( $cat_link ) ) {
            continue;
        }
        echo sprintf(
            '<a href="%s" class="fwx-sidebar-cat-btn" style="background-color: %s;">',
            esc_url( $cat_link ),
            esc_attr( $bg_color )
        );
        echo '<span class="fwx-sidebar-cat-name">' . esc_html( $cat->name ) . '</span>';
        echo '</a>';
    }
    echo '</div>';
    echo '</section>';

    $html = ob_get_clean();

    if ( fwx_get_option( 'perf_widget_transients', 0 ) ) {
        set_transient( $transient_key, $html, 6 * HOUR_IN_SECONDS );
    }
    echo $html;
}

/**
 * Limpar cache de Transients ao salvar post
 */
function fwx_clear_related_transients( $post_id ) {
    delete_transient( 'fwx_related_main_' . $post_id );
    delete_transient( 'fwx_sidebar_recentes_' . $post_id );
    delete_transient( 'fwx_sidebar_recentes' );
    delete_transient( 'fwx_related_sidebar_' . $post_id ); // retrocompatibilidade
    delete_transient( 'fwx_related_produtos_' . $post_id );
    delete_transient( 'fwx_sidebar_produtos_recentes' );
    delete_transient( 'fwx_sidebar_produtos_recentes_' . $post_id );
    delete_transient( 'fwx_sidebar_categories' );
}
add_action( 'save_post', 'fwx_clear_related_transients' );

/**
 * Avatar Local - Suporte a Imagem de Perfil via Media Library
 */

// 1. Mostrar o campo no perfil com Modal de Recorte Interativa
function fwx_user_profile_avatar_field( $user ) {
    if ( ! current_user_can( 'upload_files' ) ) return;
    $avatar_url = get_user_meta( $user->ID, 'fwx_local_avatar', true );
    wp_enqueue_media();
    ?>
    <h3>Avatar Local (Fast WebX)</h3>
    <table class="form-table">
        <tr>
            <th><label for="fwx_local_avatar">Imagem do Perfil</label></th>
            <td>
                <div class="fwx-avatar-wrapper" style="display: inline-block;">
                    <?php if ( $avatar_url ) : ?>
                        <img src="<?php echo esc_url( $avatar_url ); ?>" style="max-width:100px; display:block; margin-bottom:10px; border-radius:50%; border: 2px solid var(--fwx-border, #ccd0d4);" />
                    <?php else : ?>
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 80 80'%3E%3Ccircle cx='40' cy='40' r='40' fill='%23e0e0e0'/%3E%3C/svg%3E" style="max-width:100px; display:block; margin-bottom:10px; border-radius:50%;" />
                    <?php endif; ?>
                </div>
                <input type="text" name="fwx_local_avatar" id="fwx_local_avatar" value="<?php echo esc_url( $avatar_url ); ?>" class="regular-text" style="margin-bottom:10px; display: block;" />
                <button type="button" class="button fwx-upload-avatar-btn">Selecionar da Biblioteca</button>
                <button type="button" class="button fwx-remove-avatar-btn" style="margin-left:5px; color:#d63638; border-color:#d63638; <?php echo $avatar_url ? '' : 'display:none;'; ?>">Remover Avatar</button>
                <p class="description">Escolha qualquer imagem. O sistema abrirá uma modal para você recortar a área perfeita e a salvará fisicamente como <strong>WebP (300x300px)</strong> para máxima velocidade e nitidez na Home.</p>

                <!-- Modal de Recorte Premium Fast WebX -->
                <div id="fwx-avatar-crop-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); align-items: center; justify-content: center; z-index: 999999; box-sizing: border-box;">
                    <div style="background: #ffffff; padding: 25px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2); width: 340px; box-sizing: border-box; text-align: center; border: 1px solid rgba(226, 232, 240, 0.8); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                        <h4 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 600; color: #0f172a;">Ajustar Foto de Perfil</h4>
                        <p style="margin: 0 0 20px 0; font-size: 13px; color: #64748b;">Arraste e ajuste o zoom para enquadrar seu rosto</p>
                        
                        <!-- Viewport do Crop -->
                        <div id="fwx-crop-viewport" style="width: 250px; height: 250px; margin: 0 auto 20px; overflow: hidden; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; position: relative; cursor: move; box-shadow: inset 0 2px 4px rgba(0,0,0,0.06);">
                            <img id="fwx-crop-image" src="" style="position: absolute; top: 0; left: 0; user-select: none; pointer-events: none; -webkit-user-drag: none; max-width: none;" />
                            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 50%; box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.6); pointer-events: none; z-index: 10; border: 2px solid #3b82f6;"></div>
                        </div>

                        <!-- Zoom Slider -->
                        <div style="margin-bottom: 25px; text-align: left;">
                            <label style="font-size: 12px; font-weight: 500; color: #475569; display: block; margin-bottom: 8px;">Zoom da Imagem</label>
                            <input type="range" id="fwx-crop-zoom" min="1" max="3" step="0.01" value="1" style="width: 100%; height: 6px; background: #e2e8f0; border-radius: 3px; outline: none; transition: background 450ms ease-in; -webkit-appearance: none; cursor: pointer;" />
                        </div>

                        <!-- Botões de Ação -->
                        <div style="display: flex; gap: 10px; justify-content: space-between;">
                            <button type="button" class="button" id="fwx-crop-cancel" style="flex: 1; height: 40px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; color: #334155; font-weight: 500; cursor: pointer; transition: all 0.2s;">Cancelar</button>
                            <button type="button" class="button button-primary" id="fwx-crop-save" style="flex: 1; height: 40px; border-radius: 8px; border: none; background: #2271b1; color: #ffffff; font-weight: 500; cursor: pointer; transition: all 0.2s;">Cortar e Salvar</button>
                        </div>
                    </div>
                </div>

                <script>
                jQuery(document).ready(function($){
                    var avatar_uploader;
                    var raw_img_url;
                    var $modal = $('#fwx-avatar-crop-modal');
                    var $cropImg = $('#fwx-crop-image');
                    var $slider = $('#fwx-crop-zoom');
                    var $viewport = $('#fwx-crop-viewport');
                    
                    var isDragging = false;
                    var startX, startY;
                    var imgLeft = 0, imgTop = 0;
                    var imgWidth = 0, imgHeight = 0;
                    var baseWidth = 0, baseHeight = 0;
                    var zoom = 1;
                    
                    $('.fwx-upload-avatar-btn').click(function(e) {
                        e.preventDefault();
                        if (avatar_uploader) { avatar_uploader.open(); return; }
                        avatar_uploader = wp.media({ title: 'Escolher Foto de Perfil', button: { text: 'Usar Imagem' }, multiple: false });
                        avatar_uploader.on('select', function() {
                            var attachment = avatar_uploader.state().get('selection').first().toJSON();
                            raw_img_url = attachment.url;
                            
                            $cropImg.attr('src', raw_img_url);
                            $cropImg.css({ left: 0, top: 0, width: 'auto', height: 'auto' });
                            
                            $cropImg.off('load').on('load', function() {
                                var w = this.naturalWidth;
                                var h = this.naturalHeight;
                                
                                if ( w > h ) {
                                    baseHeight = 250;
                                    baseWidth = ( w / h ) * 250;
                                } else {
                                    baseWidth = 250;
                                    baseHeight = ( h / w ) * 250;
                                }
                                
                                imgWidth = baseWidth;
                                imgHeight = baseHeight;
                                imgLeft = (250 - imgWidth) / 2;
                                imgTop = (250 - imgHeight) / 2;
                                
                                $cropImg.css({
                                    width: imgWidth + 'px',
                                    height: imgHeight + 'px',
                                    left: imgLeft + 'px',
                                    top: imgTop + 'px'
                                });
                                
                                $slider.val(1);
                                zoom = 1;
                                $modal.css('display', 'flex').hide().fadeIn(250);
                            });
                        });
                        avatar_uploader.open();
                    });

                    $('.fwx-remove-avatar-btn').click(function(e) {
                        e.preventDefault();
                        if (confirm('Deseja realmente remover seu avatar personalizado?')) {
                            $('#fwx_local_avatar').val('');
                            $('.fwx-avatar-wrapper img').attr('src', "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 80 80'%3E%3Ccircle cx='40' cy='40' r='40' fill='%23e0e0e0'/%3E%3C/svg%3E");
                            $('.fwx-remove-avatar-btn').hide();
                        }
                    });
                    
                    $slider.on('input', function() {
                        var newZoom = parseFloat($(this).val());
                        var wDiff = (baseWidth * newZoom) - imgWidth;
                        var hDiff = (baseHeight * newZoom) - imgHeight;
                        
                        imgLeft -= wDiff / 2;
                        imgTop -= hDiff / 2;
                        
                        imgWidth = baseWidth * newZoom;
                        imgHeight = baseHeight * newZoom;
                        zoom = newZoom;
                        
                        keepInBounds();
                        
                        $cropImg.css({
                            width: imgWidth + 'px',
                            height: imgHeight + 'px',
                            left: imgLeft + 'px',
                            top: imgTop + 'px'
                        });
                    });
                    
                    $viewport.on('mousedown touchstart', function(e) {
                        e.preventDefault();
                        isDragging = true;
                        var touch = e.touches ? e.touches[0] : (e.originalEvent && e.originalEvent.touches ? e.originalEvent.touches[0] : e);
                        startX = touch.clientX - imgLeft;
                        startY = touch.clientY - imgTop;
                    });
                    
                    $(document).on('mousemove touchmove', function(e) {
                        if (!isDragging) return;
                        var touch = e.touches ? e.touches[0] : (e.originalEvent && e.originalEvent.touches ? e.originalEvent.touches[0] : e);
                        
                        imgLeft = touch.clientX - startX;
                        imgTop = touch.clientY - startY;
                        
                        keepInBounds();
                        
                        $cropImg.css({
                            left: imgLeft + 'px',
                            top: imgTop + 'px'
                        });
                    });
                    
                    $(document).on('mouseup touchend', function() {
                        isDragging = false;
                    });
                    
                    function keepInBounds() {
                        if (imgLeft > 0) imgLeft = 0;
                        if (imgTop > 0) imgTop = 0;
                        if (imgLeft + imgWidth < 250) imgLeft = 250 - imgWidth;
                        if (imgTop + imgHeight < 250) imgTop = 250 - imgHeight;
                    }
                    
                    $('#fwx-crop-cancel').click(function(e) {
                        e.preventDefault();
                        $modal.fadeOut(200);
                    });
                    
                    $('#fwx-crop-save').click(function(e) {
                        e.preventDefault();
                        
                        var canvas = document.createElement('canvas');
                        canvas.width = 300;
                        canvas.height = 300;
                        var ctx = canvas.getContext('2d');
                        
                        var natW = $cropImg[0].naturalWidth;
                        var natH = $cropImg[0].naturalHeight;
                        
                        var sX = -imgLeft * (natW / imgWidth);
                        var sY = -imgTop * (natH / imgHeight);
                        var sW = 250 * (natW / imgWidth);
                        var sH = 250 * (natH / imgHeight);
                        
                        ctx.drawImage($cropImg[0], sX, sY, sW, sH, 0, 0, 300, 300);
                        
                        var base64Data = canvas.toDataURL('image/webp', 0.9);
                        
                        var $saveBtn = $('#fwx-crop-save');
                        $saveBtn.prop('disabled', true).text('Processando...');
                        
                        $.post(ajaxurl, {
                            action: 'fwx_save_cropped_avatar',
                            image_data: base64Data,
                            user_id: $('#user_id').val() || <?php echo get_current_user_id(); ?>,
                            security: '<?php echo wp_create_nonce("fwx_avatar_crop_nonce"); ?>'
                        }, function(response) {
                            $saveBtn.prop('disabled', false).text('Cortar e Salvar');
                            if (response.success) {
                                $('#fwx_local_avatar').val(response.data.url);
                                $('.fwx-avatar-wrapper img').attr('src', response.data.url);
                                $('.fwx-remove-avatar-btn').show();
                                $modal.fadeOut(200);
                            } else {
                                alert('Erro ao salvar avatar: ' + response.data);
                            }
                        });
                    });
                });
                </script>
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'fwx_user_profile_avatar_field' );
add_action( 'edit_user_profile', 'fwx_user_profile_avatar_field' );

// 2. Salvar o campo manualmente (em caso de alterações manuais no input de texto)
function fwx_save_user_profile_avatar( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) return false;
    if ( isset( $_POST['fwx_local_avatar'] ) ) {
        update_user_meta( $user_id, 'fwx_local_avatar', esc_url_raw( $_POST['fwx_local_avatar'] ) );
    }
}
add_action( 'personal_options_update', 'fwx_save_user_profile_avatar' );
add_action( 'edit_user_profile_update', 'fwx_save_user_profile_avatar' );

// AJAX para gravação física do avatar recortado em WebP
function fwx_ajax_save_cropped_avatar() {
    check_ajax_referer( 'fwx_avatar_crop_nonce', 'security' );
    
    $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
    if ( ! $user_id || ! current_user_can( 'edit_user', $user_id ) ) {
        wp_send_json_error( 'Sem permissão.' );
    }
    
    $image_data = isset( $_POST['image_data'] ) ? $_POST['image_data'] : '';
    if ( empty( $image_data ) ) {
        wp_send_json_error( 'Nenhum dado de imagem recebido.' );
    }
    
    // Limpar cabeçalhos base64
    $image_data = str_replace( 'data:image/webp;base64,', '', $image_data );
    $image_data = str_replace( 'data:image/jpeg;base64,', '', $image_data );
    $image_data = str_replace( 'data:image/png;base64,', '', $image_data );
    $image_data = str_replace( ' ', '+', $image_data );
    $decoded_data = base64_decode( $image_data );
    
    if ( ! $decoded_data ) {
        wp_send_json_error( 'Dados de imagem inválidos.' );
    }
    
    $filename = 'avatar_' . $user_id . '_' . time() . '.webp';
    
    // Gravação nativa do arquivo no diretório de uploads do WordPress
    $upload = wp_upload_bits( $filename, null, $decoded_data );
    
    if ( isset( $upload['error'] ) && $upload['error'] !== false ) {
        wp_send_json_error( $upload['error'] );
    }
    
    // Usar o editor nativo do WordPress para garantir a consistência e qualidade em .webp (90%)
    $file_path = $upload['file'];
    $editor = wp_get_image_editor( $file_path );
    if ( ! is_wp_error( $editor ) ) {
        $editor->resize( 300, 300, true );
        $editor->set_quality( 90 );
        $editor->save( $file_path, 'image/webp' );
    }
    
    // Atualizar o meta do usuário imediatamente no banco de dados para salvar de forma instantânea
    update_user_meta( $user_id, 'fwx_local_avatar', esc_url_raw( $upload['url'] ) );
    
    wp_send_json_success( array( 'url' => $upload['url'] ) );
}
add_action( 'wp_ajax_fwx_save_cropped_avatar', 'fwx_ajax_save_cropped_avatar' );

// 3. Substituir o get_avatar nativo
function fwx_override_pre_get_avatar( $avatar, $id_or_email, $args ) {
    $user_id = false;
    if ( is_numeric( $id_or_email ) ) {
        $user_id = (int) $id_or_email;
    } elseif ( is_object( $id_or_email ) ) {
        if ( ! empty( $id_or_email->user_id ) ) {
            $user_id = (int) $id_or_email->user_id;
        } elseif ( ! empty( $id_or_email->ID ) ) {
            $user_id = (int) $id_or_email->ID;
        }
    } else {
        $user = get_user_by( 'email', $id_or_email );
        if ( $user ) $user_id = $user->ID;
    }
    
    if ( $user_id ) {
        $local_avatar = get_user_meta( $user_id, 'fwx_local_avatar', true );
        if ( $local_avatar ) {
            $size = isset( $args['size'] ) ? $args['size'] : 96;
            $class = isset( $args['class'] ) ? $args['class'] : 'avatar avatar-' . $size . ' photo';
            $alt = isset( $args['alt'] ) ? $args['alt'] : '';
            return sprintf(
                "<img alt='%s' src='%s' class='%s' height='%d' width='%d' loading='lazy' decoding='async' />",
                esc_attr( $alt ),
                esc_url( $local_avatar ),
                esc_attr( $class ),
                (int) $size,
                (int) $size
            );
        }
    }

    if ( fwx_get_option( 'perf_disable_gravatar', 0 ) ) {
        $size = isset( $args['size'] ) ? $args['size'] : 96;
        return '<img src="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 80 80\'%3E%3Ccircle cx=\'40\' cy=\'40\' r=\'40\' fill=\'%23ccc\'/%3E%3C/svg%3E" width="'.$size.'" height="'.$size.'" alt="Avatar Genérico" class="avatar">';
    }

    return $avatar;
}
add_filter( 'pre_get_avatar', 'fwx_override_pre_get_avatar', 10, 3 );

/**
 * Impede requisições de URL de Gravatar externo se a opção de desativar estiver ligada.
 */
function fwx_override_get_avatar_url( $url, $id_or_email, $args ) {
    $user_id = false;
    if ( is_numeric( $id_or_email ) ) {
        $user_id = (int) $id_or_email;
    } elseif ( is_object( $id_or_email ) ) {
        if ( ! empty( $id_or_email->user_id ) ) {
            $user_id = (int) $id_or_email->user_id;
        } elseif ( ! empty( $id_or_email->ID ) ) {
            $user_id = (int) $id_or_email->ID;
        }
    } else {
        $user = get_user_by( 'email', $id_or_email );
        if ( $user ) $user_id = $user->ID;
    }

    if ( $user_id ) {
        $local_avatar = get_user_meta( $user_id, 'fwx_local_avatar', true );
        if ( $local_avatar ) {
            return $local_avatar;
        }
    }

    if ( fwx_get_option( 'perf_disable_gravatar', 0 ) ) {
        return 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 80 80\'%3E%3Ccircle cx=\'40\' cy=\'40\' r=\'40\' fill=\'%23ccc\'/%3E%3C/svg%3E';
    }

    return $url;
}
add_filter( 'get_avatar_url', 'fwx_override_get_avatar_url', 10, 3 );


/**
 * Exibe a thumbnail do post com fallback para imagem padrão.
 */
/**
 * 2. Otimização de Imagens Responsivas (Sizes Filter) - PRIORIDADE MÁXIMA
 * Força o navegador a reconhecer o tamanho real de exibição dos cards.
 */
function fwx_force_optimized_image_sizes( $sizes, $size ) {
    // Se for o nosso tamanho de grid (seja pelo nome ou pelas dimensões)
    if ( 
        ( is_string( $size ) && strpos( $size, 'fwx-home-grid' ) !== false ) || 
        ( is_array( $size ) && isset($size[0]) && $size[0] === 600 ) 
    ) {
        // No desktop (acima de 1100px), a imagem tem ~400px. No mobile, ocupa a largura toda.
        return '(max-width: 600px) 100vw, (max-width: 1100px) 50vw, 410px';
    }
    
    if ( ( is_string( $size ) && strpos( $size, 'fwx-home-featured' ) !== false ) || ( is_array( $size ) && isset($size[0]) && $size[0] === 900 ) ) {
        return '(max-width: 900px) 100vw, 820px';
    }

    if ( is_string( $size ) && strpos( $size, 'fwx-news-list' ) !== false ) {
        return '(max-width: 480px) 100vw, 300px';
    }

    return $sizes;
}
add_filter( 'wp_calculate_image_sizes', 'fwx_force_optimized_image_sizes', 9999, 2 );

/**
 * Exibe a thumbnail do post com fallback para imagem padrão.
 */
function fwx_the_post_thumbnail( $post_id = null, $size = 'post-thumbnail', $attr = array() ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }
    
    // Força o atributo sizes se for um tamanho conhecido do tema
    if ( ! isset( $attr['sizes'] ) ) {
        if ( 'fwx-home-grid' === $size ) {
            $attr['sizes'] = '(max-width: 600px) 100vw, (max-width: 1100px) 50vw, 410px';
        }
    }

    if ( has_post_thumbnail( $post_id ) ) {
        echo get_the_post_thumbnail( $post_id, $size, $attr );
    } else {
        $fallback_type = fwx_get_option( 'thumbnail_fallback_type', 'default' );
        
        // Se a opção for "none" (Sem Imagem), não exibe nada
        if ( 'none' === $fallback_type ) {
            return;
        }

        $default_image = '';
        if ( 'custom' === $fallback_type ) {
            $default_image = fwx_get_option( 'custom_thumbnail_fallback', '' );
        }

        // Se for "default" ou se a personalizada estiver vazia, usa a padrão do tema
        if ( empty( $default_image ) ) {
            $default_image = get_template_directory_uri() . '/assets/img/default-thumb.webp';
        }

        $class = isset( $attr['class'] ) ? $attr['class'] : '';
        $loading = isset( $attr['loading'] ) ? $attr['loading'] : 'lazy';
        $priority = isset( $attr['fetchpriority'] ) ? ' fetchpriority="' . esc_attr( $attr['fetchpriority'] ) . '"' : '';
        // Removidas dimensões fixas para permitir que o CSS controle a fluidez conforme o layout (Single vs Grid)
        echo '<img src="' . esc_url( $default_image ) . '" alt="' . esc_attr( get_the_title( $post_id ) ) . '" class="fwx-default-thumb ' . esc_attr( $class ) . '" loading="' . esc_attr( $loading ) . '" decoding="async"' . $priority . ' />';
    }
}

/**
 * Controle de Tamanho do Excerpt (Resumo)
 * Padronizado em 20 palavras para alinhamento dos cartões.
 */
function fwx_custom_excerpt_length( $length ) {
    return 20;
}
add_filter( 'excerpt_length', 'fwx_custom_excerpt_length', 999 );

function fwx_custom_excerpt_more( $more ) {
    return '...';
}
add_filter( 'excerpt_more', 'fwx_custom_excerpt_more' );

/**
 * YouTube Iframe Lazy Loading (Placeholder Baseado em Clique)
 */
function fwx_lazy_load_youtube( $content ) {
    if ( is_admin() || ! fwx_get_option( 'perf_lazy_youtube', 0 ) ) return $content;
    
    $pattern = '/<iframe[^>]+src="https?:\/\/(?:www\.)?(?:youtube\.com\/embed\/|youtu\.be\/)([a-zA-Z0-9_-]+)[^"]*"[^>]*><\/iframe>/is';
    
    $replacement = function($matches) {
        $video_id = $matches[1];
        $thumb_url = "https://img.youtube.com/vi/{$video_id}/hqdefault.jpg";
        
        $html = '<div class="fwx-yt-lazy" data-embed="'.esc_attr($video_id).'" style="position:relative; cursor:pointer; width:100%; aspect-ratio:16/9; background:#000 url('.esc_url($thumb_url).') center/cover no-repeat; border-radius:8px; overflow:hidden; margin-bottom:20px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">';
        $html .= '<div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:68px; height:48px; background:rgba(255,0,0,0.9); border-radius:14px; display:flex; justify-content:center; align-items:center; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">';
        $html .= '<svg width="24" height="24" viewBox="0 0 24 24" fill="#ffffff"><path d="M8 5v14l11-7z"/></svg>';
        $html .= '</div></div>';
        
        global $fwx_has_yt_lazy;
        $fwx_has_yt_lazy = true;
        
        return $html;
    };
    
    return preg_replace_callback( $pattern, $replacement, $content );
}
add_filter( 'the_content', 'fwx_lazy_load_youtube' );

function fwx_lazy_load_youtube_script() {
    global $fwx_has_yt_lazy;
    if ( ! $fwx_has_yt_lazy ) return;
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var lazyVideos = [].slice.call(document.querySelectorAll('.fwx-yt-lazy'));
        lazyVideos.forEach(function(video) {
            video.addEventListener('click', function() {
                var iframe = document.createElement('iframe');
                iframe.setAttribute('frameborder', '0');
                iframe.setAttribute('allowfullscreen', '1');
                iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
                iframe.setAttribute('src', 'https://www.youtube.com/embed/' + this.dataset.embed + '?rel=0&showinfo=0&autoplay=1');
                iframe.style.width = '100%';
                iframe.style.height = '100%';
                iframe.style.position = 'absolute';
                iframe.style.top = '0';
                iframe.style.left = '0';
                iframe.style.borderRadius = '8px';
                this.innerHTML = '';
                this.appendChild(iframe);
                this.style.background = '#000';
            });
        });
    });
    </script>
    <?php
}
add_action( 'wp_footer', 'fwx_lazy_load_youtube_script' );
/**
 * Scripts de Terceiros Gerenciados via inc/ads-manager.php
 */

/**
 * Shortcodes para o Rodapé
 */
function fwx_site_desc_shortcode() {
    return get_bloginfo( 'description' );
}
add_shortcode( 'fwx_site_desc', 'fwx_site_desc_shortcode' );

function fwx_social_links_shortcode() {
    $networks = array(
        'facebook'  => fwx_get_option( 'social_facebook' ),
        'instagram' => fwx_get_option( 'social_instagram' ),
        'youtube'   => fwx_get_option( 'social_youtube' ),
        'tiktok'    => fwx_get_option( 'social_tiktok' ),
        'linkedin'  => fwx_get_option( 'social_linkedin' ),
        'twitter'   => fwx_get_option( 'social_twitter' ),
        'pinterest' => fwx_get_option( 'social_pinterest' ),
        'whatsapp'  => fwx_get_option( 'social_whatsapp' ),
    );

    $output = '<div class="footer-social-grid">';
    
    foreach ( $networks as $name => $url ) {
        if ( ! empty( $url ) ) {
            $output .= sprintf(
                '<a href="%s" class="footer-social-link" target="_blank" rel="noopener noreferrer" aria-label="%s">%s</a>',
                esc_url( $url ),
                esc_attr( ucfirst( $name ) ),
                fwx_get_social_icon_svg( $name )
            );
        }
    }
    
    $output .= '</div>';
    return $output;
}
add_shortcode( 'fwx_social_links', 'fwx_social_links_shortcode' );

/**
 * Customização: Suporte a Shortcodes para Elementos Nativos e Widgets (Roadmap Item 14)
 */
// 1. Habilitar execução de shortcodes em Widgets de Texto nativos
add_filter( 'widget_text', 'do_shortcode' );
// Suporte a shortcodes em Widgets de Bloco (HTML Personalizado) e áreas de texto
add_filter( 'widget_text_content', 'do_shortcode' );
// Suporte a shortcodes no rodapé e em hooks de texto dinâmico
add_filter( 'the_excerpt', 'do_shortcode' );

// 2. Criar Shortcodes para os blocos da Sidebar e Relacionados
add_shortcode( 'fwx_sidebar_author', function() {
    ob_start();
    if ( function_exists( 'fwx_sidebar_author_widget' ) ) {
        fwx_sidebar_author_widget();
    }
    return ob_get_clean();
});

add_shortcode( 'fwx_sidebar_related', function() {
    ob_start();
    if ( function_exists( 'fwx_sidebar_related_posts' ) ) {
        fwx_sidebar_related_posts();
    }
    return ob_get_clean();
});

add_shortcode( 'fwx_related_posts', function() {
    ob_start();
    if ( function_exists( 'fwx_related_posts' ) ) {
        fwx_related_posts();
    }
    return ob_get_clean();
});

// 3. Shortcodes para Elementos da Single Post
function fwx_render_share_buttons_shortcode() {
    if ( ! fwx_get_option( 'show_sharing_buttons', 0 ) ) return '';
    ob_start();
    $post_url   = urlencode( get_permalink() );
    $post_title = urlencode( get_the_title() );
    ?>
    <div class="fwx-share-buttons">
        <span class="fwx-share-title"><?php echo esc_html( fwx_t( 'share_title' ) ); ?></span>
        <a href="https://api.whatsapp.com/send?text=<?php echo $post_title . ' - ' . $post_url; ?>" target="_blank" rel="noopener noreferrer" class="fwx-btn-share fwx-whatsapp" aria-label="<?php echo esc_attr( fwx_t( 'share_whatsapp_label' ) ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg></a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $post_url; ?>" target="_blank" rel="noopener noreferrer" class="fwx-btn-share fwx-facebook" aria-label="<?php echo esc_attr( fwx_t( 'share_facebook_label' ) ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg></a>
        <a href="https://twitter.com/intent/tweet?url=<?php echo $post_url; ?>&text=<?php echo $post_title; ?>" target="_blank" rel="noopener noreferrer" class="fwx-btn-share fwx-twitter" aria-label="<?php echo esc_attr( fwx_t( 'share_twitter_label' ) ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4l11.733 16h4.267l-11.733 -16z"></path><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772"></path></svg></a>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'fwx_share_buttons', 'fwx_render_share_buttons_shortcode' );

function fwx_render_author_box_shortcode() {
    if ( ! fwx_get_option( 'enable_author_box', 0 ) ) return '';
    ob_start();

    $options = get_option( 'fast_webx_options' );
    $bio_user_id = isset( $options['bio_user_id'] ) ? $options['bio_user_id'] : '';

    if ( ! empty( $bio_user_id ) ) {
        $author_id = absint( $bio_user_id );
    } else {
        $author_id = get_the_author_meta( 'ID' );
        if ( ! $author_id ) {
            $users = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
            $author_id = ! empty( $users ) ? $users[0]->ID : 0;
        }
    }

    if ( ! $author_id ) return '';

    // Fallbacks Customizados
    $custom_name = isset( $options['bio_custom_name'] ) ? $options['bio_custom_name'] : '';
    $custom_bio  = isset( $options['bio_custom_text'] ) ? $options['bio_custom_text'] : '';

    $author_name = ! empty( $custom_name ) ? $custom_name : get_the_author_meta( 'display_name', $author_id );
    $author_bio  = ! empty( $custom_bio ) ? $custom_bio : get_the_author_meta( 'description', $author_id );
    ?>
    <div class="fwx-author-box">
        <div class="fwx-author-avatar">
            <?php echo get_avatar( $author_id, 80 ); ?>
        </div>
        <div class="fwx-author-info">
            <h3 class="fwx-author-name"><?php echo esc_html( $author_name ); ?></h3>
            <p class="fwx-author-bio"><?php echo wp_kses_post( $author_bio ); ?></p>
            <?php $author_url = get_the_author_meta( 'user_url' ); ?>
            <?php if ( $author_url ) : ?>
                <a href="<?php echo esc_url( $author_url ); ?>" target="_blank" rel="noopener noreferrer" class="fwx-author-link"><?php echo esc_html( fwx_t( 'author_site_link' ) ); ?></a>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'fwx_author_box', 'fwx_render_author_box_shortcode' );

/**
 * Tema "Ready to Use" (Configurações Out-of-the-Box)
 * 
 * Mapeamento COMPLETO de todas as chaves registradas no tema.
 * Filosofia: O tema nasce "seco" — funcionalidades premium desligadas,
 * mas com identidade visual funcional para não quebrar na ativação.
 * 
 * Categorias de defaults:
 *   ✅ Design/Visual: Valores amigáveis (cores, tipografia, layouts)
 *   ⛔ Funcionalidades: 0, false ou '' (nascem desligadas)
 *   🔄 Lógica Inversa: hide_* = 0 (recurso VISÍVEL por padrão)
 */
function fwx_get_default_options() {
    return array(

            // ═══════════════════════════════════════════
            // ✅ ABA: DESIGN — Cores Globais
            // ═══════════════════════════════════════════
            'primary_color'             => '#0073aa',
            
            'secondary_color'           => '#00aa8c',
            
            'text_color'                => '#222222',
            'text_color_dark'           => '#dddddd',
            
            'hero_color'                => '#f7f7f7',
            'hero_color_dark'           => '#222222',
            
            'hero_text_color'           => '#222222',
            'hero_text_color_dark'      => '#dddddd',
            
            'link_color'                => '#0073aa',
            'link_color_dark'           => '#66b3cc',
            'cat_text_color'            => '',

            // ✅ ABA: DESIGN — Tipografia e Visual
            'custom_logo_dark'          => '',
            'sticky_header'             => 0,
            'enable_header_search'      => 0,
            'enable_header_mode_toggle' => 0,
            'header_cta_enable'         => 0,
            'header_cta_text'           => 'Fale Conosco',
            'header_cta_url'            => '#',
            'typography'                => 'system',
            'home_layout'               => 'layout-a',
            'category_layout'           => 'global',
            'hero_layout'               => 'layout-1',
            'single_post_layout'        => 'layout-1',

            // ═══════════════════════════════════════════
            // ⛔ ABA: LANDING PAGE — Config. da Home
            // ═══════════════════════════════════════════
            'home_lp_type'              => 'autor',

            // LP: Cores Específicas
            'home_lp_color'             => '#dd3333',
            'home_lp_text_color'        => '#ffffff',
            
            'home_hero_bg_color'        => '#f7f7f7',
            'home_hero_bg_color_dark'   => '#222222',
            
            'home_hero_text_color'      => '#222222',
            'home_hero_text_color_dark' => '#dddddd',

            // LP: Seções Ativas (Modular)
            'home_show_hero'            => 0,
            'home_show_about'           => 0,
            'home_show_services'        => 0,
            'home_show_steps'           => 0,
            'home_show_blog'            => 0,
            'home_show_capture'         => 0,
            'home_show_testimonials'    => 0,
            'home_show_contact'         => 0,

            // LP: Hero (Topo)
            'home_hero_title'           => '',
            'home_hero_desc'            => '',
            'home_hero_btn_text'        => '',
            'home_hero_btn_url'         => '',
            'home_hero_img'             => '',
            'home_hero_media_type'      => 'image',
            'home_hero_video'           => '',

            // LP: Sobre / Bio
            'home_about_title'          => 'Sobre Nós',
            'home_about_text'           => '',

            // LP: Serviços
            'home_services_title'       => 'Nossos Serviços',
            'home_services_desc'        => '',
            'home_services_count'       => '3',
            'home_service_title_1'      => '',
            'home_service_desc_1'       => '',
            'home_service_link_1'       => '',
            'home_service_new_tab_1'    => 0,
            'home_service_title_2'      => '',
            'home_service_desc_2'       => '',
            'home_service_link_2'       => '',
            'home_service_new_tab_2'    => 0,
            'home_service_title_3'      => '',
            'home_service_desc_3'       => '',
            'home_service_link_3'       => '',
            'home_service_new_tab_3'    => 0,
            'home_service_title_4'      => '',
            'home_service_desc_4'       => '',
            'home_service_link_4'       => '',
            'home_service_new_tab_4'    => 0,
            'home_service_title_5'      => '',
            'home_service_desc_5'       => '',
            'home_service_link_5'       => '',
            'home_service_new_tab_5'    => 0,
            'home_service_title_6'      => '',
            'home_service_desc_6'       => '',
            'home_service_link_6'       => '',
            'home_service_new_tab_6'    => 0,

            // LP: Fluxo (3 Etapas)
            'home_steps_title'          => 'Como Funciona',
            'home_steps_desc'           => '',
            'home_step_1_title'         => '',
            'home_step_1_desc'          => '',
            'home_step_2_title'         => '',
            'home_step_2_desc'          => '',
            'home_step_3_title'         => '',
            'home_step_3_desc'          => '',

            // LP: Captura
            'home_capture_title'        => '',
            'home_capture_desc'         => '',
            'home_capture_shortcode'    => '',

            // LP: Testemunhos
            'home_testimonials_title'   => 'O que dizem nossos clientes',
            'home_testimonials_desc'    => '',
            'home_testimonials_count'   => '3',
            'home_testimonial_1_text'   => '',
            'home_testimonial_1_author' => '',
            'home_testimonial_1_img'    => '',
            'home_testimonial_2_text'   => '',
            'home_testimonial_2_author' => '',
            'home_testimonial_2_img'    => '',
            'home_testimonial_3_text'   => '',
            'home_testimonial_3_author' => '',
            'home_testimonial_3_img'    => '',
            'home_testimonial_4_text'   => '',
            'home_testimonial_4_author' => '',
            'home_testimonial_4_img'    => '',
            'home_testimonial_5_text'   => '',
            'home_testimonial_5_author' => '',
            'home_testimonial_5_img'    => '',
            'home_testimonial_6_text'   => '',
            'home_testimonial_6_author' => '',
            'home_testimonial_6_img'    => '',

            // LP: Blog & Contato
            'home_blog_title'           => 'Últimas do Blog',
            'home_blog_desc'            => '',
            'home_contact_title'        => 'Fale Conosco',
            'home_contact_desc'         => 'Tire suas dúvidas ou peça um orçamento sem compromisso.',
            'home_contact_content'      => '[fwx_contact_form]',

            // ═══════════════════════════════════════════
            // ⛔ ABA: LOJA — Módulo de Catálogo de Produtos
            // ═══════════════════════════════════════════
            'loja_enabled'              => 0,   // ⛔ Desligada por padrão (CPT opt-in)
            'loja_catalog_title'        => 'Nossa Loja',
            'loja_catalog_desc'         => '',
            'loja_cta_text'             => 'Ver Produto',
            'loja_single_message'       => '',  // Mensagem HTML abaixo do CTA (suporta links)
            'loja_columns'              => '3',
            'loja_show_price'           => 0,
            'loja_enable_related'       => 0,
            'disable_ads_loja_single'   => 0,

            // ═══════════════════════════════════════════
            // ⛔ ABA: CONTEÚDO — Toggles de Recursos
            // ═══════════════════════════════════════════
            'sidebar_posts'             => 0,
            'sidebar_pages'             => 0,
            'sidebar_archive'           => 0,
            'hide_sidebar_mobile'       => 0,   
            'hide_single_thumb'         => 1,
            'hide_page_thumb'           => 1,
            'show_single_excerpt'       => 0,
            'show_single_breadcrumb'    => 0,
            'show_page_breadcrumb'      => 0,
            'enable_toc'                => 0,
            'toc_start_collapsed'       => 0,
            'enable_sticky_toc'         => 0,
            'enable_author_box'         => 0,
            'show_sharing_buttons'      => 0,
            'enable_lightbox'           => 0,
            'enable_reading_progress'   => 0,
            'enable_tts'                => 0,
            'enable_back_to_top'        => 0,
            'enable_related_posts'      => 0,
            'enable_sidebar_author'     => 0,
            'enable_sidebar_lead_widget' => 0,
            'enable_sidebar_related'    => 0,
            'sticky_sidebar_widget'     => 0,
            'sidebar_last_widget'       => 'wp_widgets',
            'sidebar_widget_order'      => '',
            'enable_link_margin'        => 0,
            'thumbnail_fallback_type'   => 'default',
            'custom_thumbnail_fallback' => '',
            'show_single_tags'          => 0,

            // Sidebar: Locais de exibição da Loja (dependem de loja_enabled)
            'sidebar_loja'              => 0,
            'sidebar_loja_single'       => 0,

            // Widget Autor — Locais de exibição
            'author_widget_in_single_post'      => 0,
            'author_widget_in_single_product'   => 0,
            'author_widget_in_archive_post'     => 0,
            'author_widget_in_archive_product'  => 0,

            // Widget Leads — Locais de exibição
            'lead_widget_in_single_post'        => 0,
            'lead_widget_in_single_product'     => 0,
            'lead_widget_in_archive_post'       => 0,
            'lead_widget_in_archive_product'    => 0,

            // Widget Relacionados — Locais de exibição
            'related_widget_in_single_post'     => 0,
            'related_widget_in_single_product'  => 0,
            'related_widget_in_archive_post'    => 0,
            'related_widget_in_archive_product' => 0,

            // Widget Produtos Recentes (Loja) — Locais de exibição
            'loja_enable_sidebar_recent'                => 0,
            'recent_products_widget_in_single_post'     => 0,
            'recent_products_widget_in_single_product'  => 0,
            'recent_products_widget_in_archive_post'    => 0,
            'recent_products_widget_in_archive_product' => 0,

            // Widget Categorias — Locais de exibição
            'enable_sidebar_categories'                 => 0,
            'categories_widget_in_single_post'          => 0,
            'categories_widget_in_single_product'       => 0,
            'categories_widget_in_archive_post'         => 0,
            'categories_widget_in_archive_product'      => 0,

            // ═══════════════════════════════════════════
            // ⛔ ABA: BIO & AUTOR — Redes Sociais e Perfil
            // ═══════════════════════════════════════════
            'social_facebook'           => '',
            'social_instagram'          => '',
            'social_youtube'            => '',
            'social_tiktok'             => '',
            'social_linkedin'           => '',
            'social_twitter'            => '',
            'social_pinterest'          => '',
            'social_whatsapp'           => '',

            // Página Link In Bio
            'bio_user_id'               => '',
            'bio_custom_name'           => '',
            'bio_custom_text'           => '',
            'bio_footer_text'           => '',
            'bio_highlight_id'          => '',
            'bio_link_text_1'           => '',
            'bio_link_url_1'            => '',
            'bio_link_text_2'           => '',
            'bio_link_url_2'            => '',
            'bio_link_text_3'           => '',
            'bio_link_url_3'            => '',
            'bio_link_text_4'           => '',
            'bio_link_url_4'            => '',
            'bio_link_text_5'           => '',
            'bio_link_url_5'            => '',

            // ═══════════════════════════════════════════
            // ⛔ ABA: ADS & LEADS
            // ═══════════════════════════════════════════
            'ad_block_content_1'        => '',
            'ad_injection_point_1'      => 'none',
            'ad_block_content_2'        => '',
            'ad_injection_point_2'      => 'none',
            'ad_block_content_3'        => '',
            'ad_injection_point_3'      => 'none',

            // Ads: Textos da Captura
            'enable_whatsapp_capture'   => 0,
            'lead_email_optional'       => 0,
            'lead_form_title'           => 'Cadastre-se na nossa Newsletter',
            'lead_form_desc'            => 'Receba as melhores dicas e atualizações semanais gratuitamente.',
            'lead_form_lgpd_msg'        => 'Eu concordo com a Política de Privacidade e aceito receber comunicações (LGPD).',

            // Ads: Integrações
            'enable_contact_form'       => 0,
            'webhook_url'               => '',
            'lead_redirect_url'         => '',

            // Ads: Exit Intent
            'enable_exit_intent'        => 0,
            'exit_intent_title'         => 'espere um pouco',
            'exit_intent_desc'          => 'tenho um presente pra você',
            'exit_intent_frequency'     => 'always',

            // ═══════════════════════════════════════════
            // ⛔ ABA: PERFORMANCE
            // ═══════════════════════════════════════════
            'perf_disable_emojis'       => 0,
            'perf_disable_gravatar'     => 0,
            'perf_disable_rss'          => 0,
            'perf_disable_extra_image_sizes' => 0,
            'perf_disable_heartbeat'    => 0,
            'perf_auto_webp_upload'     => 0,
            'perf_widget_transients'    => 0,
            'perf_lazy_youtube'         => 0,
            'disable_comments'          => 0,

            // ═══════════════════════════════════════════
            // ✅ ABA: RODAPÉ & SCRIPTS
            // ═══════════════════════════════════════════
            'footer_desc'               => '<strong>[nome_site]</strong><br><br>[fwx_site_desc]<br><br>Aproveitem!⚡',
            'footer_social'             => '[fwx_social_links]<br><br>Para conhecer mais dos nossos produtos acesse: <a href="https://urlaki.com/bio" target="_blank">Mesaque M.</a>',
            'footer_aux_text'           => 'Construído para <strong>SEO</strong> e Performance.',
            'footer_text'               => '© [ano] <a href="/">[nome_site]</a>. Todos os direitos reservados.',

            // LGPD
            'lgpd_text'                 => 'Nós usamos cookies para melhorar sua experiência diária.',
            'lgpd_policy_url'           => '#',

            // Scripts
            'stealth_mode'              => 0,
            'head_scripts'              => '',
            'body_scripts'              => '',
            'footer_scripts'            => '',

            // ═══════════════════════════════════════════
            // ⛔ ABA: SEO
            // ═══════════════════════════════════════════
            'home_meta_desc'            => '',
            'enable_excerpt_as_meta'    => 0,
            'enable_cat_desc_as_meta'   => 0,
            'auto_img_alt'              => 0,
            'batch_img_alt'             => 0,
            'seo_title_case'            => 0,
            'seo_external_links_blank'  => 0,
            'seo_toc_compatibility'     => 0,
            'enable_lazy_load_global'   => 0,
            'enable_smart_lazy_load'    => 0,

            // Redirecionamentos 301 (SEO & 404)
            'enable_auto_301'           => 0,
            'custom_301_url'            => '',

            // ═══════════════════════════════════════════
            // ⛔ ABA: CORE CLEAN & EXTRAS
            // ═══════════════════════════════════════════
            'disable_app_passwords'     => 0,
            'clean_rest_api'            => 0,
            'show_admin_ids'            => 0,
            'theme_language'            => 'pt_BR',

    );
}
/**
 * Seta opções padrão na ativação do tema (usa função reutilizável)
 */
function fast_webx_set_default_options() {
    $options = get_option( 'fast_webx_options' );
    if ( false === $options || empty( $options ) ) {
        update_option( 'fast_webx_options', fwx_get_default_options() );
    }
}
add_action( 'after_switch_theme', 'fast_webx_set_default_options' );

/**
 * AJAX: Exportar Configurações (GET)
 */
function fwx_ajax_export_settings() {
    if ( ob_get_length() ) {
        ob_clean();
    }
    if ( ! check_ajax_referer( 'fwx_settings_tool', 'nonce', false ) ) {
        wp_send_json_error( 'Sessão expirada. Recarregue a página.' );
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Sem permissão.' );
    }
    if ( ob_get_length() ) {
        ob_clean();
    }
    $options = get_option( 'fast_webx_options', array() );
    wp_send_json_success( $options );
}
add_action( 'wp_ajax_fwx_export_settings', 'fwx_ajax_export_settings' );

/**
 * AJAX: Importar Configurações (POST)
 */
function fwx_ajax_import_settings() {
    if ( ob_get_length() ) {
        ob_clean();
    }
    if ( ! check_ajax_referer( 'fwx_settings_tool', 'nonce', false ) ) {
        wp_send_json_error( 'Sessão expirada. Recarregue a página.' );
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Sem permissão.' );
    }

    $raw = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : '';
    $imported = json_decode( $raw, true );

    if ( ! is_array( $imported ) || empty( $imported ) ) {
        wp_send_json_error( 'Arquivo JSON inválido ou vazio.' );
    }

    // Whitelist: só aceitar chaves conhecidas do tema para evitar injeção de lixo no DB
    $defaults = fwx_get_default_options();
    $filtered = array();
    foreach ( $defaults as $key => $default_value ) {
        if ( array_key_exists( $key, $imported ) ) {
            $filtered[ $key ] = $imported[ $key ];
        } else {
            $filtered[ $key ] = $default_value;
        }
    }

    // SANITIZAÇÃO MANUAL: Chama o método de sanitização oficial do tema para garantir integridade
    if ( class_exists('Fast_WebX_Admin') ) {
        $admin = new Fast_WebX_Admin();
        $filtered = $admin->sanitize_options( $filtered );
    }

    // Salva e limpa cache de objeto (previne persistência de valores antigos no painel)
    update_option( 'fast_webx_options', $filtered );
    wp_cache_delete( 'fast_webx_options', 'options' );

    if ( ob_get_length() ) {
        ob_clean();
    }
    wp_send_json_success( 'Configurações importadas com sucesso! Sua página será atualizada com os dados exatos do backup.' );
}
add_action( 'wp_ajax_fwx_import_settings', 'fwx_ajax_import_settings' );

/**
 * AJAX: Resetar Configurações para Padrão (POST)
 */
function fwx_ajax_reset_settings() {
    if ( ob_get_length() ) {
        ob_clean();
    }
    if ( ! check_ajax_referer( 'fwx_settings_tool', 'nonce', false ) ) {
        wp_send_json_error( 'Sessão expirada. Recarregue a página antes de resetar.' );
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Sem permissão.' );
    }

    $defaults = fwx_get_default_options();

    // SANITIZAÇÃO MANUAL: Garante integridade absoluta dos valores padrão antes de salvar
    if ( class_exists('Fast_WebX_Admin') ) {
        $admin = new Fast_WebX_Admin();
        $defaults = $admin->sanitize_options( $defaults );
    }

    update_option( 'fast_webx_options', $defaults );
    wp_cache_delete( 'fast_webx_options', 'options' );

    if ( ob_get_length() ) {
        ob_clean();
    }
    wp_send_json_success( 'Todas as configurações foram restauradas ao padrão de fábrica! Recarregando...' );
}
add_action( 'wp_ajax_fwx_reset_settings', 'fwx_ajax_reset_settings' );

/**
 * Renderiza os metadados de post (Autor e Data) nos loops globais.
 * Focado em SEO (E-E-A-T) e Transparência.
 */
function fwx_render_post_meta() {
    ?>
    <div class="fwx-post-meta">
        <span class="fwx-meta-author"><?php echo esc_html( fwx_t( 'single_author_prefix' ) ); ?> <?php the_author(); ?></span>
        <span class="fwx-meta-date"><?php echo get_the_date(); ?></span>
    </div>
    <?php
}
/**
 * 23. Suporte Nativo a SVG (Performance & Nitidez)
 * Permite o upload de arquivos .svg na biblioteca de mídia.
 */
function fwx_allow_svg_uploads( $mimes ) {
    $mimes['svg']  = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
}
add_filter( 'upload_mimes', 'fwx_allow_svg_uploads' );

function fwx_fix_svg_extension_issue( $data, $file, $filename, $mimes ) {
    $ext = isset( $data['ext'] ) ? $data['ext'] : '';
    if ( empty( $ext ) ) {
        $exploded = explode( '.', $filename );
        $ext      = strtolower( end( $exploded ) );
        if ( 'svg' === $ext ) {
            $data['type'] = 'image/svg+xml';
            $data['ext']  = 'svg';
        }
    }
    return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'fwx_fix_svg_extension_issue', 10, 4 );

/**
 * CSS para visualização de SVG na Biblioteca de Mídia
 */
function fwx_svg_admin_styles() {
    echo '<style>
        .attachment-264x264, .thumbnail img[src$=".svg"] { width: 100% !important; height: auto !important; }
    </style>';
}
add_action( 'admin_head', 'fwx_svg_admin_styles' );

/**
 * 24. SEO de Imagens: Auto-Alt
 * Insere o título do post no atributo alt caso ele esteja vazio.
 * Melhora o E-E-A-T e a indexação no Google Imagens.
 */
/**
 * 24. SEO de Imagens: Auto-Alt Físico no Upload
 * Quando você sobe uma imagem, o tema pega o título e já preenche o campo ALT no banco de dados.
 * Assim você vê o campo preenchido no painel e pode editar se quiser.
 */
function fwx_set_image_alt_on_upload( $post_ID ) {
    $options = get_option( 'fast_webx_options' );
    
    // Só age se a opção estiver ligada no painel
    if ( empty( $options['auto_img_alt'] ) ) {
        return;
    }

    // Pega o título que o WP deu para a imagem (geralmente o nome do arquivo)
    $img_title = get_the_title( $post_ID );

    if ( $img_title ) {
        // Limpa o título (remove traços e deixa a primeira letra maiúscula para ficar bonito)
        $img_alt = ucfirst( str_replace( array( '-', '_' ), ' ', $img_title ) );
        
        // Grava FISICAMENTE no banco de dados do WordPress
        update_post_meta( $post_ID, '_wp_attachment_image_alt', $img_alt );
    }
}
add_action( 'add_attachment', 'fwx_set_image_alt_on_upload' );

/**
 * 25. Personalização de Cores por Categoria
 * Permite definir uma cor exclusiva para cada categoria de post via metadados de taxonomia.
 */

// Enfileira os scripts do color picker nativo do WordPress na tela de categorias
function fwx_enqueue_category_color_picker( $hook_suffix ) {
    $screen = get_current_screen();
    if ( ! $screen || 'category' !== $screen->taxonomy ) {
        return;
    }
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );
    
    add_action( 'admin_footer', function() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($){
                // Inicializa nos seletores existentes
                $('.fwx-color-picker').wpColorPicker();
                
                // Re-inicializa após adição rápida via AJAX
                $(document).ajaxComplete(function(event, xhr, settings) {
                    if (settings.data && settings.data.indexOf('action=add-tag') !== -1) {
                        setTimeout(function() {
                            $('.fwx-color-picker').wpColorPicker();
                        }, 100);
                    }
                });
            });
        </script>
        <style>
            /* Pequeno ajuste visual para o color picker no form de criação rápida */
            .form-field .wp-picker-container { display: inline-block; margin-top: 5px; }
        </style>
        <?php
    } );
}
add_action( 'admin_enqueue_scripts', 'fwx_enqueue_category_color_picker' );

// Campo na tela de criação rápida de categorias (Adicionar Nova)
function fwx_category_add_color_field() {
    ?>
    <div class="form-field term-color-wrap">
        <label for="category_color"><?php esc_html_e( 'Cor da Categoria', 'fast-webx' ); ?></label>
        <input type="text" name="category_color" id="category_color" class="fwx-color-picker" value="" data-default-color="" />
        <p class="description"><?php esc_html_e( 'Selecione uma cor personalizada para esta categoria. Ela será usada como fundo nas tags do front-end.', 'fast-webx' ); ?></p>
    </div>
    <?php
}
add_action( 'category_add_form_fields', 'fwx_category_add_color_field', 10 );

// Campo na tela de edição de categoria
function fwx_category_edit_color_field( $term ) {
    $color = get_term_meta( $term->term_id, 'category_color', true );
    ?>
    <tr class="form-field term-color-wrap">
        <th scope="row"><label for="category_color"><?php esc_html_e( 'Cor da Categoria', 'fast-webx' ); ?></label></th>
        <td>
            <input type="text" name="category_color" id="category_color" class="fwx-color-picker" value="<?php echo esc_attr( $color ); ?>" data-default-color="" />
            <p class="description"><?php esc_html_e( 'Selecione uma cor personalizada para esta categoria. Ela será usada como fundo nas tags do front-end.', 'fast-webx' ); ?></p>
        </td>
    </tr>
    <?php
}
add_action( 'category_edit_form_fields', 'fwx_category_edit_color_field', 10 );

// Salva a cor no metadado do termo
function fwx_save_category_color( $term_id ) {
    if ( isset( $_POST['category_color'] ) ) {
        $color = sanitize_hex_color( $_POST['category_color'] );
        if ( ! empty( $color ) ) {
            update_term_meta( $term_id, 'category_color', $color );
        } else {
            delete_term_meta( $term_id, 'category_color' );
        }
    }
}
add_action( 'created_category', 'fwx_save_category_color', 10 );
add_action( 'edited_category', 'fwx_save_category_color', 10 );

/**
 * Pré-carregamento imediato da imagem de destaque (LCP) no Head para Posts Individuais.
 * Reduz drasticamente o LCP eliminando gargalos de descoberta pelo parser.
 */
function fwx_preload_single_lcp_image() {
    if ( ! is_single() ) {
        return;
    }

    $hide_thumb = fwx_get_option( 'hide_single_thumb', 0 );
    if ( $hide_thumb || ! has_post_thumbnail() ) {
        return;
    }

    $post_id = get_the_ID();
    $single_layout = fwx_get_option( 'single_post_layout', 'layout-1' );
    $size = ( 'layout-2' === $single_layout ) ? 'full' : 'fwx-single-thumb';

    $img_id = get_post_thumbnail_id( $post_id );
    $img_src = wp_get_attachment_image_url( $img_id, $size );

    if ( $img_src ) {
        $img_srcset = wp_get_attachment_image_srcset( $img_id, $size );
        $img_sizes  = wp_get_attachment_image_sizes( $img_id, $size );

        echo '<link rel="preload" as="image" href="' . esc_url( $img_src ) . '"';
        if ( $img_srcset ) {
            echo ' imagesrcset="' . esc_attr( $img_srcset ) . '"';
        }
        if ( $img_sizes ) {
            echo ' imagesizes="' . esc_attr( $img_sizes ) . '"';
        }
        echo ' fetchpriority="high" />' . "\n";
    }
}
add_action( 'wp_head', 'fwx_preload_single_lcp_image', 1 );

/**
 * Converte uma URL de imagem do WordPress em ID de anexo de forma extremamente robusta.
 * Funciona mesmo após migrações de domínio, mudanças de protocolo (HTTP/HTTPS) ou URLs relativas.
 *
 * @since   3.1.3
 * @param  string $url A URL da imagem.
 * @return int A ID do anexo ou 0 se não encontrar.
 */
function fwx_get_attachment_id_by_url( $url ) {
    if ( empty( $url ) ) {
        return 0;
    }

    // 1. Tenta a função padrão do WordPress
    $attachment_id = attachment_url_to_postid( $url );
    if ( $attachment_id ) {
        return intval( $attachment_id );
    }

    // 2. Fallback robusto por consulta SQL na wp_postmeta (limpa domínio e protocolo)
    global $wpdb;
    
    // Remove protocolo e domínio da URL buscada
    $clean_url = preg_replace( '/^https?:/i', '', $url );
    $clean_url = preg_replace( '/^\/\/[^\/]+/i', '', $clean_url ); // Remove //dominio.com

    // Remove qualquer tamanho adicionado pelo WordPress no final do arquivo (ex: -150x150.jpg)
    $clean_url = preg_replace( '/-\d+x\d+(?=\.[a-z0-9]+$)/i', '', $clean_url );

    // Pega o diretório relativo a wp-content/uploads/
    $upload_dir = wp_get_upload_dir();
    $base_url = preg_replace( '/^https?:/i', '', $upload_dir['baseurl'] );
    $base_url = preg_replace( '/^\/\/[^\/]+/i', '', $base_url ); // Remove //dominio.com do baseurl

    // Extrai o caminho relativo
    $relative_path = str_replace( $base_url . '/', '', $clean_url );
    
    // Se a URL ainda tiver "/wp-content/uploads/", limpa isso
    $relative_path = preg_replace( '/^.*?\/uploads\//i', '', $relative_path );

    if ( ! empty( $relative_path ) ) {
        $attachment_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value = %s",
            $relative_path
        ) );
    }

    return $attachment_id ? intval( $attachment_id ) : 0;
}

add_action( 'init', function() {
    if ( get_option( 'fwx_loja_needs_flush' ) ) {
        flush_rewrite_rules();
        delete_option( 'fwx_loja_needs_flush' );
    }
}, 99 );

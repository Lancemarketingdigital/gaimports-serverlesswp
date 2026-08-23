<?php
/**
 * Fast WebX � M�dulo Loja (Post Type Cat�logo de Produtos)
 *
 * Implementa o Custom Post Type `fwx_produto` com taxonomia de categorias,
 * meta fields personalizados (pre�o, link, badge) e shortcode [fwx_loja].
 *
 * Vitrine/Cat�logo puro: sem carrinho, sem checkout, sem WooCommerce.
 *
 * @package Fast_WebX
 * @since   3.1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! fwx_get_option( 'loja_enabled', 0 ) ) {
    return;
}

// Enfileira o CSS dedicado da Loja condicionalmente (otimiza��o LCP e PageSpeed)
add_action( 'wp_enqueue_scripts', function() {
    if ( ! fwx_get_option( 'loja_enabled', 0 ) ) {
        return;
    }

    // Se for o template da Home LP ou p�gina inicial, n�o enfileira o CSS da loja de forma alguma
    if ( is_page_template( 'template-home.php' ) || is_front_page() ) {
        return;
    }

    $enqueue = false;

    // 1. Se for singular do produto, arquivo do cat�logo ou taxonomia da loja
    if ( is_singular( 'fwx_produto' ) || is_post_type_archive( 'fwx_produto' ) || is_tax( 'fwx_categoria_produto' ) ) {
        $enqueue = true;
    }

    // 2. Se for uma p�gina de cat�logo da loja de acordo com a fun��o helper
    if ( ! $enqueue && function_exists( 'fwx_is_loja_page' ) && fwx_is_loja_page() ) {
        $enqueue = true;
    }

    // 3. Se contiver o shortcode [fwx_loja] no conte�do do post/p�gina singular
    if ( ! $enqueue && is_singular() ) {
        $post = get_post();
        if ( $post && has_shortcode( $post->post_content, 'fwx_loja' ) ) {
            $enqueue = true;
        }
    }

    if ( $enqueue ) {
        wp_enqueue_style( 'fwx-loja', get_template_directory_uri() . '/css-loja.css', [], FWX_VERSION );
    }
} );

// =============================================================================
// 1. REGISTRO DO CUSTOM POST TYPE
// =============================================================================

/**
 * Registra o CPT `fwx_produto` e a taxonomia `fwx_categoria_produto`.
 *
 * @since   3.1.3
 */
function fwx_register_cpt_produto() {

    // --- Post Type ---
    $labels_cpt = array(
        'name'               => _x( 'Produtos', 'post type general name', 'fast-webx' ),
        'singular_name'      => _x( 'Produto', 'post type singular name', 'fast-webx' ),
        'menu_name'          => __( 'Loja', 'fast-webx' ),
        'name_admin_bar'     => __( 'Produto', 'fast-webx' ),
        'add_new'            => __( 'Adicionar Produto', 'fast-webx' ),
        'add_new_item'       => __( 'Adicionar Novo Produto', 'fast-webx' ),
        'new_item'           => __( 'Novo Produto', 'fast-webx' ),
        'edit_item'          => __( 'Editar Produto', 'fast-webx' ),
        'view_item'          => __( 'Ver Produto', 'fast-webx' ),
        'all_items'          => __( 'Todos os Produtos', 'fast-webx' ),
        'search_items'       => __( 'Buscar Produtos', 'fast-webx' ),
        'not_found'          => __( 'Nenhum produto encontrado.', 'fast-webx' ),
        'not_found_in_trash' => __( 'Nenhum produto na lixeira.', 'fast-webx' ),
    );

    // --- Taxonomia de Categorias ---
    $labels_tax = array(
        'name'              => _x( 'Categorias de Produtos', 'taxonomy general name', 'fast-webx' ),
        'singular_name'     => _x( 'Categoria de Produto', 'taxonomy singular name', 'fast-webx' ),
        'search_items'      => __( 'Buscar Categorias', 'fast-webx' ),
        'all_items'         => __( 'Todas as Categorias', 'fast-webx' ),
        'parent_item'       => __( 'Categoria Pai', 'fast-webx' ),
        'parent_item_colon' => __( 'Categoria Pai:', 'fast-webx' ),
        'edit_item'         => __( 'Editar Categoria', 'fast-webx' ),
        'update_item'       => __( 'Atualizar Categoria', 'fast-webx' ),
        'add_new_item'      => __( 'Adicionar Nova Categoria', 'fast-webx' ),
        'new_item_name'     => __( 'Nome da Nova Categoria', 'fast-webx' ),
        'menu_name'         => __( 'Categorias', 'fast-webx' ),
    );

    $args_tax = array(
        'hierarchical'      => true,
        'labels'            => $labels_tax,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'loja/categoria', 'with_front' => false ),
        'show_in_rest'      => true,
    );

    register_taxonomy( 'fwx_categoria_produto', array( 'fwx_produto' ), $args_tax );

    // --- Post Type ---
    $args_cpt = array(
        'labels'             => $labels_cpt,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'loja', 'with_front' => false ),
        'capability_type'    => 'post',
        'has_archive'        => 'loja',
        'hierarchical'       => false,
        'menu_position'      => 25,
        'menu_icon'          => 'dashicons-store',
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        'taxonomies'         => array( 'fwx_categoria_produto' ),
        'show_in_rest'       => true,
    );

    register_post_type( 'fwx_produto', $args_cpt );
}
add_action( 'init', 'fwx_register_cpt_produto' );

// =============================================================================
// 2. FLUSH DE REWRITE RULES (�nico na ativa��o do tema)
// =============================================================================

/**
 * For�a a regenera��o das regras de permalink ao ativar o tema.
 *
 * @since   3.1.3
 */
function fwx_loja_flush_rewrite_rules() {
    fwx_register_cpt_produto();
    flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'fwx_loja_flush_rewrite_rules' );

// =============================================================================
// 3. META BOX � CAMPOS DO PRODUTO
// =============================================================================

/**
 * Registra a Meta Box com campos personalizados do produto.
 *
 * @since   3.1.3
 */
function fwx_produto_meta_box() {
    add_meta_box(
        'fwx-produto-dados',
        '??? Dados do Produto',
        'fwx_produto_meta_box_render',
        'fwx_produto',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'fwx_produto_meta_box' );

/**
 * Renderiza o HTML da Meta Box.
 *
 * @param WP_Post $post O objeto do post atual.
 * @since   3.1.3
 */
function fwx_produto_meta_box_render( $post ) {
    wp_nonce_field( 'fwx_produto_meta_save', 'fwx_produto_meta_nonce' );

    // Enfileirar biblioteca de m�dia do WordPress
    wp_enqueue_media();

    $preco        = get_post_meta( $post->ID, '_fwx_produto_preco', true );
    $preco_de     = get_post_meta( $post->ID, '_fwx_produto_preco_de', true );
    $link         = get_post_meta( $post->ID, '_fwx_produto_link', true );
    $badge        = get_post_meta( $post->ID, '_fwx_produto_badge', true );
    $link_target  = get_post_meta( $post->ID, '_fwx_produto_link_target', true );

    // Campos da galeria
    $img_1        = get_post_meta( $post->ID, '_fwx_produto_img_1', true );
    $img_2        = get_post_meta( $post->ID, '_fwx_produto_img_2', true );
    $img_3        = get_post_meta( $post->ID, '_fwx_produto_img_3', true );
    ?>
    <style>
        .fwx-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        .fwx-meta-field { display: flex; flex-direction: column; gap: 4px; }
        .fwx-meta-field label { font-weight: 600; font-size: 13px; color: #1d2327; }
        .fwx-meta-field input[type="text"],
        .fwx-meta-field input[type="url"] { width: 100%; }
        .fwx-meta-field-full { margin-bottom: 16px; }
        .fwx-meta-field-full label { font-weight: 600; font-size: 13px; color: #1d2327; display: block; margin-bottom: 4px; }
        .fwx-meta-hint { font-size: 11px; color: #787c82; margin-top: 3px; }
    </style>

    <div class="fwx-meta-grid">
        <div class="fwx-meta-field">
            <label for="fwx_produto_preco">?? Pre�o (exibido)</label>
            <input type="text" id="fwx_produto_preco" name="fwx_produto_preco"
                   value="<?php echo esc_attr( $preco ); ?>"
                   placeholder="Ex: R$ 97,00 ou Gratuito" />
            <span class="fwx-meta-hint">Texto livre � exibido no card.</span>
        </div>
        <div class="fwx-meta-field">
            <label for="fwx_produto_preco_de">?? Pre�o original (riscado)</label>
            <input type="text" id="fwx_produto_preco_de" name="fwx_produto_preco_de"
                   value="<?php echo esc_attr( $preco_de ); ?>"
                   placeholder="Ex: R$ 197,00" />
            <span class="fwx-meta-hint">Deixe vazio para ocultar.</span>
        </div>
    </div>

    <div class="fwx-meta-grid">
        <div class="fwx-meta-field">
            <label for="fwx_produto_badge">?? Badge / Label</label>
            <input type="text" id="fwx_produto_badge" name="fwx_produto_badge"
                   value="<?php echo esc_attr( $badge ); ?>"
                   placeholder="Ex: Novo, Oferta, Mais Vendido" />
            <span class="fwx-meta-hint">Aparece como etiqueta no canto do card.</span>
        </div>
        <div class="fwx-meta-field" style="justify-content:flex-end; padding-bottom:6px;">
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                <input type="checkbox" name="fwx_produto_link_target" value="1"
                       <?php checked( $link_target, '1' ); ?> />
                Abrir link em nova aba
            </label>
        </div>
    </div>

    <div class="fwx-meta-field-full">
        <label for="fwx_produto_link">?? URL de destino do produto</label>
        <input type="url" id="fwx_produto_link" name="fwx_produto_link"
               value="<?php echo esc_url( $link ); ?>"
               placeholder="https://..." style="width:100%;" />
        <span class="fwx-meta-hint">Link para compra, landing page ou p�gina externa. Deixe vazio para redirecionar ao single.</span>
    </div>

    <div style="margin-top: 24px; border-top: 1px solid #ccd0d4; padding-top: 16px;">
        <h4 style="margin: 0 0 8px; font-size: 14px; color: #1d2327;">?? Galeria de Imagens Adicionais (1 + 3)</h4>
        <p class="description" style="margin-bottom: 16px;">Adicione at� 3 imagens adicionais que ser�o exibidas em uma galeria no single do produto (abaixo da imagem de destaque).</p>
        
        <div class="fwx-meta-grid" style="grid-template-columns: repeat(3, 1fr);">
            <div class="fwx-meta-field">
                <label for="fwx_produto_img_1">Imagem Adicional 1</label>
                <div style="display:flex; gap: 6px;">
                    <input type="text" id="fwx_produto_img_1" name="fwx_produto_img_1" value="<?php echo esc_url($img_1); ?>" placeholder="https://..." style="flex:1;" />
                    <button type="button" class="button fwx-upload-gal-btn" data-target="fwx_produto_img_1">Selecionar</button>
                </div>
            </div>
            <div class="fwx-meta-field">
                <label for="fwx_produto_img_2">Imagem Adicional 2</label>
                <div style="display:flex; gap: 6px;">
                    <input type="text" id="fwx_produto_img_2" name="fwx_produto_img_2" value="<?php echo esc_url($img_2); ?>" placeholder="https://..." style="flex:1;" />
                    <button type="button" class="button fwx-upload-gal-btn" data-target="fwx_produto_img_2">Selecionar</button>
                </div>
            </div>
            <div class="fwx-meta-field">
                <label for="fwx_produto_img_3">Imagem Adicional 3</label>
                <div style="display:flex; gap: 6px;">
                    <input type="text" id="fwx_produto_img_3" name="fwx_produto_img_3" value="<?php echo esc_url($img_3); ?>" placeholder="https://..." style="flex:1;" />
                    <button type="button" class="button fwx-upload-gal-btn" data-target="fwx_produto_img_3">Selecionar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        jQuery(document).ready(function($){
            $('.fwx-upload-gal-btn').click(function(e) {
                e.preventDefault();
                var targetId = $(this).attr('data-target');
                var inputField = $('#' + targetId);
                var frame = wp.media({
                    title: 'Selecionar Imagem da Galeria',
                    multiple: false,
                    library: { type: 'image' },
                    button: { text: 'Usar esta imagem' }
                });
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    inputField.val(attachment.url);
                });
                frame.open();
            });
        });
    </script>
    <?php
}

/**
 * Salva os meta fields do produto com sanitiza��o segura.
 *
 * @param int $post_id ID do post sendo salvo.
 * @since   3.1.3
 */
function fwx_produto_save_meta( $post_id ) {
    // Verifica��es de seguran�a
    if ( ! isset( $_POST['fwx_produto_meta_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['fwx_produto_meta_nonce'], 'fwx_produto_meta_save' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Salvar campos
    if ( isset( $_POST['fwx_produto_preco'] ) ) {
        update_post_meta( $post_id, '_fwx_produto_preco', sanitize_text_field( $_POST['fwx_produto_preco'] ) );
    }
    if ( isset( $_POST['fwx_produto_preco_de'] ) ) {
        update_post_meta( $post_id, '_fwx_produto_preco_de', sanitize_text_field( $_POST['fwx_produto_preco_de'] ) );
    }
    if ( isset( $_POST['fwx_produto_badge'] ) ) {
        update_post_meta( $post_id, '_fwx_produto_badge', sanitize_text_field( $_POST['fwx_produto_badge'] ) );
    }
    if ( isset( $_POST['fwx_produto_link'] ) ) {
        update_post_meta( $post_id, '_fwx_produto_link', sanitize_url( $_POST['fwx_produto_link'] ) );
    }
    // Checkbox: salva 1 se marcado, remove se desmarcado
    $link_target = isset( $_POST['fwx_produto_link_target'] ) ? '1' : '0';
    update_post_meta( $post_id, '_fwx_produto_link_target', $link_target );

    // Salvar imagens da galeria
    if ( isset( $_POST['fwx_produto_img_1'] ) ) {
        update_post_meta( $post_id, '_fwx_produto_img_1', sanitize_url( $_POST['fwx_produto_img_1'] ) );
    }
    if ( isset( $_POST['fwx_produto_img_2'] ) ) {
        update_post_meta( $post_id, '_fwx_produto_img_2', sanitize_url( $_POST['fwx_produto_img_2'] ) );
    }
    if ( isset( $_POST['fwx_produto_img_3'] ) ) {
        update_post_meta( $post_id, '_fwx_produto_img_3', sanitize_url( $_POST['fwx_produto_img_3'] ) );
    }
}
add_action( 'save_post_fwx_produto', 'fwx_produto_save_meta' );

// =============================================================================
// 4. HELPER: RENDERIZA��O DE UM CARD DE PRODUTO
// =============================================================================

/**
 * Renderiza o HTML de um �nico card de produto.
 * Centralizado aqui para reutiliza��o no archive e no shortcode.
 *
 * @param int  $post_id  ID do produto.
 * @param bool $lazy     Se true, usa loading="lazy" na imagem.
 * @since   3.1.3
 */
function fwx_render_produto_card( $post_id, $lazy = true ) {
    $preco        = get_post_meta( $post_id, '_fwx_produto_preco', true );
    $preco_de     = get_post_meta( $post_id, '_fwx_produto_preco_de', true );
    $link         = get_post_meta( $post_id, '_fwx_produto_link', true );
    $badge        = get_post_meta( $post_id, '_fwx_produto_badge', true );
    $link_target  = get_post_meta( $post_id, '_fwx_produto_link_target', true );

    $show_price   = fwx_get_option( 'loja_show_price', 0 );
    $cta_text     = fwx_get_option( 'loja_cta_text', 'Ver Produto' );
    $title        = get_the_title( $post_id );
        // URL do card (imagem e t�tulo) sempre vai para a p�gina de detalhes (single) do pr�prio site
    $card_url    = get_permalink( $post_id );
    
    // URL do bot�o CTA (se houver link externo, vai para ele, sen�o vai para a p�gina de detalhes)
    $cta_url     = ! empty( $link ) ? $link : get_permalink( $post_id );
    $cta_target  = ( ! empty( $link ) && $link_target === '1' ) ? ' target="_blank" rel="noopener noreferrer"' : '';
    $loading_attr = $lazy ? 'lazy' : 'eager';

    // Categorias do produto
    $categorias = get_the_terms( $post_id, 'fwx_categoria_produto' );

    echo '<article class="fwx-produto-card" id="produto-' . esc_attr( $post_id ) . '">';

    // --- Thumbnail ---
    echo '<div class="fwx-produto-thumb">';
    echo '<a href="' . esc_url( $card_url ) . '" aria-label="' . esc_attr( $title ) . '">';
    if ( has_post_thumbnail( $post_id ) ) {
        echo get_the_post_thumbnail( $post_id, 'fwx-loja-square', array(
            'loading' => $loading_attr,
            'decoding' => 'async',
            'class' => 'fwx-produto-img',
            'alt' => esc_attr( $title ),
        ) );
    } else {
        echo '<div class="fwx-produto-img fwx-produto-no-thumb" aria-hidden="true">';
        echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>';
        echo '</div>';
    }
    echo '</a>';

    // Badge
    if ( ! empty( $badge ) ) {
        echo '<span class="fwx-produto-badge">' . esc_html( $badge ) . '</span>';
    }
    echo '</div>'; // .fwx-produto-thumb

    // --- Conte�do ---
    echo '<div class="fwx-produto-content">';

    // Categoria
    if ( ! empty( $categorias ) && ! is_wp_error( $categorias ) ) {
        $cat = $categorias[0];
        echo '<a href="' . esc_url( get_term_link( $cat ) ) . '" class="fwx-produto-cat">' . esc_html( $cat->name ) . '</a>';
    }

    // T�tulo
    echo '<h2 class="fwx-produto-title"><a href="' . esc_url( $card_url ) . '">' . esc_html( $title ) . '</a></h2>';

    // Excerpt (descri��o curta)
    if ( ! empty( $excerpt ) ) {
        echo '<p class="fwx-produto-desc">' . esc_html( wp_trim_words( $excerpt, 18, '...' ) ) . '</p>';
    }

    // Pre�o
    if ( $show_price && ! empty( $preco ) ) {
        echo '<div class="fwx-produto-preco-wrap">';
        if ( ! empty( $preco_de ) ) {
            echo '<span class="fwx-produto-preco-de">' . esc_html( $preco_de ) . '</span>';
        }
        echo '<span class="fwx-produto-preco">' . esc_html( $preco ) . '</span>';
        echo '</div>';
    }

    // CTA
    echo '<a href="' . esc_url( $cta_url ) . '"' . $cta_target . ' class="fwx-produto-cta">' . esc_html( $cta_text ) . '</a>';

    echo '</div>'; // .fwx-produto-content
    echo '</article>';
}

// =============================================================================
// 5. SHORTCODE [fwx_loja]
// =============================================================================

/**
 * Shortcode para exibir o cat�logo de produtos em qualquer conte�do.
 *
 * Atributos:
 *  - columns    (int)    N�mero de colunas. Padr�o: configura��o do painel.
 *  - per_page   (int)    Produtos por p�gina. Padr�o: 12.
 *  - categoria  (string) Slug de categoria para filtrar. Padr�o: '' (todos).
 *  - orderby    (string) Crit�rio de ordena��o. Padr�o: 'date'.
 *  - order      (string) ASC ou DESC. Padr�o: DESC.
 *
 * @param array $atts Atributos do shortcode.
 * @return string HTML do cat�logo.
 * @since   3.1.3
 */
function fwx_loja_shortcode( $atts ) {
    $defaults = array(
        'columns'   => fwx_get_option( 'loja_columns', '3' ),
        'per_page'  => 12,
        'categoria' => '',
        'orderby'   => 'date',
        'order'     => 'DESC',
    );
    $atts = shortcode_atts( $defaults, $atts, 'fwx_loja' );

    $query_args = array(
        'post_type'      => 'fwx_produto',
        'posts_per_page' => absint( $atts['per_page'] ),
        'post_status'    => 'publish',
        'orderby'        => sanitize_key( $atts['orderby'] ),
        'order'          => ( strtoupper( $atts['order'] ) === 'ASC' ) ? 'ASC' : 'DESC',
    );

    if ( ! empty( $atts['categoria'] ) ) {
        $query_args['tax_query'] = array(
            array(
                'taxonomy' => 'fwx_categoria_produto',
                'field'    => 'slug',
                'terms'    => sanitize_text_field( $atts['categoria'] ),
            ),
        );
    }

    $produtos = new WP_Query( $query_args );

    if ( ! $produtos->have_posts() ) {
        return '<p class="fwx-loja-empty">Nenhum produto encontrado.</p>';
    }

    $cols = absint( $atts['columns'] );
    if ( $cols < 1 || $cols > 6 ) {
        $cols = 3;
    }

    ob_start();
    echo '<div class="fwx-loja-grid fwx-loja-cols-' . $cols . '">';

    $i = 0;
    while ( $produtos->have_posts() ) {
        $produtos->the_post();
        fwx_render_produto_card( get_the_ID(), $i > 2 );
        $i++;
    }

    echo '</div>';
    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode( 'fwx_loja', 'fwx_loja_shortcode' );

// =============================================================================
// 6. QUERY E TEMPLATES DE CATEGORIAS (Evita erro 404 e unifica visual)
// =============================================================================

/**
 * Inclui os produtos no loop principal da p�gina da taxonomia de categorias.
 * Resolve o erro 404 que ocorre porque por padr�o a query principal busca posts comuns.
 *
 * @param WP_Query $query Objeto da query.
 * @since   3.1.3
 */
function fwx_loja_taxonomy_query( $query ) {
    if ( ! is_admin() && $query->is_main_query() && is_tax( 'fwx_categoria_produto' ) ) {
        $query->set( 'post_type', array( 'fwx_produto' ) );
    }
}
add_action( 'pre_get_posts', 'fwx_loja_taxonomy_query' );

/**
 * Redireciona o template da taxonomia da loja para usar o mesmo template de cat�logo.
 *
 * @param string $template Caminho completo do template atual do WordPress.
 * @return string Caminho completo do novo template.
 * @since   3.1.3
 */
function fwx_loja_taxonomy_template( $template ) {
    if ( is_tax( 'fwx_categoria_produto' ) ) {
        $new_template = locate_template( array( 'archive-fwx_produto.php' ) );
        if ( ! empty( $new_template ) ) {
            return $new_template;
        }
    }
    return $template;
}
add_filter( 'template_include', 'fwx_loja_taxonomy_template' );

// =============================================================================
// 7. PRODUTOS RELACIONADOS DA LOJA
// =============================================================================

/**
 * Renderiza produtos relacionados (mesma categoria, excluindo o atual).
 * Segue exatamente a mesma est�tica modular do blog.
 *
 * @since   3.1.3
 */
function fwx_related_produtos() {
    global $post;
    if ( ! fwx_get_option( 'loja_enable_related', 0 ) ) {
        return;
    }
    if ( ! is_singular( 'fwx_produto' ) ) {
        return;
    }

    $post_id = get_the_ID();
    $transient_key = 'fwx_related_produtos_' . $post_id;
    $cached_html = get_transient( $transient_key );
    if ( false !== $cached_html && fwx_get_option( 'perf_widget_transients', 0 ) ) {
        echo $cached_html;
        return;
    }

    $terms = get_the_terms( $post_id, 'fwx_categoria_produto' );
    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return;
    }

    $term_ids = wp_list_pluck( $terms, 'term_id' );

    $args = array(
        'post_type'      => 'fwx_produto',
        'tax_query'      => array(
            array(
                'taxonomy' => 'fwx_categoria_produto',
                'field'    => 'term_id',
                'terms'    => $term_ids,
            ),
        ),
        'post__not_in'   => array( $post_id ),
        'posts_per_page' => 3,
        'orderby'        => 'rand',
    );
    $related = get_posts( $args );

    if ( empty( $related ) ) {
        return;
    }

    ob_start();

    echo '<section class="fwx-related-produtos">';
    echo '<h2 class="fwx-related-title">' . esc_html( fwx_t( 'loja_related_title' ) ) . '</h2>';
    echo '<div class="fwx-related-grid">';

    foreach ( $related as $product ) {
        fwx_render_produto_card( $product->ID );
    }

    echo '</div></section>';
    $html = ob_get_clean();
    if ( fwx_get_option( 'perf_widget_transients', 0 ) ) {
        set_transient( $transient_key, $html, 12 * HOUR_IN_SECONDS );
    }
    echo $html;
}

// O suporte a breadcrumbs para a loja foi integrado de forma nativa e completa (incluindo JSON-LD) em fwx_breadcrumbs() no functions.php.

/**
 * Verifica se uma dada URL � de um v�deo (local MP4/WebM ou externo YouTube/Vimeo).
 *
 * @param string $url URL do arquivo/p�gina.
 * @return bool True se for v�deo, false caso contr�rio.
 * @since   3.1.3
 */
function fwx_is_video_url( $url ) {
    if ( empty( $url ) ) {
        return false;
    }
    if ( preg_match( '/\.(mp4|webm|ogg|mov|3gp)(\?.*)?$/i', $url ) ) {
        return true;
    }
    if ( strpos( $url, 'youtube.com' ) !== false || strpos( $url, 'youtu.be' ) !== false || strpos( $url, 'vimeo.com' ) !== false ) {
        return true;
    }
    return false;
}

/**
 * Widget Autom�tico de Produtos Recentes na Sidebar
 * Exibe at� 5 produtos cadastrados recentemente de forma minimalista.
 *
 * @since   3.1.3
 */
function fwx_sidebar_recent_produtos() {
    if ( ! fwx_get_option( 'loja_enable_sidebar_recent', 0 ) ) {
        return;
    }

    $show = false;
    if ( is_singular( 'post' ) && fwx_get_option( 'recent_products_widget_in_single_post', 0 ) ) {
        $show = true;
    } elseif ( is_singular( 'fwx_produto' ) && fwx_get_option( 'recent_products_widget_in_single_product', 0 ) ) {
        $show = true;
    } elseif ( ( is_home() || is_archive() || is_search() ) && ! fwx_is_loja_page() && fwx_get_option( 'recent_products_widget_in_archive_post', 0 ) ) {
        $show = true;
    } elseif ( fwx_is_loja_page() && fwx_get_option( 'recent_products_widget_in_archive_product', 0 ) ) {
        $show = true;
    } elseif ( is_page() && ! fwx_is_loja_page() && fwx_get_option( 'recent_products_widget_in_single_post', 0 ) ) {
        $show = true;
    }

    if ( ! $show ) {
        return;
    }

    $post_id = is_singular( 'fwx_produto' ) ? get_the_ID() : 0;
    $transient_key = 'fwx_sidebar_produtos_recentes';
    if ( $post_id ) {
        $transient_key = 'fwx_sidebar_produtos_recentes_' . $post_id;
    }

    $cached_html = get_transient( $transient_key );
    if ( false !== $cached_html && fwx_get_option( 'perf_widget_transients', 0 ) ) {
        echo $cached_html;
        return;
    }

    $args = array(
        'post_type'      => 'fwx_produto',
        'posts_per_page' => 5,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    if ( $post_id ) {
        $args['post__not_in'] = array( $post_id );
    }

    $produtos = get_posts( $args );

    if ( empty( $produtos ) ) {
        return;
    }

    ob_start();

    echo '<section class="widget fwx-sidebar-recent-produtos">';
    echo '<h2 class="widget-title">' . esc_html( fwx_t( 'widget_recent_products' ) ) . '</h2>';

    foreach ( $produtos as $prod ) {
        $preco      = get_post_meta( $prod->ID, '_fwx_produto_preco', true );
        $show_price = fwx_get_option( 'loja_show_price', 0 );
        $categorias = get_the_terms( $prod->ID, 'fwx_categoria_produto' );

        echo '<article class="fwx-sidebar-rel-item">';
        echo '<a href="' . esc_url( get_permalink( $prod->ID ) ) . '" class="fwx-sidebar-rel-thumb" tabindex="-1" aria-hidden="true" style="aspect-ratio: 1/1;">';
        if ( has_post_thumbnail( $prod->ID ) ) {
            echo get_the_post_thumbnail( $prod->ID, 'fwx-loja-square-small', array( 'loading' => 'lazy', 'decoding' => 'async' ) );
        } else {
            echo '<div class="fwx-produto-no-thumb" style="aspect-ratio:1/1; display:flex; align-items:center; justify-content:center; background:var(--fwx-bg-color-light); color:rgba(0,0,0,0.2); border:var(--fwx-border); border-radius:var(--fwx-rad-4); width:100%; height:100%;">';
            echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="20" height="20"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>';
            echo '</div>';
        }
        echo '</a>';
        echo '<div class="fwx-sidebar-rel-info">';
        
        // Categoria
        if ( ! empty( $categorias ) && ! is_wp_error( $categorias ) ) {
            $cat = $categorias[0];
            echo '<a href="' . esc_url( get_term_link( $cat ) ) . '" class="fwx-produto-cat" style="font-size: 10px; display: block; margin-bottom: 2px;">' . esc_html( $cat->name ) . '</a>';
        }

        echo '<h3 class="fwx-sidebar-rel-title" style="margin: 0; font-size: 0.9rem; line-height: 1.3;"><a href="' . esc_url( get_permalink( $prod->ID ) ) . '">' . esc_html( get_the_title( $prod->ID ) ) . '</a></h3>';
        
        // Pre�o
        if ( $show_price && ! empty( $preco ) ) {
            echo '<div class="fwx-produto-preco" style="font-size: 0.85rem; margin-top: 3px; font-weight: 700;">' . esc_html( $preco ) . '</div>';
        }
        
        echo '</div>';
        echo '</article>';
    }

    echo '</section>';

    $html = ob_get_clean();
    if ( fwx_get_option( 'perf_widget_transients', 0 ) ) {
        set_transient( $transient_key, $html, 6 * HOUR_IN_SECONDS );
    }
    echo $html;
}

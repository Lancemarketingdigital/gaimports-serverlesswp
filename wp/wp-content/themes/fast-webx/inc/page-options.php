<?php
/**
 * Fast WebX - Opções de Página (Meta Boxes)
 * 
 * Permite configurações individuais por página, como ocultar título e sidebar.
 * 
 * @package Fast_WebX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registra o Meta Box de Opções de Página
 */
function fwx_register_page_options_meta_box() {
    add_meta_box(
        'fwx_page_options',
        'Configs. (Fast WebX)',
        'fwx_display_page_options_meta_box',
        'page',
        'side',
        'default'
    );

    // Meta Box para Scripts & Estilos Customizados
    $screens = [ 'post', 'page' ];
    foreach ( $screens as $screen ) {
        add_meta_box(
            'fwx_page_scripts_styles',
            'Fast WebX - Scripts & Estilos Customizados',
            'fwx_display_scripts_styles_meta_box',
            $screen,
            'normal',
            'default'
        );
    }
}
add_action( 'add_meta_boxes', 'fwx_register_page_options_meta_box' );

/**
 * Exibe o conteúdo do Meta Box
 */
function fwx_display_page_options_meta_box( $post ) {
    // Nonce para segurança
    wp_nonce_field( 'fwx_save_page_options', 'fwx_page_options_nonce' );

    // Obtém valores atuais
    $hide_title  = get_post_meta( $post->ID, '_fwx_hide_title', true );
    $hide_header = get_post_meta( $post->ID, '_fwx_hide_header', true );
    $hide_footer = get_post_meta( $post->ID, '_fwx_hide_footer', true );
    $enable_toc  = get_post_meta( $post->ID, '_fwx_enable_page_toc', true );
    ?>
    <p>
        <label>
            <input type="checkbox" name="fwx_hide_title" value="1" <?php checked( $hide_title, '1' ); ?>>
            Ocultar Título (h1)
        </label>
    </p>
    <p>
        <label>
            <input type="checkbox" name="fwx_hide_header" value="1" <?php checked( $hide_header, '1' ); ?>>
            Ocultar Cabeçalho (Header)
        </label>
    </p>
    <p>
        <label>
            <input type="checkbox" name="fwx_hide_footer" value="1" <?php checked( $hide_footer, '1' ); ?>>
            Ocultar Rodapé (Footer)
        </label>
    </p>
    <p>
        <label>
            <input type="checkbox" name="fwx_enable_page_toc" value="1" <?php checked( $enable_toc, '1' ); ?>>
            Exibir Índice de Conteúdo (TOC)
        </label>
    </p>
    <p class="description">Útil para Landing Pages e modelos personalizados.</p>
    <?php
}

/**
 * Salva os dados do Meta Box
 */
function fwx_save_page_options_meta_box( $post_id ) {
    // Verifica nonce de Page Options
    if ( isset( $_POST['fwx_page_options_nonce'] ) && wp_verify_nonce( $_POST['fwx_page_options_nonce'], 'fwx_save_page_options' ) ) {
        if ( current_user_can( 'edit_page', $post_id ) ) {
            $hide_title  = isset( $_POST['fwx_hide_title'] ) ? '1' : '0';
            $hide_header = isset( $_POST['fwx_hide_header'] ) ? '1' : '0';
            $hide_footer = isset( $_POST['fwx_hide_footer'] ) ? '1' : '0';
            $enable_toc  = isset( $_POST['fwx_enable_page_toc'] ) ? '1' : '0';

            update_post_meta( $post_id, '_fwx_hide_title', $hide_title );
            update_post_meta( $post_id, '_fwx_hide_header', $hide_header );
            update_post_meta( $post_id, '_fwx_hide_footer', $hide_footer );
            update_post_meta( $post_id, '_fwx_enable_page_toc', $enable_toc );
        }
    }

    // Verifica nonce de Scripts e Estilos
    if ( isset( $_POST['fwx_scripts_styles_nonce'] ) && wp_verify_nonce( $_POST['fwx_scripts_styles_nonce'], 'fwx_save_scripts_styles' ) ) {
        if ( current_user_can( 'edit_post', $post_id ) ) {
            // Salva o toggle de Modo Furtivo por página
            $disable_stealth = isset( $_POST['fwx_disable_stealth'] ) ? '1' : '0';
            update_post_meta( $post_id, '_fwx_disable_stealth', $disable_stealth );

            // Salva o toggle de Scripts Globais por página
            $disable_global = isset( $_POST['fwx_disable_global_scripts'] ) ? '1' : '0';
            update_post_meta( $post_id, '_fwx_disable_global_scripts', $disable_global );

            // Salva os campos de scripts/styles sem filtrar para não perder tags HTML se o usuário tiver permissão.
            if ( current_user_can( 'unfiltered_html' ) ) {
                if ( isset( $_POST['fwx_custom_css'] ) ) {
                    update_post_meta( $post_id, '_fwx_custom_css', $_POST['fwx_custom_css'] );
                }
                if ( isset( $_POST['fwx_custom_js_head'] ) ) {
                    update_post_meta( $post_id, '_fwx_custom_js_head', $_POST['fwx_custom_js_head'] );
                }
                if ( isset( $_POST['fwx_custom_js_body'] ) ) {
                    update_post_meta( $post_id, '_fwx_custom_js_body', $_POST['fwx_custom_js_body'] );
                }
            } else {
                // Se o usuário não puder salvar HTML sem filtro, sanitizamos o mínimo possível,
                // mas as tags <script> geralmente serão removidas pelo kses.
                if ( isset( $_POST['fwx_custom_css'] ) ) {
                    update_post_meta( $post_id, '_fwx_custom_css', wp_kses_post( wp_unslash( $_POST['fwx_custom_css'] ) ) );
                }
                if ( isset( $_POST['fwx_custom_js_head'] ) ) {
                    update_post_meta( $post_id, '_fwx_custom_js_head', wp_kses_post( wp_unslash( $_POST['fwx_custom_js_head'] ) ) );
                }
                if ( isset( $_POST['fwx_custom_js_body'] ) ) {
                    update_post_meta( $post_id, '_fwx_custom_js_body', wp_kses_post( wp_unslash( $_POST['fwx_custom_js_body'] ) ) );
                }
            }
        }
    }
}
add_action( 'save_post', 'fwx_save_page_options_meta_box' );

/**
 * Exibe o conteúdo do Meta Box de Scripts e Estilos
 */
function fwx_display_scripts_styles_meta_box( $post ) {
    wp_nonce_field( 'fwx_save_scripts_styles', 'fwx_scripts_styles_nonce' );

    $custom_css      = get_post_meta( $post->ID, '_fwx_custom_css', true );
    $custom_js_head  = get_post_meta( $post->ID, '_fwx_custom_js_head', true );
    $custom_js_body  = get_post_meta( $post->ID, '_fwx_custom_js_body', true );
    $disable_stealth = get_post_meta( $post->ID, '_fwx_disable_stealth', true );
    $disable_global  = get_post_meta( $post->ID, '_fwx_disable_global_scripts', true );
    $stealth_global  = fwx_get_option( 'stealth_mode', 0 );
    ?>
    <style>
        .fwx-code-textarea { width: 100%; font-family: monospace; font-size: 13px; padding: 10px; background: #f9f9f9; border: 1px solid #ccc; }
        .fwx-meta-section { margin-bottom: 20px; }
        .fwx-meta-section h4 { margin: 0 0 5px 0; font-size: 14px; }
        .fwx-stealth-toggle, .fwx-global-toggle { padding: 10px 14px; border-radius: 4px; margin-bottom: 15px; }
        .fwx-stealth-toggle { background: #fff3cd; border: 1px solid #ffc107; }
        .fwx-stealth-toggle.fwx-stealth-off { background: #f0f0f0; border-color: #ccc; }
        .fwx-global-toggle { background: #f8d7da; border: 1px solid #f5c6cb; }
        .fwx-global-toggle.fwx-global-off { background: #f0f0f0; border-color: #ccc; }
        .fwx-stealth-toggle label, .fwx-global-toggle label { font-weight: 600; cursor: pointer; }
        .fwx-stealth-toggle .description, .fwx-global-toggle .description { margin-top: 4px; }
    </style>

    <?php if ( $stealth_global ) : ?>
    <div class="fwx-stealth-toggle <?php echo $disable_stealth === '1' ? 'fwx-stealth-off' : ''; ?>">
        <label>
            <input type="checkbox" name="fwx_disable_stealth" value="1" <?php checked( $disable_stealth, '1' ); ?>>
            ⚡ Desativar Modo Furtivo nesta página
        </label>
        <p class="description">Marque para que os scripts desta página executem imediatamente, sem esperar interação do usuário. <strong>Recomendado para Landing Pages.</strong></p>
    </div>
    <?php else : ?>
    <div class="fwx-stealth-toggle fwx-stealth-off">
        <p class="description" style="margin:0;">ℹ️ O Modo Furtivo está <strong>desativado globalmente</strong> no painel do tema. Os scripts desta página executarão normalmente.</p>
        <input type="hidden" name="fwx_disable_stealth" value="<?php echo esc_attr( $disable_stealth ); ?>">
    </div>
    <?php endif; ?>

    <div class="fwx-global-toggle <?php echo $disable_global === '1' ? '' : 'fwx-global-off'; ?>">
        <label>
            <input type="checkbox" name="fwx_disable_global_scripts" value="1" <?php checked( $disable_global, '1' ); ?>>
            🚫 Desativar Scripts Globais nesta página
        </label>
        <p class="description">Impede que os scripts globais do tema (AdSense, Pixel, Google Tag Manager, etc.) sejam carregados nesta página. Ideal para Landing Pages dedicadas onde você quer controle total sobre os scripts.</p>
    </div>

    <div class="fwx-meta-section">
        <h4>CSS Customizado (Injetado no &lt;head&gt;)</h4>
        <p class="description">Ideal para estilos específicos desta página ou ajustes de Landing Pages. Ex: <code>&lt;style&gt; body { background: #000; } &lt;/style&gt;</code></p>
        <textarea name="fwx_custom_css" rows="6" class="fwx-code-textarea"><?php echo esc_textarea( $custom_css ); ?></textarea>
    </div>
    
    <div class="fwx-meta-section">
        <h4>JS Customizado (Injetado no &lt;head&gt;)</h4>
        <p class="description">Ideal para preloads, anti-FOUC ou scripts que precisam carregar cedo. <strong>Nunca é atrasado pelo Modo Furtivo.</strong></p>
        <textarea name="fwx_custom_js_head" rows="6" class="fwx-code-textarea"><?php echo esc_textarea( $custom_js_head ); ?></textarea>
    </div>

    <div class="fwx-meta-section">
        <h4>JS Customizado (Injetado antes de fechar &lt;/body&gt;)</h4>
        <p class="description">Ideal para scripts secundários. Será afetado pelo Modo Furtivo, a menos que desativado acima.</p>
        <textarea name="fwx_custom_js_body" rows="6" class="fwx-code-textarea"><?php echo esc_textarea( $custom_js_body ); ?></textarea>
    </div>
    <?php
}

/**
 * Verifica se o Modo Furtivo deve ser aplicado nos scripts DESTA página.
 * Retorna false (não aplicar stealth) se:
 *   1. Stealth Mode global está desligado
 *   2. O template da página é Blank (auto-bypass para LPs)
 *   3. O checkbox "Desativar Modo Furtivo nesta página" está marcado
 */
function fwx_should_apply_stealth_for_page( $post_id ) {
    // 1. Stealth global desligado → sem stealth
    $stealth_global = fwx_get_option( 'stealth_mode', 0 );
    if ( ! $stealth_global ) {
        return false;
    }

    // 2. Modelo Blank → auto-bypass (LPs precisam de controle total)
    $template = get_page_template_slug( $post_id );
    if ( 'template-blank.php' === $template ) {
        return false;
    }

    // 3. Toggle por página → bypass manual
    $disable_stealth = get_post_meta( $post_id, '_fwx_disable_stealth', true );
    if ( '1' === $disable_stealth ) {
        return false;
    }

    return true;
}

/**
 * Injeção de Scripts e Estilos no Front-end (Head)
 */
function fwx_output_custom_page_scripts_head() {
    if ( ! is_singular() ) {
        return;
    }
    
    $post_id        = get_queried_object_id();
    $custom_css     = get_post_meta( $post_id, '_fwx_custom_css', true );
    $custom_js_head = get_post_meta( $post_id, '_fwx_custom_js_head', true );

    if ( ! empty( $custom_css ) ) {
        echo "<!-- FWX Custom CSS -->\n" . $custom_css . "\n";
    }

    if ( ! empty( $custom_js_head ) ) {
        echo "<!-- FWX Custom JS Head -->\n";
        // JS Head NUNCA é atrasado: contém anti-FOUC e preloads críticos para o render.
        echo $custom_js_head . "\n";
    }
}
add_action( 'wp_head', 'fwx_output_custom_page_scripts_head', 100 );

/**
 * Injeção de Scripts no Front-end (Footer)
 */
function fwx_output_custom_page_scripts_footer() {
    if ( ! is_singular() ) {
        return;
    }
    
    $post_id        = get_queried_object_id();
    $custom_js_body = get_post_meta( $post_id, '_fwx_custom_js_body', true );

    if ( ! empty( $custom_js_body ) ) {
        echo "<!-- FWX Custom JS Footer -->\n";

        if ( fwx_should_apply_stealth_for_page( $post_id ) ) {
            // Stealth Mode ativo para esta página: transforma scripts em inertes
            $custom_js_body = str_replace( 'type="text/javascript"', '', $custom_js_body );
            $custom_js_body = str_replace( "type='text/javascript'", '', $custom_js_body );
            $custom_js_body = str_replace( '<script', '<script type="text/fwx-delayed-script"', $custom_js_body );
            echo $custom_js_body . "\n";
        } else {
            // Stealth desativado (global, por página ou Blank): executa normalmente
            echo $custom_js_body . "\n";
        }
    }
}
add_action( 'wp_footer', 'fwx_output_custom_page_scripts_footer', 100 );

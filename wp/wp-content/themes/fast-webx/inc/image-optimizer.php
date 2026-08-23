<?php
/**
 * Módulo de Otimização e Crop de Imagens
 * 
 * @package Fast_WebX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. Definir Tamanhos Exatos e Limpar Arquivos Inúteis
 */
function fwx_setup_images() {
    // Adiciona os tamanhos exatos do Fast WebX com hard crop
    add_image_size( 'fwx-home-grid', 600, 400, true );
    add_image_size( 'fwx-home-featured', 900, 600, true );
    add_image_size( 'fwx-single-thumb', 1200, 800, true );
    add_image_size( 'fwx-news-list', 400, 260, true );
    
    // Tamanhos da Loja dependem da ativação do módulo
    if ( fwx_get_option( 'loja_enabled', 0 ) ) {
        add_image_size( 'fwx-loja-thumb', 400, 300, true );
        add_image_size( 'fwx-loja-square-small', 150, 150, true );
        add_image_size( 'fwx-loja-square', 400, 400, true );
        add_image_size( 'fwx-loja-square-large', 800, 800, true );
    }
    
    add_image_size( 'fwx-compact-thumb', 200, 150, true );
    add_image_size( 'fwx-avatar', 200, 200, true );
}
add_action( 'after_setup_theme', 'fwx_setup_images' );

function fwx_disable_default_image_sizes( $sizes ) {
    if ( fwx_get_option( 'perf_disable_extra_image_sizes', 0 ) ) {
        unset( $sizes['medium'] );
        unset( $sizes['medium_large'] );
        unset( $sizes['large'] );
        unset( $sizes['1536x1536'] );
        unset( $sizes['2048x2048'] );
    }
    return $sizes;
}
add_filter( 'intermediate_image_sizes_advanced', 'fwx_disable_default_image_sizes' );

/**
 * 2. Injetar `decoding="async"`
 */
function fwx_add_async_decoding( $attr ) {
    if ( ! isset( $attr['decoding'] ) ) {
        $attr['decoding'] = 'async';
    }
    if ( isset( $attr['loading'] ) && $attr['loading'] === 'eager' ) {
        $attr['fetchpriority'] = 'high';
    }
    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'fwx_add_async_decoding' );

/**
 * 2b. Conversão Automática para WebP no Upload
 * Aplica conversão JPEG/PNG → WebP em todo upload novo, sem precisar rodar o otimizador em lote.
 * Controlado pela opção 'perf_auto_webp_upload' do painel (padrão: ativo).
 */
function fwx_auto_convert_upload_to_webp( $formats ) {
    if ( ! fwx_get_option( 'perf_auto_webp_upload', 0 ) ) {
        return $formats;
    }
    $formats['image/jpeg'] = 'image/webp';
    $formats['image/png']  = 'image/webp';
    return $formats;
}
add_filter( 'image_editor_output_format', 'fwx_auto_convert_upload_to_webp' );

/**
 * 2c. Corrigir tipo MIME no banco de dados para imagens convertidas para WebP
 */
function fwx_correct_webp_mime_type( $metadata, $attachment_id ) {
    if ( ! fwx_get_option( 'perf_auto_webp_upload', 0 ) ) {
        return $metadata;
    }
    
    $file = get_attached_file( $attachment_id );
    if ( $file && file_exists( $file ) ) {
        $path_info = pathinfo( $file );
        $extension = isset( $path_info['extension'] ) ? strtolower( $path_info['extension'] ) : '';
        
        if ( 'webp' === $extension ) {
            // Arquivo já é .webp no disco (convertido pelo image_editor_output_format)
            // Apenas garante que o tipo MIME no banco reflita a realidade
            $post = get_post( $attachment_id );
            if ( $post && 'image/webp' !== $post->post_mime_type ) {
                global $wpdb;
                $wpdb->update(
                    $wpdb->posts,
                    array( 'post_mime_type' => 'image/webp' ),
                    array( 'ID' => $attachment_id )
                );
                clean_post_cache( $attachment_id );
            }
        } elseif ( in_array( $extension, array( 'jpg', 'jpeg', 'png' ) ) ) {
            // Arquivo legado ainda não foi convertido - converte agora
            $new_file = $path_info['dirname'] . '/' . $path_info['filename'] . '.webp';
            
            $editor = wp_get_image_editor( $file );
            if ( ! is_wp_error( $editor ) ) {
                $saved = $editor->save( $new_file, 'image/webp' );
                if ( ! is_wp_error( $saved ) ) {
                    if ( file_exists( $new_file ) && $new_file !== $file ) {
                        @unlink( $file );
                    }
                    
                    update_attached_file( $attachment_id, $new_file );
                    
                    global $wpdb;
                    $wpdb->update(
                        $wpdb->posts,
                        array( 'post_mime_type' => 'image/webp' ),
                        array( 'ID' => $attachment_id )
                    );
                    clean_post_cache( $attachment_id );
                    
                    if ( isset( $metadata['file'] ) ) {
                        $metadata['file'] = str_replace( '.' . $path_info['extension'], '.webp', $metadata['file'] );
                    }
                }
            }
        }
    }
    return $metadata;
}
add_filter( 'wp_generate_attachment_metadata', 'fwx_correct_webp_mime_type', 10, 2 );





/**
 * 3. Menu e Interface de Regeneração no Painel WebX
 */
function fwx_register_media_optimizer_page() {
    add_submenu_page(
        'fast-webx',
        'Otimização de Mídia',
        'Otimizar Mídias',
        'manage_options',
        'fast-webx-media',
        'fwx_render_media_optimizer_page'
    );
}
add_action( 'admin_menu', 'fwx_register_media_optimizer_page' );

function fwx_enqueue_media_assets( $hook ) {
    if ( 'fast-webx_page_fast-webx-media' !== $hook ) {
        return;
    }
    wp_enqueue_script( 'fwx-media-optimizer', get_template_directory_uri() . '/assets/js/image-optimizer.js', array(), time(), true );
    wp_localize_script( 'fwx-media-optimizer', 'fwx_ajax', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'fwx_optimize_media' ),
        'version'  => FWX_VERSION
    ) );
}
add_action( 'admin_enqueue_scripts', 'fwx_enqueue_media_assets' );

function fwx_render_media_optimizer_page() {
    // Pega todos os IDs de imagens ativas (JPEG, PNG, WEBP)
    $images = get_posts( array(
        'post_type'      => 'attachment',
        'post_mime_type' => 'image',
        'post_status'    => 'inherit',
        'posts_per_page' => -1,
        'fields'         => 'ids'
    ) );
    
    $total = count( $images );
    $ids_json = wp_json_encode( $images );
    ?>
    <div class="wrap">
        <h1>Otimização de Mídias (Crop Retrospectivo)</h1>
        
        <?php if ( function_exists('fwx_is_active') && ! fwx_is_active() ) : ?>
            <?php fwx_render_lock_overlay(); ?>
        <?php else : ?>
        <div style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ccd0d4; margin-bottom: 30px; max-width: 800px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h3 style="margin-top:0; color:#2271b1;">O que este otimizador faz?</h3>
            <p>Este motor de <strong>Regeneração e Recorte Inteligente</strong> garante a perfeição visual e performance do seu site:</p>
            <ul style="list-style: disc; margin-left: 20px; line-height: 1.6;">
                <li><strong>Recorte Milimétrico (Hard Crop):</strong> Força as imagens nas proporções exatas exigidas pelo tema (3:2 e formatos do Layout F), eliminando distorções.</li>
                <li><strong>Sincronização de Layout:</strong> Adapta fotos antigas para "vestirem" perfeitamente o layout que você selecionou no painel (Grid, Lista ou News).</li>
                <li><strong>Performance:</strong> Garante que o navegador carregue o tamanho exato, ajudando a atingir PageSpeed 100/100 ao evitar redimensionamento via CSS.</li>
                <li><strong>Padrão de Qualidade:</strong> Recomendamos o uso de imagens originais com no mínimo <strong>1280x832px</strong> para garantir nitidez máxima em telas Retina e 4K.</li>
                <?php if ( fwx_get_option( 'loja_enabled', 0 ) ) : ?>
                    <li style="color: #2271b1;"><strong>Loja Ativa (Recorte 1:1):</strong> Serão processados os cortes físicos quadrados da Loja (<code>fwx-loja-square-small</code>, <code>fwx-loja-square</code> e <code>fwx-loja-square-large</code>).</li>
                <?php else : ?>
                    <li style="opacity: 0.65;"><strong>Loja Inativa:</strong> Os recortes físicos da Loja (1:1 e thumb) estão suspensos e não serão gerados.</li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="card" style="max-width: 600px; padding: 20px; display: inline-block; box-sizing: border-box; border-radius: 8px;">
            <h3>Recortar e Regenerar Banco de Imagens</h3>
            <p><strong>Total de imagens no banco para processar:</strong> <span id="fwx-total-count"><?php echo esc_html( $total ); ?></span></p>
            
            <div style="margin: 20px 0; padding: 15px; background: #f0f6fb; border-radius: 6px;">
                <label style="display:block;margin-bottom:10px; font-weight:500;">
                    <input type="checkbox" id="fwx-skip-optimized" checked> Pular imagens já adaptadas (Altamente Recomendado)
                </label>
                <?php $enable_batch_alt = fwx_get_option( 'batch_img_alt', 0 ); ?>
                <label style="display:block;margin-bottom:10px; font-weight:500; color: #2271b1; <?php echo empty( $enable_batch_alt ) ? 'opacity: 0.5; cursor: not-allowed;' : ''; ?>">
                    <input type="checkbox" id="fwx-batch-alt-gen" <?php disabled( empty( $enable_batch_alt ), true ); ?> <?php checked( ! empty( $enable_batch_alt ), true ); ?>> Auto-Alt: Preencher tags ALT em lote para imagens sem descrição (SEO)
                    <?php if ( empty( $enable_batch_alt ) ) : ?>
                        <span style="font-size: 11px; background: #e0e0e0; color: #666; padding: 2px 6px; border-radius: 4px; margin-left: 8px; vertical-align: middle;">Requer "Gerador de ALT em Lote" ativo na Aba SEO</span>
                    <?php endif; ?>
                </label>
                <?php $disable_extra_sizes = fwx_get_option( 'perf_disable_extra_image_sizes', 0 ); ?>
                <label style="display:block;margin-bottom:10px; font-weight:500; color: #d63638; <?php echo empty( $disable_extra_sizes ) ? 'opacity: 0.5; cursor: not-allowed;' : ''; ?>">
                    <input type="checkbox" id="fwx-clean-orphans" <?php disabled( empty( $disable_extra_sizes ), true ); ?>> Limpeza de Mídias Órfãs (Apagar tamanhos antigos do WP)
                    <?php if ( empty( $disable_extra_sizes ) ) : ?>
                        <span style="font-size: 11px; background: #e0e0e0; color: #666; padding: 2px 6px; border-radius: 4px; margin-left: 8px; vertical-align: middle;">Requer "Tamanhos de Imagem Extra" ativo na Aba Desempenho</span>
                    <?php endif; ?>
                </label>
                <label style="display:block;margin-bottom:10px; font-weight:500; color: #2271b1;">
                    <input type="checkbox" id="fwx-convert-webp" checked> Converter para WebP (Compressão 90% - Alta Performance)
                </label>
                <?php $loja_enabled = fwx_get_option( 'loja_enabled', 0 ); ?>
                <label style="display:block;margin-bottom:10px; font-weight:500; color: #2271b1; <?php echo empty( $loja_enabled ) ? 'opacity: 0.5; cursor: not-allowed;' : ''; ?>">
                    <input type="checkbox" id="fwx-generate-loja-sizes" <?php disabled( empty( $loja_enabled ), true ); ?> <?php checked( ! empty( $loja_enabled ), true ); ?>> Gerar recortes da Loja (Recorte 1:1)
                    <?php if ( empty( $loja_enabled ) ) : ?>
                        <span style="font-size: 11px; background: #e0e0e0; color: #666; padding: 2px 6px; border-radius: 4px; margin-left: 8px; vertical-align: middle;">Requer "Loja" ativa na Aba Loja</span>
                    <?php endif; ?>
                </label>
                <label style="display:block; font-weight:500;">
                    <input type="checkbox" id="fwx-throttle-process"> Processamento Seguro (Intervalo de 1s entre imagens)
                </label>
                <p class="description" style="margin-top:10px;"><strong>⚠️ Cuidado:</strong> A limpeza apaga arquivos físicos de tamanhos que o tema não usa. Isso economiza muito espaço, mas pode quebrar imagens em posts muito antigos que dependiam de tamanhos padrão do WordPress.</p>
            </div>

            <?php if ( $total > 0 ) : ?>
                <button type="button" class="button button-primary button-large" id="fwx-btn-start-crop">Iniciar Regeneração</button>
                <button type="button" class="button button-secondary button-large" id="fwx-btn-stop-crop" style="display: none; margin-left: 10px; border-color: #d63638; color: #d63638;">Forçar Parada</button>
                <div id="fwx-progress-container" style="display: none; margin-top: 20px;">
                    <div style="background: #e0e0e0; height: 20px; border-radius: 4px; overflow: hidden;">
                        <div id="fwx-progress-bar" style="background: #0073aa; width: 0%; height: 100%; transition: width 0.3s;"></div>
                    </div>
                    <p style="margin-top: 10px; font-weight: bold;" id="fwx-status-msg">Processando: 0%</p>
                    
                    <div id="fwx-realtime-logs" style="margin-top: 20px; background: #1e1e1e; color: #00ff00; padding: 15px; font-family: monospace; font-size: 12px; border-radius: 4px; height: 180px; overflow-y: auto; border: 1px solid #333;">
                        > Aguardando início...
                    </div>
                </div>
            <?php else : ?>
                <p style="color: green;">Nenhuma imagem pendente no sistema.</p>
            <?php endif; ?>
        </div>
        
        <script>
            window.fwxMediaIds = <?php echo $ids_json; ?>;
        </script>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * 4. Lógica de Crop AJAX (Regeneração)
 */
function fwx_ajax_regenerate_image() {
    check_ajax_referer( 'fwx_optimize_media', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Sem permissão.' );
    }

    $attachment_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
    $skip_optimized = isset( $_POST['skip_optimized'] ) && $_POST['skip_optimized'] === 'true';
    $generate_loja = isset( $_POST['generate_loja'] ) && $_POST['generate_loja'] === 'true';
    $loja_enabled = fwx_get_option( 'loja_enabled', 0 );
    
    if ( ! $attachment_id ) {
        wp_send_json_error( 'ID inválido.' );
    }

    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    
    $file = get_attached_file( $attachment_id );
    if ( ! file_exists( $file ) ) {
        wp_send_json_error( 'Arquivo original não existe.' );
    }

    // Se a opção de gerar crops da loja for falsa ou a loja estiver desativada globalmente
    if ( ! $generate_loja || ! $loja_enabled ) {
        add_filter( 'intermediate_image_sizes_advanced', function( $sizes ) {
            unset( $sizes['fwx-loja-thumb'] );
            unset( $sizes['fwx-loja-square-small'] );
            unset( $sizes['fwx-loja-square'] );
            unset( $sizes['fwx-loja-square-large'] );
            return $sizes;
        }, 99 );
    }

    // Lógica de Conversão para WebP
    if ( isset( $_POST['convert_webp'] ) && $_POST['convert_webp'] === 'true' ) {
        add_filter( 'image_editor_output_format', function( $formats ) {
            $formats['image/jpeg'] = 'image/webp';
            $formats['image/png']  = 'image/webp';
            return $formats;
        });
        add_filter( 'wp_editor_set_quality', function( $quality, $mime_type ) {
            return 90;
        }, 999, 2 );
    }

    // Lógica de Geração de ALT em Lote
    $alt_status = '';
    if ( isset( $_POST['generate_alt'] ) && $_POST['generate_alt'] === 'true' ) {
        $current_alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
        if ( empty( $current_alt ) ) {
            $img_title = get_the_title( $attachment_id );
            if ( empty( $img_title ) && ! empty( $file ) ) {
                $img_title = pathinfo( $file, PATHINFO_FILENAME );
            }
            if ( ! empty( $img_title ) ) {
                $img_alt = ucfirst( str_replace( array( '-', '_' ), ' ', $img_title ) );
                update_post_meta( $attachment_id, '_wp_attachment_image_alt', $img_alt );
                if ( function_exists( 'clean_post_cache' ) ) {
                    clean_post_cache( $attachment_id );
                }
                $alt_status = ' [+ALT Gerado]';
            }
        }
    }

    // Verificar se já tem os tamanhos do tema
    $metadata_old = wp_get_attachment_metadata( $attachment_id );
    if ( $skip_optimized && isset( $metadata_old['sizes']['fwx-home-grid'] ) ) {
        wp_send_json_success( array( 
            'skipped'  => true,
            'message'  => 'Já otimizada.',
            'filename' => basename( $file ) . $alt_status
        ) );
    }

    $metadata = wp_generate_attachment_metadata( $attachment_id, $file );
    
    // Lógica de Limpeza de Órfãs
    if ( isset( $_POST['clean_orphans'] ) && $_POST['clean_orphans'] === 'true' ) {
        $old_sizes = isset( $metadata_old['sizes'] ) ? $metadata_old['sizes'] : array();
        $new_sizes = isset( $metadata['sizes'] ) ? $metadata['sizes'] : array();
        $upload_dir = wp_upload_dir();
        $base_path = dirname( $file ) . '/';

        foreach ( $old_sizes as $size_key => $size_data ) {
            // Se o tamanho antigo não existe mais no novo metadata, apagamos o arquivo
            if ( ! isset( $new_sizes[ $size_key ] ) ) {
                $old_file = $base_path . $size_data['file'];
                if ( file_exists( $old_file ) ) {
                    @unlink( $old_file );
                }
            }
        }
    }

    if ( ! is_wp_error( $metadata ) && ! empty( $metadata ) ) {
        wp_update_attachment_metadata( $attachment_id, $metadata );
        
        $versions = array_keys( $metadata['sizes'] );
        $fwx_versions = array_filter( $versions, function($v) { return strpos($v, 'fwx-') === 0; } );
        
        wp_send_json_success( array( 
            'message'  => 'Adaptada com sucesso.',
            'filename' => basename( $file ) . $alt_status,
            'versions' => implode( ', ', $fwx_versions )
        ) );
    } else {
        wp_send_json_error( 'Erro ao processar imagem ou formato incompatível.' );
    }
}
add_action( 'wp_ajax_fwx_regenerate_image', 'fwx_ajax_regenerate_image' );

/**
 * 5. Salvar Log Final na Opção do Painel
 */
function fwx_ajax_finalize_media_logs() {
    check_ajax_referer( 'fwx_optimize_media', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error();
    }

    $count = isset( $_POST['count'] ) ? intval( $_POST['count'] ) : 0;
    $timestamp = current_time( 'd/m/Y H:i:s' );
    $layout = fwx_get_option( 'home_layout', 'layout-a' );
    
    $new_entry = "[{$timestamp}] Sucesso: {$count} imagens adaptadas aos padrões do tema.\n";
    $old_logs = get_option( 'fwx_media_logs', '' );
    
    // Mantém apenas os últimos 1000 caracteres
    $final_log = substr( $new_entry . $old_logs, 0, 1000 );
    
    update_option( 'fwx_media_logs', $final_log );
    wp_send_json_success();
}
add_action( 'wp_ajax_fwx_finalize_media_logs', 'fwx_ajax_finalize_media_logs' );

/**
 * 6. Expor e Mapear Tamanhos do Tema no Gutenberg e Editor de Blocos
 * Garante que os tamanhos otimizados permaneçam selecionáveis mesmo se os padrões forem desativados.
 */
function fwx_add_theme_image_sizes_to_editor( $sizes ) {
    $theme_sizes = array(
        'fwx-home-grid'     => __( 'Grid Fast WebX (600x400)', 'fast-webx' ),
        'fwx-home-featured' => __( 'Destaque Fast WebX (900x600)', 'fast-webx' ),
        'fwx-single-thumb'  => __( 'Single Fast WebX (1200x800)', 'fast-webx' ),
        'fwx-news-list'     => __( 'Notícias Fast WebX (400x260)', 'fast-webx' ),
    );

    if ( fwx_get_option( 'loja_enabled', 0 ) ) {
        $theme_sizes = array_merge( $theme_sizes, array(
            'fwx-loja-thumb'        => __( 'Loja Fast WebX (400x300)', 'fast-webx' ),
            'fwx-loja-square-small' => __( 'Loja Fast WebX Quadrada Pequena (150x150)', 'fast-webx' ),
            'fwx-loja-square'       => __( 'Loja Fast WebX Quadrada (400x400)', 'fast-webx' ),
            'fwx-loja-square-large' => __( 'Loja Fast WebX Quadrada Grande (800x800)', 'fast-webx' ),
        ) );
    }

    $theme_sizes['fwx-compact-thumb'] = __( 'Compacto Fast WebX (200x150)', 'fast-webx' );

    return array_merge( $sizes, $theme_sizes );
}
add_filter( 'image_size_names_choose', 'fwx_add_theme_image_sizes_to_editor' );

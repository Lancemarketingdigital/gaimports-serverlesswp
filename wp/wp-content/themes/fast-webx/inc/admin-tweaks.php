<?php
/**
 * Otimizações e Tweaks para o Painel Administrativo
 * 
 * @package Fast_WebX
 * @since   3.1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Adiciona a coluna ID nas listagens de Posts e Páginas
 * Ativado via Painel Fast WebX > Extras
 */
function fwx_admin_ids_setup() {
    $options = get_option( 'fast_webx_options' );
    $show_ids = isset( $options['show_admin_ids'] ) ? $options['show_admin_ids'] : 0;

    if ( $show_ids ) {
        // Para Posts
        add_filter( 'manage_posts_columns', 'fwx_add_id_column' );
        add_action( 'manage_posts_custom_column', 'fwx_show_id_column_content', 10, 2 );

        // Para Páginas
        add_filter( 'manage_pages_columns', 'fwx_add_id_column' );
        add_action( 'manage_pages_custom_column', 'fwx_show_id_column_content', 10, 2 );

        // Estilo para a coluna ID (largura fixa)
        add_action( 'admin_head', function() {
            echo '<style>.column-fwx_post_id { width: 50px !important; text-align: center; font-weight: 600; color: #777; }</style>';
        });
    }
}
add_action( 'admin_init', 'fwx_admin_ids_setup' );

/**
 * Adiciona o cabeçalho da coluna
 */
function fwx_add_id_column( $columns ) {
    $columns['fwx_post_id'] = 'ID';
    return $columns;
}

/**
 * Exibe o conteúdo da coluna (ID do post)
 */
function fwx_show_id_column_content( $column, $post_id ) {
    if ( 'fwx_post_id' === $column ) {
        echo esc_html( $post_id );
    }
}

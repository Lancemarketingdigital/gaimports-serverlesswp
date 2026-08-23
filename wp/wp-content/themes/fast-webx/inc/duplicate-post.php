<?php
/**
 * Fast WebX - Módulo Injetor: Duplicador Nativo de Conteúdo
 * Não precisamos de plugins para duplicar posts. O próprio core faz isso com poucas linhas.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function fwx_duplicate_post_as_draft() {
    global $wpdb;
    
    // Verificações de segurança
    if ( ! ( isset( $_GET['post'] ) || isset( $_POST['post'] ) ) || ! isset( $_GET['action'] ) || $_GET['action'] != 'fwx_duplicate_post_as_draft' ) {
        wp_die( 'Ação de duplicação inválida.' );
    }

    check_admin_referer( basename( __FILE__ ) );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'Permissões insuficientes.' );
    }

    // Identificar ID do post
    $post_id = ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
    
    // Obter o post
    $post = get_post( $post_id );
    if ( ! $post ) {
        wp_die( 'Falha na leitura do post para duplicar.' );
    }

    $current_user = wp_get_current_user();
    
    // Preparar os dados para a cópia
    $args = array(
        'comment_status' => $post->comment_status,
        'ping_status'    => $post->ping_status,
        'post_author'    => $current_user->ID,
        'post_content'   => $post->post_content,
        'post_excerpt'   => $post->post_excerpt,
        'post_name'      => $post->post_name . '-copia',
        'post_parent'    => $post->post_parent,
        'post_password'  => $post->post_password,
        'post_status'    => 'draft',
        'post_title'     => $post->post_title . ' (Cópia)',
        'post_type'      => $post->post_type,
        'to_ping'        => $post->to_ping,
        'menu_order'     => $post->menu_order
    );

    // Inserir o post novo formatado
    $new_post_id = wp_insert_post( $args );

    // Copiar taxonomia e metadados pesados
    $taxonomies = get_object_taxonomies( $post->post_type );
    foreach ( $taxonomies as $taxonomy ) {
        $post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
        wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
    }

    $post_meta_infos = $wpdb->get_results( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id" );
    if ( count( $post_meta_infos ) != 0 ) {
        $sql_query     = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
        $sql_query_sel = array();
        foreach ( $post_meta_infos as $meta_info ) {
            $meta_key = $meta_info->meta_key;
            if( $meta_key == '_wp_old_slug' ) continue;
            
            $meta_value = addslashes( $meta_info->meta_value );
            $sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
        }
        $sql_query .= implode( " UNION ALL ", $sql_query_sel );
        $wpdb->query( $sql_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

    // Redireciona de volta para a lista (post ou page)
    wp_safe_redirect( admin_url( 'edit.php?post_type=' . $post->post_type ) );
    exit;
}
add_action( 'admin_action_fwx_duplicate_post_as_draft', 'fwx_duplicate_post_as_draft' );

/**
 * Adicionar o link de Duplicar na lista de ações das páginas e posts (Hover)
 */
function fwx_duplicate_post_link( $actions, $post ) {
    if ( current_user_can( 'edit_posts' ) ) {
        $actions['duplicate'] = '<a href="' . wp_nonce_url( admin_url( 'admin.php?action=fwx_duplicate_post_as_draft&post=' . $post->ID ), basename( __FILE__ ) ) . '" title="Duplicar este item" rel="permalink">Duplicar</a>';
    }
    return $actions;
}
add_filter( 'post_row_actions', 'fwx_duplicate_post_link', 10, 2 );
add_filter( 'page_row_actions', 'fwx_duplicate_post_link', 10, 2 );

<?php
/**
 * Fast WebX Core Tools & Maintenance
 * 
 * @package Fast_WebX
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Listener para ações do Core (Limpeza, Otimização)
 */
function fwx_handle_core_actions() {
    if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) return;
    
    if ( isset( $_POST['fwx_core_action'] ) ) {
        $action = $_POST['fwx_core_action'];
        
        if ( 'clear_transients' === $action ) {
            global $wpdb;
            $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'" );
            update_option( 'fwx_last_transients_clear', current_time( 'd/m/Y H:i' ) );
            add_settings_error( 'fast_webx_options', 'transients_cleared', 'Todos os transients do tema foram limpos!', 'updated' );
        }
        
        if ( 'optimize_db' === $action ) {
            // Limpeza leve de opções temporárias do tema
            delete_option( 'fwx_last_optimization_check' );
            update_option( 'fwx_last_core_optimize', current_time( 'd/m/Y H:i' ) );
            add_settings_error( 'fast_webx_options', 'db_optimized', 'Otimização do Core concluída com sucesso.', 'updated' );
        }

        if ( 'clear_media_logs' === $action ) {
            delete_option( 'fwx_media_logs' );
            add_settings_error( 'fast_webx_options', 'media_logs_cleared', 'Histórico de mídias removido com sucesso.', 'updated' );
        }
    }
}
add_action( 'admin_init', 'fwx_handle_core_actions' );

/**
 * SEO de Recuperação: Redirecionamento 301 Automático
 * Segue o Roadmap: Redireciona erros 404 para a Home Page.
 */
function fwx_execute_auto_redirects() {
    if ( is_404() && fwx_get_option( 'enable_auto_301', 0 ) ) {
        $custom_url = fwx_get_option( 'custom_301_url', '' );
        $redirect_url = ! empty( $custom_url ) ? esc_url( $custom_url ) : home_url( '/' );
        
        wp_redirect( $redirect_url, 301 );
        exit;
    }
}
add_action( 'template_redirect', 'fwx_execute_auto_redirects' );

/**
 * Core Clean: Segurança e Limpeza de Endpoints
 */
if ( fwx_get_option( 'disable_app_passwords', 0 ) ) {
    add_filter( 'wp_is_application_passwords_available', '__return_false' );
}

if ( fwx_get_option( 'clean_rest_api', 0 ) ) {
    add_filter( 'rest_authentication_errors', function( $result ) {
        // Se já houve erro anterior, propaga
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        // Se o usuário estiver autenticado (Gutenberg, Admin, etc.), permite o acesso normalmente
        if ( is_user_logged_in() ) {
            return $result;
        }

        // Bloqueia endpoints sensíveis apenas para visitantes anônimos (evita enumeração de usuários e plugins)
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

        $restricted_routes = array(
            '/wp/v2/users',
            '/wp/v2/plugins',
            '/wp/v2/themes',
            '/wp/v2/settings',
        );

        foreach ( $restricted_routes as $route ) {
            if ( strpos( $request_uri, $route ) !== false ) {
                return new WP_Error(
                    'rest_forbidden',
                    'Acesso restrito.',
                    array( 'status' => 401 )
                );
            }
        }

        return $result;
    }, 20 );
}

<?php
/**
 * Plugin Name: Lance - WordPress em Português do Brasil
 * Description: Mantém o WordPress e o painel administrativo em pt-BR.
 */
if (!defined('ABSPATH')) exit;

add_filter('locale', static function ($locale) {
    return 'pt_BR';
}, 999);

add_filter('determine_locale', static function ($locale) {
    return 'pt_BR';
}, 999);

add_action('admin_init', static function () {
    if (get_option('WPLANG') !== 'pt_BR') {
        update_option('WPLANG', 'pt_BR');
    }
    $user_id = get_current_user_id();
    if ($user_id && get_user_meta($user_id, 'locale', true) !== 'pt_BR') {
        update_user_meta($user_id, 'locale', 'pt_BR');
    }
});

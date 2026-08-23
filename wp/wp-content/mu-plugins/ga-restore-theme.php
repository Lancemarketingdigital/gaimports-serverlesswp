<?php
/**
 * Plugin Name: GA Imports - Restore Original Theme
 * Description: Ativa uma vez o tema restaurado e garante a estrutura original da Home e dos links.
 */
if (!defined('ABSPATH')) exit;
add_action('init', function () {
    if (get_option('ga_restored_theme_20260823') === '1') return;
    if (wp_get_theme('gaimports-restored')->exists()) {
        switch_theme('gaimports-restored');
        $home = get_page_by_path('home', OBJECT, 'page');
        $blog = get_page_by_path('blog', OBJECT, 'page');
        update_option('show_on_front', 'page');
        if ($home) update_option('page_on_front', (int)$home->ID);
        if ($blog) update_option('page_for_posts', (int)$blog->ID);
        update_option('permalink_structure', '/%postname%/');
        flush_rewrite_rules(false);
        update_option('ga_restored_theme_20260823', '1', false);
    }
}, 1);

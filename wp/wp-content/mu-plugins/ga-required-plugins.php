<?php
/**
 * Plugin Name: GA Imports Required Plugins
 * Description: Ensures the required GA Imports plugins are activated when their official code is present.
 */
if (!defined('ABSPATH')) exit;

add_action('init', function () {
    if (!function_exists('is_plugin_active') || !function_exists('activate_plugin')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $required = [
        'code-snippets/code-snippets.php',
        'wp-reviews-plugin-for-google/wp-reviews-plugin-for-google.php',
    ];

    foreach ($required as $plugin) {
        $file = WP_PLUGIN_DIR . '/' . $plugin;
        if (is_file($file) && !is_plugin_active($plugin)) {
            activate_plugin($plugin, '', false, true);
        }
    }
}, 1);

add_action('init', function () {
    if (!shortcode_exists('trustindex')) {
        add_shortcode('trustindex', static function () {
            return '';
        });
    }
}, 99);

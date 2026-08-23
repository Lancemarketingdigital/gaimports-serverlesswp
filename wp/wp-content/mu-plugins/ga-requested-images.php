<?php
/**
 * Plugin Name: GA Imports - Imagens solicitadas
 * Description: Mantém as imagens principais da Home e a logo nos URLs aprovados do Vercel Blob.
 */
if (!defined('ABSPATH')) exit;

function ga_requested_image_urls(string $html): string {
    $hero = 'https://ocng1uhcwz3c4z8o.public.blob.vercel-storage.com/wp-content/uploads/2026/08/cropped-hero-medicamentos.webp';
    $service = 'https://ocng1uhcwz3c4z8o.public.blob.vercel-storage.com/wp-content/uploads/2026/08/atendimento-ga-imports-1.webp';
    $logo = 'https://ocng1uhcwz3c4z8o.public.blob.vercel-storage.com/wp-content/uploads/2026/08/logo-ga-imports.png';

    $html = preg_replace('~https?://[^\"\'<>\s]+/wp-content/uploads/2026/08/hero-medicamentos\.webp~i', $hero, $html) ?? $html;
    $html = preg_replace('~https?://[^\"\'<>\s]+/wp-content/uploads/2026/08/atendimento-ga-imports\.webp~i', $service, $html) ?? $html;
    $html = preg_replace('~https?://[^\"\'<>\s]+/wp-content/uploads/2026/08/logo-ga-imports\.png~i', $logo, $html) ?? $html;
    return $html;
}

add_filter('the_content', 'ga_requested_image_urls', 99);
add_filter('widget_text_content', 'ga_requested_image_urls', 99);

add_filter('wp_get_attachment_url', function($url, $id) {
    $file = (string) get_post_meta((int)$id, '_wp_attached_file', true);
    if ($file && basename($file) === 'logo-ga-imports.png') {
        return 'https://ocng1uhcwz3c4z8o.public.blob.vercel-storage.com/wp-content/uploads/2026/08/logo-ga-imports.png';
    }
    return $url;
}, 99, 2);

add_action('init', function() {
    $home = get_page_by_path('home', OBJECT, 'page');
    if (!$home instanceof WP_Post) return;
    $updated = ga_requested_image_urls((string)$home->post_content);
    if ($updated !== $home->post_content) {
        wp_update_post(wp_slash([
            'ID' => $home->ID,
            'post_content' => $updated,
        ]));
        clean_post_cache($home->ID);
    }
}, 20);

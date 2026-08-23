<?php
/**
 * Painel Administrativo do Fast WebX - V2 (Premium Tabs)
 * 
 * @package Fast_WebX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Fast_WebX_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    public function add_admin_menu() {
        add_menu_page(
            'Fast WebX',
            'Fast WebX',
            'manage_options',
            'fast-webx',
            array( $this, 'render_admin_page' ),
            'dashicons-performance',
            60
        );
    }

    public function enqueue_admin_assets( $hook ) {
        if ( strpos( $hook, 'fast-webx' ) === false ) {
            return;
        }
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'fast-webx-admin-js', get_template_directory_uri() . '/assets/js/admin.js', array( 'wp-color-picker', 'jquery' ), '1.0.0', true );
        // Obrigatório para que o botão "Enviar Imagem" abra a Biblioteca de Mídia
        wp_enqueue_media();
        
        // Estilos CSS do novo painel em abas injetados dinamicamente para não precisar de um arquivo novo na fase final
        wp_add_inline_style( 'wp-color-picker', '
            .fwx-admin-wrap { max-width: 1200px; margin-top: 20px; font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif; }
            .fwx-header { display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 20px 30px; border-radius: 8px 8px 0 0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
            .fwx-header h1 { margin: 0; font-size: 24px; font-weight: 600; color: #1d2327; }
            .fwx-tabs { display: flex; background: #f0f0f1; padding: 0 15px; border-bottom: 1px solid #dcdcde; box-shadow: inset 0 -1px 0 #dcdcde; overflow-x: auto; overflow-y: hidden; -ms-overflow-style: none; scrollbar-width: none; }
            .fwx-tabs::-webkit-scrollbar { display: none; }
            .fwx-tab-btn { background: transparent; border: none; padding: 15px 12px; cursor: pointer; font-size: 13px; font-weight: 600; color: #50575e; transition: all 0.2s; margin-bottom: -1px; white-space: nowrap; display: flex; align-items: center; gap: 6px; }
            .fwx-tab-btn svg { flex-shrink: 0; }
            .fwx-tab-btn:hover { color: #2271b1; }
            .fwx-tab-btn.active { color: #2271b1; border-bottom: 3px solid #2271b1; }
            .fwx-content-box { background: #fff; padding: 30px; border-radius: 0 0 8px 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); min-height: 400px; }
            .fwx-tab-content { display: none; }
            .fwx-tab-content.active { display: block; animation: fadeIn 0.3s ease; }
            @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
            .fwx-form-table th { font-weight: 600; width: 30%; }
            .fwx-form-table td { padding-bottom: 20px; }
            .fwx-section-title { font-size: 18px; color: #1d2327; margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 1px solid #f0f0f1; }
            .fwx-input-large { width: 100%; max-width: 600px; }
            .fwx-disabled-row { opacity: 0.35; pointer-events: none; transition: opacity 0.3s ease; }
            .fwx-cores-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px 24px; margin-top: 15px; margin-bottom: 30px !important; }
            .fwx-cores-grid tbody { display: contents; }
            .fwx-cores-grid tr { display: flex; flex-direction: column; gap: 8px; padding: 14px; background: #fcfcfc; border: 1px solid #e5e7eb; border-radius: 6px; margin: 0 !important; box-sizing: border-box; }
            .fwx-cores-grid th, .fwx-cores-grid td { display: block; width: 100% !important; padding: 0 !important; }
            .fwx-cores-grid th { margin-bottom: 4px; }
            .fwx-cores-grid th label { font-weight: 600; font-size: 13px; color: #1d2327; }
            .fwx-cores-grid .description { margin-top: 6px; font-size: 11px; line-height: 1.4; color: #6b7280; }
            @media (max-width: 768px) {
                .fwx-cores-grid { grid-template-columns: 1fr; }
            }
        ' );
    }

    public function register_settings() {
        register_setting( 'fast_webx_options_group', 'fast_webx_options', array( $this, 'sanitize_options' ) );
    }

    public function sanitize_options( $input ) {
        $sanitized = array();
        $fields_hex = ['primary_color', 'secondary_color', 'text_color', 'text_color_dark', 'link_color', 'link_color_dark', 'hero_color', 'hero_color_dark', 'hero_text_color', 'hero_text_color_dark', 'home_lp_color', 'home_lp_text_color', 'home_hero_bg_color', 'home_hero_bg_color_dark', 'home_hero_text_color', 'home_hero_text_color_dark', 'cat_text_color'];
        foreach($fields_hex as $fh) {
            if ( isset( $input[$fh] ) ) $sanitized[$fh] = sanitize_hex_color( $input[$fh] );
        }
        
        $fields_txt = ['home_layout', 'category_layout', 'hero_layout', 'single_post_layout', 'typography', 'sidebar_widget_order', 'sidebar_last_widget', 'ad_injection_point_1', 'ad_injection_point_2', 'ad_injection_point_3', 'lgpd_text', 'lgpd_policy_url', 'lead_form_title', 'lead_form_desc', 'lead_redirect_url', 'webhook_url', 'bio_user_id', 'bio_link_text_1', 'bio_link_url_1', 'bio_link_text_2', 'bio_link_url_2', 'bio_link_text_3', 'bio_link_url_3', 'bio_link_text_4', 'bio_link_url_4', 'bio_link_text_5', 'bio_link_url_5', 'social_facebook', 'social_instagram', 'social_youtube', 'social_tiktok', 'social_linkedin', 'social_twitter', 'social_pinterest', 'social_whatsapp', 'home_lp_type', 'home_hero_title', 'home_hero_desc', 'home_hero_btn_text', 'home_hero_btn_url', 'home_hero_img', 'home_hero_media_type', 'home_hero_video', 'home_about_title', 'home_services_title', 'home_services_desc', 'home_services_count', 'home_service_title_1', 'home_service_desc_1', 'home_service_link_1', 'home_service_title_2', 'home_service_desc_2', 'home_service_link_2', 'home_service_title_3', 'home_service_desc_3', 'home_service_link_3', 'home_service_title_4', 'home_service_desc_4', 'home_service_link_4', 'home_service_title_5', 'home_service_desc_5', 'home_service_link_5', 'home_service_title_6', 'home_service_desc_6', 'home_service_link_6', 'home_steps_title', 'home_steps_desc', 'home_step_1_title', 'home_step_1_desc', 'home_step_2_title', 'home_step_2_desc', 'home_step_3_title', 'home_step_3_desc', 'home_capture_title', 'home_capture_desc', 'home_capture_shortcode', 'home_testimonials_title', 'home_testimonials_desc', 'home_testimonial_1_text', 'home_testimonial_1_author', 'home_testimonial_1_img', 'home_testimonial_2_text', 'home_testimonial_2_author', 'home_testimonial_2_img', 'home_testimonial_3_text', 'home_testimonial_3_author', 'home_testimonial_3_img', 'home_testimonial_4_text', 'home_testimonial_4_author', 'home_testimonial_4_img', 'home_testimonial_5_text', 'home_testimonial_5_author', 'home_testimonial_5_img', 'home_testimonial_6_text', 'home_testimonial_6_author', 'home_testimonial_6_img', 'home_testimonials_count', 'home_blog_title', 'home_blog_desc', 'home_contact_title', 'home_contact_desc', 'exit_intent_title', 'exit_intent_frequency', 'custom_301_url', 'home_meta_desc', 'bio_custom_name', 'bio_custom_text', 'bio_footer_text', 'bio_highlight_id', 'header_cta_text', 'header_cta_url', 'theme_language', 'loja_catalog_title', 'loja_cta_text', 'loja_columns', 'thumbnail_fallback_type'];
        foreach($fields_txt as $ft) {
            if ( isset( $input[$ft] ) ) $sanitized[$ft] = sanitize_text_field( $input[$ft] );
        }
        
        $fields_bool = ['sidebar_posts', 'sidebar_pages', 'sidebar_archive', 'sidebar_loja', 'sidebar_loja_single', 'hide_sidebar_mobile', 'enable_toc', 'toc_start_collapsed', 'enable_sticky_toc', 'stealth_mode', 'enable_reading_progress', 'enable_lightbox', 'enable_back_to_top', 'sticky_sidebar_widget', 'hide_single_thumb', 'hide_page_thumb', 'show_single_breadcrumb', 'show_page_breadcrumb', 'show_single_excerpt', 'sticky_header', 'enable_header_search', 'enable_header_mode_toggle', 'disable_comments', 'enable_author_box', 'enable_related_posts', 'enable_sidebar_related', 'perf_disable_emojis', 'perf_disable_gravatar', 'perf_disable_rss', 'perf_disable_extra_image_sizes', 'perf_disable_heartbeat', 'perf_auto_webp_upload', 'enable_contact_form', 'enable_sidebar_author', 'show_sharing_buttons', 'enable_sidebar_lead_widget', 'perf_lazy_youtube', 'perf_widget_transients', 'home_show_hero', 'home_show_about', 'home_show_services', 'home_show_steps', 'home_show_blog', 'home_show_capture', 'home_show_testimonials', 'home_show_contact', 'enable_exit_intent', 'enable_auto_301', 'disable_app_passwords', 'clean_rest_api', 'enable_excerpt_as_meta', 'enable_cat_desc_as_meta', 'show_admin_ids', 'enable_whatsapp_capture', 'lead_email_optional', 'header_cta_enable', 'enable_link_margin', 'auto_img_alt', 'batch_img_alt', 'enable_lazy_load_global', 'enable_smart_lazy_load', 'home_service_new_tab_1', 'home_service_new_tab_2', 'home_service_new_tab_3', 'home_service_new_tab_4', 'home_service_new_tab_5', 'home_service_new_tab_6', 'seo_title_case', 'seo_external_links_blank', 'seo_toc_compatibility', 'loja_show_price', 'loja_enabled', 'disable_ads_loja_single', 'loja_enable_related', 'loja_enable_sidebar_recent', 'author_widget_in_single_post', 'author_widget_in_single_product', 'author_widget_in_archive_post', 'author_widget_in_archive_product', 'lead_widget_in_single_post', 'lead_widget_in_single_product', 'lead_widget_in_archive_post', 'lead_widget_in_archive_product', 'related_widget_in_single_post', 'related_widget_in_single_product', 'related_widget_in_archive_post', 'related_widget_in_archive_product', 'recent_products_widget_in_single_post', 'recent_products_widget_in_single_product', 'recent_products_widget_in_archive_post', 'recent_products_widget_in_archive_product', 'enable_sidebar_categories', 'categories_widget_in_single_post', 'categories_widget_in_single_product', 'categories_widget_in_archive_post', 'categories_widget_in_archive_product', 'enable_tts', 'show_single_tags'];
        foreach($fields_bool as $fb) {
            // Se o valor for '1', true, ou o número 1, salva como 1. Caso contrário, 0.
            // Isso resolve o bug onde o import (que envia a chave com valor 0) acabava ativando a função.
            $sanitized[$fb] = (isset($input[$fb]) && ($input[$fb] == 1 || $input[$fb] === true)) ? 1 : 0;
        }

        if ( isset( $input['home_about_text'] ) ) $sanitized['home_about_text'] = wp_kses_post( $input['home_about_text'] );
        if ( isset( $input['home_contact_content'] ) ) $sanitized['home_contact_content'] = current_user_can('unfiltered_html') ? $input['home_contact_content'] : wp_kses_post( $input['home_contact_content'] );
        if ( isset( $input['footer_text'] ) ) $sanitized['footer_text'] = wp_kses_post( $input['footer_text'] );
        if ( isset( $input['footer_aux_text'] ) ) $sanitized['footer_aux_text'] = wp_kses_post( $input['footer_aux_text'] );
        if ( isset( $input['footer_desc'] ) ) $sanitized['footer_desc'] = wp_kses_post( $input['footer_desc'] );
        if ( isset( $input['footer_social'] ) ) $sanitized['footer_social'] = wp_kses_post( $input['footer_social'] );
        if ( isset( $input['lead_form_lgpd_msg'] ) ) $sanitized['lead_form_lgpd_msg'] = wp_kses_post( $input['lead_form_lgpd_msg'] );
        if ( isset( $input['exit_intent_desc'] ) ) $sanitized['exit_intent_desc'] = wp_kses_post( $input['exit_intent_desc'] );
        if ( isset( $input['loja_catalog_desc'] ) ) $sanitized['loja_catalog_desc'] = wp_kses_post( $input['loja_catalog_desc'] );
        if ( isset( $input['loja_single_message'] ) ) $sanitized['loja_single_message'] = wp_kses_post( $input['loja_single_message'] );
        if ( isset( $input['custom_logo_dark'] ) ) $sanitized['custom_logo_dark'] = sanitize_url( $input['custom_logo_dark'] );
        if ( isset( $input['custom_thumbnail_fallback'] ) ) $sanitized['custom_thumbnail_fallback'] = sanitize_url( $input['custom_thumbnail_fallback'] );
        
        // Allowed raw code blocks
        $fields_code = ['head_scripts', 'body_scripts', 'footer_scripts', 'ad_block_content_1', 'ad_block_content_2', 'ad_block_content_3'];
        foreach($fields_code as $fc) {
            if ( isset( $input[$fc] ) ) $sanitized[$fc] = current_user_can('unfiltered_html') ? $input[$fc] : wp_kses_post( $input[$fc] );
        }

        // Detecta mudança na ativação da Loja para fazer flush de rewrite rules
        $old_options = get_option( 'fast_webx_options' );
        $old_enabled = isset( $old_options['loja_enabled'] ) ? (int) $old_options['loja_enabled'] : 0;
        $new_enabled = isset( $sanitized['loja_enabled'] ) ? (int) $sanitized['loja_enabled'] : 0;
        if ( $old_enabled !== $new_enabled ) {
            update_option( 'fwx_loja_needs_flush', 1 );
        }
        
        return $sanitized;
    }

    public function render_admin_page() {
        $options = get_option( 'fast_webx_options' );
        
        // Helper para valores
        $val = function($key, $default = '') use ($options) {
            return isset($options[$key]) ? $options[$key] : $default;
        };
        $checked = function($key, $default = 0) use ($options) {
            return (isset($options[$key]) ? $options[$key] : $default) == 1 ? 'checked' : '';
        };
        ?>
        <div class="wrap fwx-admin-wrap">
            <form method="post" action="options.php">
                <?php settings_fields( 'fast_webx_options_group' ); ?>
                
                <div class="fwx-header">
                    <h1>Fast WebX - Painel Pro <span style="font-size: 11px; vertical-align: middle; background: #2271b1; color: #fff; padding: 2px 8px; border-radius: 4px; margin-left: 10px; font-weight: 600;">v<?php echo FWX_VERSION; ?></span></h1>
                    <?php submit_button('Salvar Alterações', 'primary', 'submit_top', false); ?>
                </div>
                
                <div class="fwx-tabs">
                    <button type="button" class="fwx-tab-btn active" data-target="tab-design">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        Design
                    </button>
                    <button type="button" class="fwx-tab-btn" data-target="tab-lp">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        Home
                    </button>
                    <button type="button" class="fwx-tab-btn" data-target="tab-loja">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        Loja
                    </button>
                    <button type="button" class="fwx-tab-btn" data-target="tab-recursos">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        Conteúdo
                    </button>
                    <button type="button" class="fwx-tab-btn" data-target="tab-bio">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        Bio &amp; Autor
                    </button>
                    <button type="button" class="fwx-tab-btn" data-target="tab-ads">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                        Ads &amp; Leads
                    </button>
                    <button type="button" class="fwx-tab-btn" data-target="tab-performance">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                        Performance
                    </button>
                    <button type="button" class="fwx-tab-btn" data-target="tab-lgpd">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                        Rodapé &amp; Scripts
                    </button>
                    <button type="button" class="fwx-tab-btn" data-target="tab-core">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                        Core Clean
                    </button>
                    <button type="button" class="fwx-tab-btn" data-target="tab-seo">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        SEO
                    </button>
                    <button type="button" class="fwx-tab-btn" data-target="tab-extras">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        Extras
                    </button>
                </div>
                
                <div class="fwx-content-box">
                    
                    <!-- ABA DESIGN -->
                    <div id="tab-design" class="fwx-tab-content active">
                    <?php if ( fwx_is_active() ) : ?>
                        <h2 class="fwx-section-title">Cores Globais</h2>
                        <table class="form-table fwx-form-table fwx-cores-grid">
                            <tr>
                                <th><label>Cores da Marca (Primária / Secundária)</label></th>
                                <td>
                                    <input type="text" name="fast_webx_options[primary_color]" class="fwx-color-picker" value="<?php echo esc_attr($val('primary_color', '#0073aa')); ?>" />
                                    <input type="text" name="fast_webx_options[secondary_color]" class="fwx-color-picker" value="<?php echo esc_attr($val('secondary_color', '#005177')); ?>" />
                                    <p class="description">Define a cor principal (botões e links importantes) e a cor secundária (efeitos de hover e elementos de suporte).</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Cor do Texto (Claro/Escuro)</label></th>
                                <td>
                                    <input type="text" name="fast_webx_options[text_color]" class="fwx-color-picker" value="<?php echo esc_attr($val('text_color', '#222222')); ?>" />
                                    <input type="text" name="fast_webx_options[text_color_dark]" class="fwx-color-picker" value="<?php echo esc_attr($val('text_color_dark', '#dddddd')); ?>" />
                                    <p class="description">Define a cor principal do texto dos artigos e páginas para o Modo Claro e Modo Escuro. Padrão: #222222 e #dddddd.</p>
                                </td>
                            </tr>
                            <tr id="fwx_hero_bg_row">
                                <th><label>Fundo do Hero (Claro/Escuro)</label></th>
                                <td>
                                    <input type="text" name="fast_webx_options[hero_color]" class="fwx-color-picker" value="<?php echo esc_attr($val('hero_color', '#0073aa')); ?>" />
                                    <input type="text" name="fast_webx_options[hero_color_dark]" class="fwx-color-picker" value="<?php echo esc_attr($val('hero_color_dark', '#002233')); ?>" />
                                    <p class="description">Define a cor base do fundo das seções Hero no Modo Claro e no Modo Escuro.</p>
                                </td>
                            </tr>
                            <tr id="fwx_hero_text_row">
                                <th><label>Texto do Hero (Claro/Escuro)</label></th>
                                <td>
                                    <input type="text" name="fast_webx_options[hero_text_color]" class="fwx-color-picker" value="<?php echo esc_attr($val('hero_text_color', '#ffffff')); ?>" />
                                    <input type="text" name="fast_webx_options[hero_text_color_dark]" class="fwx-color-picker" value="<?php echo esc_attr($val('hero_text_color_dark', '#ffffff')); ?>" />
                                    <p class="description">Define a cor das fontes exibidas em cima do fundo Hero nos modos claro e escuro.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Cor Textos de Botões e Labels</label></th>
                                <td>
                                    <input type="text" name="fast_webx_options[cat_text_color]" class="fwx-color-picker" value="<?php echo esc_attr($val('cat_text_color', '')); ?>" data-default-color="#ffffff" />
                                    <p class="description">Customiza a cor de contraste das fontes dos botões e etiquetas de categoria para validação ideal no Lighthouse.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Cor de Links (Claro/Escuro)</label></th>
                                <td>
                                    <input type="text" name="fast_webx_options[link_color]" class="fwx-color-picker" value="<?php echo esc_attr($val('link_color', '#0073aa')); ?>" />
                                    <input type="text" name="fast_webx_options[link_color_dark]" class="fwx-color-picker" value="<?php echo esc_attr($val('link_color_dark', '#66b3cc')); ?>" />
                                    <p class="description">Define a cor dos hiperlinks internos inseridos dentro do corpo de textos em ambos os modos de leitura.</p>
                                </td>
                            </tr>
                        </table>

                        <h2 class="fwx-section-title">Tipografia e Visual</h2>
                        <p class="description" style="margin-bottom:20px;"><strong>Dica de Performance:</strong> O tema aceita arquivos <strong>.svg</strong> no logo e favicon, garantindo nitidez total e arquivos mais leves.</p>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Logo Modo Escuro</label></th>
                                <td>
                                    <input type="text" name="fast_webx_options[custom_logo_dark]" id="custom_logo_dark_input" class="regular-text" value="<?php echo esc_url($val('custom_logo_dark')); ?>" placeholder="Insira a URL ou clique em Enviar" />
                                    <button type="button" class="button fwx-upload-logo-btn">Enviar Imagem</button>
                                    <p class="description">Logo Claro e demais configurações são feitas em Aparência > Personalizar.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Cabeçalho Fixo (Sticky)</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[sticky_header]" value="1" <?php echo $checked('sticky_header', 0); ?> /> Congelar o Menu Topo ao descer a página.</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Ícones do Header</label></th>
                                <td>
                                    <label style="display:block;margin-bottom:10px;"><input type="checkbox" name="fast_webx_options[enable_header_search]" value="1" <?php echo $checked('enable_header_search', 0); ?> /> Exibir Ícone de Busca.</label>
                                    <label><input type="checkbox" name="fast_webx_options[enable_header_mode_toggle]" value="1" <?php echo $checked('enable_header_mode_toggle', 0); ?> /> Exibir Seletor de Modo (Claro/Escuro).</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Botão CTA (Header)</label></th>
                                <td>
                                    <label style="display:block;margin-bottom:10px;"><input type="checkbox" name="fast_webx_options[header_cta_enable]" value="1" <?php echo $checked('header_cta_enable', 0); ?> /> Exibir Botão de Destaque no Cabeçalho.</label>
                                    <div style="display:flex;gap:10px;">
                                        <input type="text" name="fast_webx_options[header_cta_text]" class="regular-text" style="width:150px;" value="<?php echo esc_attr($val('header_cta_text', 'Fale Conosco')); ?>" placeholder="Texto do Botão" />
                                        <input type="text" name="fast_webx_options[header_cta_url]" class="regular-text" style="flex:1;" value="<?php echo esc_attr($val('header_cta_url', '#')); ?>" placeholder="URL de Destino (ex: WhatsApp)" />
                                    </div>
                                    <p class="description">O botão será exibido ao lado do menu em Desktop.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Fontes do Tema</label></th>
                                <td>
                                    <select name="fast_webx_options[typography]">
                                        <option value="system" <?php selected($val('typography', 'system'), 'system'); ?>>Fontes de Sistema (Padrão - Ultraleve)</option>
                                        <option value="inter" <?php selected($val('typography'), 'inter'); ?>>Inter (Local - Moderno)</option>
                                        <option value="montserrat" <?php selected($val('typography'), 'montserrat'); ?>>Montserrat (Local - Elegante)</option>
                                        <option value="poppins" <?php selected($val('typography'), 'poppins'); ?>>Poppins (Local - Clean)</option>
                                        <option value="questrial" <?php selected($val('typography'), 'questrial'); ?>>Questrial (Local - Geométrica)</option>
                                        <option value="roboto" <?php selected($val('typography'), 'roboto'); ?>>Roboto (Local - Versátil)</option>
                                    </select>
                                    <p class="description">Todas as fontes são servidas localmente em formato .woff2 para máxima performance (100/100) e privacidade (LGPD).</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Layout de Posts (Blog / Global)</label></th>
                                <td>
                                    <select name="fast_webx_options[home_layout]">
                                        <option value="layout-a" <?php selected($val('home_layout', 'layout-a'), 'layout-a'); ?>>Layout A - Blog Clássico</option>
                                        <option value="layout-b" <?php selected($val('home_layout'), 'layout-b'); ?>>Layout B - Grid 3 Colunas Imagens</option>
                                        <option value="layout-c" <?php selected($val('home_layout'), 'layout-c'); ?>>Layout C - Híbrido (Destaque + Grid)</option>
                                        <option value="layout-d" <?php selected($val('home_layout'), 'layout-d'); ?>>Layout D - Destaque + 2 Colunas</option>
                                        <option value="layout-e" <?php selected($val('home_layout'), 'layout-e'); ?>>Layout E - Lista com Thumbs Grandes</option>
                                        <option value="layout-f" <?php selected($val('home_layout'), 'layout-f'); ?>>Layout F - Estilo News (Impacto)</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Layout de Categorias (Arquivos)</label></th>
                                <td>
                                    <select name="fast_webx_options[category_layout]">
                                        <option value="global" <?php selected($val('category_layout', 'global'), 'global'); ?>>Herdar Layout Global da Home</option>
                                        <option value="layout-a" <?php selected($val('category_layout'), 'layout-a'); ?>>Layout A - Blog Clássico</option>
                                        <option value="layout-b" <?php selected($val('category_layout'), 'layout-b'); ?>>Layout B - Grid 3 Colunas Imagens</option>
                                        <option value="layout-c" <?php selected($val('category_layout'), 'layout-c'); ?>>Layout C - Híbrido (Destaque + Grid)</option>
                                        <option value="layout-d" <?php selected($val('category_layout'), 'layout-d'); ?>>Layout D - Destaque + 2 Colunas</option>
                                        <option value="layout-e" <?php selected($val('category_layout'), 'layout-e'); ?>>Layout E - Lista com Thumbs Grandes</option>
                                        <option value="layout-f" <?php selected($val('category_layout'), 'layout-f'); ?>>Layout F - Estilo News (Impacto)</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Topo (Hero) de Categorias</label></th>
                                <td>
                                    <select id="fwx_hero_layout" name="fast_webx_options[hero_layout]">
                                        <option value="layout-1" <?php selected($val('hero_layout', 'layout-1'), 'layout-1'); ?>>Layout 1 (Caixa Colorida Centralizada)</option>
                                        <option value="layout-2" <?php selected($val('hero_layout'), 'layout-2'); ?>>Layout 2 (Editorial Minimalista a Esquerda)</option>
                                    </select>
                                    <p class="description">Nota: As cores de 'Fundo do Hero (Claro/Escuro)' (definidas na aba Design) só terão efeito se esta opção estiver configurada como 'Layout 1 (Caixa Colorida Centralizada)'.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Layout Post Individual (Single)</label></th>
                                <td>
                                    <select name="fast_webx_options[single_post_layout]">
                                        <option value="layout-1" <?php selected($val('single_post_layout', 'layout-1'), 'layout-1'); ?>>Layout 1 (Clássico no Grid)</option>
                                        <option value="layout-2" <?php selected($val('single_post_layout'), 'layout-2'); ?>>Layout 2 (Editorial Impacto / Imagem Larga)</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    <?php else : ?>
                        <?php fwx_render_lock_overlay(); ?>
                    <?php endif; ?>
                    </div>
                    
                    <!-- ABA LANDING PAGE (HOME) -->
                    <div id="tab-lp" class="fwx-tab-content">
                    <?php if ( fwx_is_active() ) : ?>
                        <h2 class="fwx-section-title">Configurações da Landing Page</h2>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Estilo Visual (Preset)</label></th>
                                <td>
                                    <select name="fast_webx_options[home_lp_type]">
                                        <option value="autor" <?php selected($val('home_lp_type', 'autor'), 'autor'); ?>>Autor</option>
                                        <option value="corporativo" <?php selected($val('home_lp_type'), 'corporativo'); ?>>Corporativo</option>
                                        <option value="editorial" <?php selected($val('home_lp_type'), 'editorial'); ?>>Editorial</option>
                                    </select>
                                    <p class="description">Altera o estilo do Hero e as cores base da Landing Page.</p>
                                </td>
                            </tr>

                            <tr>
                                <th><label>Seções Ativas (Modular)</label></th>
                                <td style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                    <label><input type="checkbox" name="fast_webx_options[home_show_hero]" value="1" <?php echo $checked('home_show_hero', 0); ?> /> Exibir Hero (Topo)</label>
                                    <label><input type="checkbox" name="fast_webx_options[home_show_about]" value="1" <?php echo $checked('home_show_about', 0); ?> /> Exibir Sobre / Bio</label>
                                    <label><input type="checkbox" name="fast_webx_options[home_show_services]" value="1" <?php echo $checked('home_show_services', 0); ?> /> Exibir Grade de Serviços</label>
                                    <label><input type="checkbox" name="fast_webx_options[home_show_steps]" value="1" <?php echo $checked('home_show_steps', 0); ?> /> Exibir Fluxo (3 Etapas)</label>
                                    <label><input type="checkbox" name="fast_webx_options[home_show_blog]" value="1" <?php echo $checked('home_show_blog', 0); ?> /> Exibir Feed do Blog</label>
                                    <label><input type="checkbox" name="fast_webx_options[home_show_capture]" value="1" <?php echo $checked('home_show_capture', 0); ?> /> Exibir Bloco de Captura</label>
                                    <label><input type="checkbox" name="fast_webx_options[home_show_testimonials]" value="1" <?php echo $checked('home_show_testimonials', 0); ?> /> Exibir Testemunhos</label>
                                    <label><input type="checkbox" name="fast_webx_options[home_show_contact]" value="1" <?php echo $checked('home_show_contact', 0); ?> /> Exibir Formulário de Contato</label>
                                </td>
                            </tr>
                        </table>

                        <h3 class="fwx-section-title" style="margin-top:20px;">Seção Hero (Topo)</h3>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Fundo do Hero (Claro/Escuro)</label></th>
                                <td>
                                    <input type="text" name="fast_webx_options[home_hero_bg_color]" class="fwx-color-picker" value="<?php echo esc_attr($val('home_hero_bg_color', '')); ?>" />
                                    <input type="text" name="fast_webx_options[home_hero_bg_color_dark]" class="fwx-color-picker" value="<?php echo esc_attr($val('home_hero_bg_color_dark', '')); ?>" />
                                    <p class="description">Deixe em branco para herdar as cores globais.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Texto do Hero (Claro/Escuro)</label></th>
                                <td>
                                    <input type="text" name="fast_webx_options[home_hero_text_color]" class="fwx-color-picker" value="<?php echo esc_attr($val('home_hero_text_color', '')); ?>" />
                                    <input type="text" name="fast_webx_options[home_hero_text_color_dark]" class="fwx-color-picker" value="<?php echo esc_attr($val('home_hero_text_color_dark', '')); ?>" />
                                </td>
                            </tr>
                            <tr>
                                <th><label>Título Principal</label></th>
                                <td><input type="text" name="fast_webx_options[home_hero_title]" class="regular-text fwx-input-large" value="<?php echo esc_attr($val('home_hero_title')); ?>" /></td>
                            </tr>
                            <tr>
                                <th><label>Descrição/Subtítulo</label></th>
                                <td><textarea name="fast_webx_options[home_hero_desc]" rows="3" class="large-text fwx-input-large"><?php echo esc_textarea($val('home_hero_desc')); ?></textarea></td>
                            </tr>
                            <tr>
                                <th><label>Texto do Botão CTA</label></th>
                                <td><input type="text" name="fast_webx_options[home_hero_btn_text]" class="regular-text" value="<?php echo esc_attr($val('home_hero_btn_text')); ?>" /></td>
                            </tr>
                            <tr>
                                <th><label>URL do Botão CTA</label></th>
                                <td><input type="url" name="fast_webx_options[home_hero_btn_url]" class="regular-text" value="<?php echo esc_url($val('home_hero_btn_url')); ?>" /></td>
                            </tr>
                            <tr>
                                <th><label>Tipo de Mídia da Hero</label></th>
                                <td>
                                    <select name="fast_webx_options[home_hero_media_type]" id="home_hero_media_type_select">
                                        <option value="image" <?php selected($val('home_hero_media_type', 'image'), 'image'); ?>>Imagem de Destaque</option>
                                        <option value="video" <?php selected($val('home_hero_media_type', 'image'), 'video'); ?>>Vídeo de Destaque</option>
                                    </select>
                                    <p class="description">Selecione se deseja exibir uma imagem estática ou um vídeo na Hero da Landing Page.</p>
                                </td>
                            </tr>
                            <tr id="fwx_hero_image_row">
                                <th><label>Imagem de Destaque</label></th>
                                <td>
                                    <input type="text" name="fast_webx_options[home_hero_img]" id="home_hero_img_input" class="regular-text" value="<?php echo esc_url($val('home_hero_img')); ?>" />
                                    <button type="button" class="button fwx-upload-img-btn" data-target="home_hero_img_input">Enviar Imagem</button>
                                    <p class="description">Usada como imagem principal ou como imagem estática de fallback/thumbnail para vídeos do YouTube/Vimeo antes do clique.</p>
                                </td>
                            </tr>
                            <tr id="fwx_hero_video_row">
                                <th><label>Vídeo de Destaque (URL)</label></th>
                                <td>
                                    <input type="text" name="fast_webx_options[home_hero_video]" id="home_hero_video_input" class="regular-text fwx-input-large" value="<?php echo esc_url($val('home_hero_video')); ?>" placeholder="Ex: https://www.youtube.com/watch?v=... ou link de arquivo .mp4" />
                                    <button type="button" class="button fwx-upload-video-btn" data-target="home_hero_video_input">Enviar Vídeo</button>
                                    <p class="description">Suporta links do YouTube, Vimeo ou arquivos de vídeo locais (.mp4, .webm, .ogg).</p>
                                </td>
                            </tr>
                        </table>

                        <h3 class="fwx-section-title" style="margin-top:20px;">Seção Sobre / Bio</h3>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Título da Seção</label></th>
                                <td><input type="text" name="fast_webx_options[home_about_title]" class="regular-text" value="<?php echo esc_attr($val('home_about_title', 'Sobre Nós')); ?>" /></td>
                            </tr>
                            <tr>
                                <th><label>Conteúdo da Bio</label></th>
                                <td><?php wp_editor($val('home_about_text'), 'home_about_text_editor', array('textarea_name' => 'fast_webx_options[home_about_text]', 'textarea_rows' => 8)); ?></td>
                            </tr>
                        </table>

                        <h3 class="fwx-section-title" style="margin-top:20px;">Seção de Serviços</h3>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Título da Seção</label></th>
                                <td><input type="text" name="fast_webx_options[home_services_title]" class="regular-text" value="<?php echo esc_attr($val('home_services_title', 'Nossos Serviços')); ?>" /></td>
                            </tr>
                            <tr>
                                <th><label>Subtítulo da Seção</label></th>
                                <td><input type="text" name="fast_webx_options[home_services_desc]" class="regular-text fwx-input-large" placeholder="Texto descritivo abaixo do título" value="<?php echo esc_attr($val('home_services_desc')); ?>" /></td>
                            </tr>
                            <tr>
                                <th><label>Quantidade de Serviços</label></th>
                                <td>
                                    <select name="fast_webx_options[home_services_count]">
                                        <option value="3" <?php selected($val('home_services_count', '3'), '3'); ?>>Exibir 3 Serviços</option>
                                        <option value="6" <?php selected($val('home_services_count', '3'), '6'); ?>>Exibir 6 Serviços</option>
                                    </select>
                                    <p class="description">Defina quantos serviços/produtos ativos serão renderizados no frontend (3 ou 6).</p>
                                </td>
                            </tr>
                            <?php for($i=1; $i<=6; $i++): ?>
                            <tr>
                                <th><label>Serviço <?php echo $i; ?></label></th>
                                <td>
                                    <input type="text" name="fast_webx_options[home_service_title_<?php echo $i; ?>]" class="regular-text" placeholder="Título" value="<?php echo esc_attr($val('home_service_title_'.$i)); ?>" style="width:28%;" />
                                    <input type="text" name="fast_webx_options[home_service_desc_<?php echo $i; ?>]" class="regular-text" placeholder="Breve descrição" value="<?php echo esc_attr($val('home_service_desc_'.$i)); ?>" style="width:28%;" />
                                    <input type="url" name="fast_webx_options[home_service_link_<?php echo $i; ?>]" class="regular-text" placeholder="https://link-do-botao.com" value="<?php echo esc_attr($val('home_service_link_'.$i)); ?>" style="width:28%;" />
                                    <label style="display:inline-flex; align-items:center; gap:4px; margin-left:5px; font-weight:600; font-size:12px;">
                                        <input type="checkbox" name="fast_webx_options[home_service_new_tab_<?php echo $i; ?>]" value="1" <?php echo $checked('home_service_new_tab_'.$i, 0); ?> />
                                        Nova guia
                                    </label>
                                </td>
                            </tr>
                            <?php endfor; ?>
                        </table>

                        <h3 class="fwx-section-title" style="margin-top:20px;">Seção Fluxo (3 Etapas)</h3>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Título do Fluxo</label></th>
                                <td><input type="text" name="fast_webx_options[home_steps_title]" class="regular-text" value="<?php echo esc_attr($val('home_steps_title', 'Como Funciona')); ?>" /></td>
                            </tr>
                            <tr>
                                <th><label>Subtítulo do Fluxo</label></th>
                                <td><input type="text" name="fast_webx_options[home_steps_desc]" class="regular-text fwx-input-large" placeholder="Texto descritivo abaixo do título" value="<?php echo esc_attr($val('home_steps_desc')); ?>" /></td>
                            </tr>
                            <?php for($i=1; $i<=3; $i++): ?>
                            <tr>
                                <th><label>Etapa <?php echo $i; ?></label></th>
                                <td>
                                    <input type="text" name="fast_webx_options[home_step_<?php echo $i; ?>_title]" class="regular-text" placeholder="Título" value="<?php echo esc_attr($val('home_step_'.$i.'_title')); ?>" style="width:48%;" />
                                    <input type="text" name="fast_webx_options[home_step_<?php echo $i; ?>_desc]" class="regular-text" placeholder="Breve descrição" value="<?php echo esc_attr($val('home_step_'.$i.'_desc')); ?>" style="width:48%;" />
                                </td>
                            </tr>
                            <?php endfor; ?>
                        </table>

                        <h3 class="fwx-section-title" style="margin-top:20px;">Seção de Captura (Lead Magnet)</h3>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Título de Chamada</label></th>
                                <td><input type="text" name="fast_webx_options[home_capture_title]" class="regular-text fwx-input-large" value="<?php echo esc_attr($val('home_capture_title')); ?>" /></td>
                            </tr>
                            <tr>
                                <th><label>Descrição</label></th>
                                <td><textarea name="fast_webx_options[home_capture_desc]" rows="2" class="large-text fwx-input-large"><?php echo esc_textarea($val('home_capture_desc')); ?></textarea></td>
                            </tr>
                            <tr>
                                <th><label>Shortcode do Form</label></th>
                                <td><input type="text" name="fast_webx_options[home_capture_shortcode]" class="regular-text" placeholder="[fwx_lead_form]" value="<?php echo esc_attr($val('home_capture_shortcode')); ?>" /></td>
                            </tr>
                        </table>

                        <h3 class="fwx-section-title" style="margin-top:20px;">Testemunhos (Social Proof)</h3>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Título da Seção</label></th>
                                <td><input type="text" name="fast_webx_options[home_testimonials_title]" class="regular-text" value="<?php echo esc_attr($val('home_testimonials_title', 'O que dizem nossos clientes')); ?>" /></td>
                            </tr>
                            <tr>
                                <th><label>Subtítulo da Seção</label></th>
                                <td><input type="text" name="fast_webx_options[home_testimonials_desc]" class="regular-text" value="<?php echo esc_attr($val('home_testimonials_desc', '')); ?>" placeholder="Deixe em branco para ocultar" /></td>
                            </tr>
                            <tr>
                                <th><label>Quantidade de Testemunhos</label></th>
                                <td>
                                    <select name="fast_webx_options[home_testimonials_count]">
                                        <option value="3" <?php selected($val('home_testimonials_count', '3'), '3'); ?>>Exibir 3 Testemunhos</option>
                                        <option value="6" <?php selected($val('home_testimonials_count', '3'), '6'); ?>>Exibir 6 Testemunhos</option>
                                    </select>
                                    <p class="description">Defina quantos depoimentos ativos serão renderizados no frontend (3 ou 6).</p>
                                </td>
                            </tr>
                            <?php for($i=1; $i<=6; $i++): ?>
                            <tr>
                                <th><label>Depoimento <?php echo $i; ?></label></th>
                                <td>
                                    <textarea name="fast_webx_options[home_testimonial_<?php echo $i; ?>_text]" rows="2" style="width:100%; margin-bottom:5px;" placeholder="Texto do depoimento"><?php echo esc_textarea($val('home_testimonial_'.$i.'_text')); ?></textarea>
                                    <input type="text" name="fast_webx_options[home_testimonial_<?php echo $i; ?>_author]" class="regular-text" placeholder="Nome do Autor / Cargo" value="<?php echo esc_attr($val('home_testimonial_'.$i.'_author')); ?>" style="width:100%; margin-bottom:5px;" />
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <input type="text" name="fast_webx_options[home_testimonial_<?php echo $i; ?>_img]" id="home_testimonial_<?php echo $i; ?>_img_input" class="regular-text" placeholder="URL da foto de perfil (opcional)" value="<?php echo esc_url($val('home_testimonial_'.$i.'_img')); ?>" />
                                        <button type="button" class="button fwx-upload-img-btn" data-target="home_testimonial_<?php echo $i; ?>_img_input">Selecionar Foto</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endfor; ?>
                        </table>

                        <h3 class="fwx-section-title" style="margin-top:20px;">Feed do Blog & Contato</h3>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Título do Blog</label></th>
                                <td><input type="text" name="fast_webx_options[home_blog_title]" class="regular-text" value="<?php echo esc_attr($val('home_blog_title', 'Últimas do Blog')); ?>" /></td>
                            </tr>
                            <tr>
                                <th><label>Subtítulo do Blog</label></th>
                                <td><input type="text" name="fast_webx_options[home_blog_desc]" class="regular-text" value="<?php echo esc_attr($val('home_blog_desc', '')); ?>" placeholder="Deixe em branco para ocultar" /></td>
                            </tr>
                            <tr>
                                <th><label>Título de Contato</label></th>
                                <td><input type="text" name="fast_webx_options[home_contact_title]" class="regular-text" value="<?php echo esc_attr($val('home_contact_title', 'Fale Conosco')); ?>" /></td>
                            </tr>
                            <tr>
                                <th><label>Descrição de Contato</label></th>
                                <td><input type="text" name="fast_webx_options[home_contact_desc]" class="regular-text fwx-input-large" value="<?php echo esc_attr($val('home_contact_desc')); ?>" /></td>
                            </tr>
                            <tr>
                                <th><label>Conteúdo / Shortcode de Contato</label></th>
                                <td>
                                    <textarea name="fast_webx_options[home_contact_content]" rows="4" class="large-text fwx-input-large" placeholder="[fwx_contact_form]"><?php echo esc_textarea($val('home_contact_content', '[fwx_contact_form]')); ?></textarea>
                                    <p class="description">Insira o código HTML, texto ou Shortcode personalizado de contato (ex: <code>[fwx_contact_form]</code>, <code>[contact-form-7 ...]</code> ou HTML livre).</p>
                                </td>
                            </tr>
                        </table>
                    <?php else : ?>
                        <?php fwx_render_lock_overlay(); ?>
                    <?php endif; ?>
                    </div>

                    <!-- ABA LOJA -->
                    <div id="tab-loja" class="fwx-tab-content">
                    <?php if ( fwx_is_active() ) : ?>
                        <h2 class="fwx-section-title">🛒 Loja — Catálogo de Produtos</h2>
                        <p class="description" style="margin-bottom:20px;">Configure a aparência e comportamento do catálogo <strong>fwx_produto</strong>. Acesse os produtos em <a href="edit.php?post_type=fwx_produto" style="color:#2271b1;">WP Admin &rsaquo; Loja</a>.</p>

                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Ativar Loja</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[loja_enabled]" value="1" <?php echo $checked('loja_enabled', 0); ?> /> <strong>Ativar módulo Loja</strong> (CPT, catálogo e vitrine de produtos).</label>
                                    <p class="description">Se desativado, nenhuma funcionalidade da loja será carregada (CPT, taxonomias, shortcode, meta boxes e CSS dedicado).</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="loja_catalog_title">Título do Catálogo</label></th>
                                <td>
                                    <input type="text" id="loja_catalog_title" name="fast_webx_options[loja_catalog_title]" class="regular-text" value="<?php echo esc_attr($val('loja_catalog_title', 'Nossa Loja')); ?>" placeholder="Nossa Loja" />
                                    <p class="description">Título exibido no topo do catálogo em <code>/loja/</code>.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="loja_catalog_desc">Descrição do Catálogo</label></th>
                                <td>
                                    <textarea id="loja_catalog_desc" name="fast_webx_options[loja_catalog_desc]" rows="3" class="large-text fwx-input-large" placeholder="Breve descrição da loja..."><?php echo esc_textarea($val('loja_catalog_desc')); ?></textarea>
                                    <p class="description">Descrição exibida no topo do catálogo em <code>/loja/</code>, abaixo do título.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="loja_cta_text">Texto do Botão CTA</label></th>
                                <td>
                                    <input type="text" id="loja_cta_text" name="fast_webx_options[loja_cta_text]" class="regular-text" value="<?php echo esc_attr($val('loja_cta_text', 'Ver Produto')); ?>" placeholder="Ver Produto" />
                                    <p class="description">Texto do botão em cada card de produto.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="loja_single_message">Mensagem abaixo do CTA</label></th>
                                <td>
                                    <textarea id="loja_single_message" name="fast_webx_options[loja_single_message]" rows="2" class="large-text fwx-input-large" placeholder="Mensagem discreta com suporte a links..."><?php echo esc_textarea($val('loja_single_message')); ?></textarea>
                                    <p class="description">Texto discreto exibido logo abaixo do botão CTA no detalhe do produto. Suporta HTML básico (como links).</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="loja_columns">Colunas do Grid</label></th>
                                <td>
                                    <select id="loja_columns" name="fast_webx_options[loja_columns]" class="regular-text">
                                        <option value="2" <?php selected($val('loja_columns', '3'), '2'); ?>>2 colunas</option>
                                        <option value="3" <?php selected($val('loja_columns', '3'), '3'); ?>>3 colunas (padrão)</option>
                                        <option value="4" <?php selected($val('loja_columns', '3'), '4'); ?>>4 colunas</option>
                                    </select>
                                    <p class="description">Número de colunas no catálogo. Em mobile é sempre responsivo (1-2 colunas).</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Exibir Preço nos Cards</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[loja_show_price]" value="1" <?php echo $checked('loja_show_price', 0); ?> /> Mostrar o campo de preço nos cards e na página do produto.</label>
                                    <p class="description">Desmarque se preferir ocultar preços (ex: portfólio, catálogo sem preço).</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Produtos Relacionados</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[loja_enable_related]" value="1" <?php echo $checked('loja_enable_related', 0); ?> /> Exibir seção de produtos relacionados na página de detalhes do produto.</label>
                                    <p class="description">Mostra uma grade com até 3 produtos da mesma categoria no rodapé do produto.</p>
                                </td>
                            </tr>
                        </table>
                    <?php else : ?>
                        <?php fwx_render_lock_overlay(); ?>
                    <?php endif; ?>
                    </div>

                    <!-- ABA RECURSOS -->
                    <div id="tab-recursos" class="fwx-tab-content">
                    <?php if ( fwx_is_active() ) : ?>
                        <h2 class="fwx-section-title">Controle Granular de Layout</h2>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Imagem de Destaque</label></th>
                                <td>
                                    <label style="display:block;margin-bottom:10px;"><input type="checkbox" name="fast_webx_options[hide_single_thumb]" value="1" <?php echo $checked('hide_single_thumb', 0); ?> /> Ocultar Thumbnail no topo do Artigo (Post).</label>
                                    <label><input type="checkbox" name="fast_webx_options[hide_page_thumb]" value="1" <?php echo $checked('hide_page_thumb', 0); ?> /> Ocultar Thumbnail na Página (Page).</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Fallback de Miniatura (Thumbnail)</label></th>
                                <td>
                                    <select name="fast_webx_options[thumbnail_fallback_type]" id="thumbnail_fallback_type" style="margin-bottom: 10px; display: block;">
                                        <option value="default" <?php selected($val('thumbnail_fallback_type', 'default'), 'default'); ?>>Imagem Padrão do Tema (default-thumb.webp)</option>
                                        <option value="custom" <?php selected($val('thumbnail_fallback_type'), 'custom'); ?>>Imagem Personalizada (Upload abaixo)</option>
                                        <option value="none" <?php selected($val('thumbnail_fallback_type'), 'none'); ?>>Sem Imagem (Ocultar miniatura se não houver destaque)</option>
                                    </select>
                                    
                                    <div id="fwx_custom_fallback_wrapper" style="margin-top: 10px; display: <?php echo $val('thumbnail_fallback_type', 'default') === 'custom' ? 'block' : 'none'; ?>;">
                                        <input type="text" name="fast_webx_options[custom_thumbnail_fallback]" id="custom_thumbnail_fallback_input" class="regular-text" value="<?php echo esc_url($val('custom_thumbnail_fallback')); ?>" placeholder="URL da Imagem de Fallback" style="max-width: 350px;" />
                                        <button type="button" class="button fwx-upload-img-btn" data-target="custom_thumbnail_fallback_input">Selecionar Imagem</button>
                                        <p class="description">Faça o upload ou selecione a imagem da biblioteca para usar como fallback.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Resumo (Excerpt)</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[show_single_excerpt]" value="1" <?php echo $checked('show_single_excerpt', 0); ?> /> Exibir resumo (chamada) logo abaixo do título no Post Individual.</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Breadcrumbs (Migalha de Pão)</label></th>
                                <td>
                                    <label style="display:block;margin-bottom:10px;"><input type="checkbox" name="fast_webx_options[show_single_breadcrumb]" value="1" <?php echo $checked('show_single_breadcrumb', 0); ?> /> Exibir Breadcrumbs nos Artigos (Posts).</label>
                                    <label><input type="checkbox" name="fast_webx_options[show_page_breadcrumb]" value="1" <?php echo $checked('show_page_breadcrumb', 0); ?> /> Exibir Breadcrumbs nas Páginas (Pages).</label>
                                </td>
                            </tr>

                            <tr>
                                <th><label>Índice de Conteúdo (TOC)</label></th>
                                <td>
                                    <label style="display:block;margin-bottom:10px;"><input type="checkbox" name="fast_webx_options[enable_toc]" value="1" <?php echo $checked('enable_toc', 0); ?> /> Gerar Tabela (Sumário) automaticamente no topo dos posts.</label>
                                    <label style="display:block;margin-bottom:10px;"><input type="checkbox" name="fast_webx_options[toc_start_collapsed]" value="1" <?php echo $checked('toc_start_collapsed', 0); ?> /> Exibir o sumário já <strong>recolhido</strong> ao carregar a página (o leitor pode expandir manualmente).</label>
                                    <label><input type="checkbox" name="fast_webx_options[enable_sticky_toc]" value="1" <?php echo $checked('enable_sticky_toc', 0); ?> /> Em Desktop, move o sumário para uma coluna lateral esquerda flutuante que acompanha a página.</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Caixa do Autor (E-E-A-T)</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[enable_author_box]" value="1" <?php echo $checked('enable_author_box', 0); ?> /> Exibir a foto de perfil, bio e nome do autor na base do artigo.</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Tags do Artigo</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[show_single_tags]" value="1" <?php echo $checked('show_single_tags', 0); ?> /> Exibir as tags/etiquetas do artigo no final do conteúdo do post individual.</label>
                                </td>
                            </tr>

                            <tr>
                                <th><label>Botões de Compartilhamento</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[show_sharing_buttons]" value="1" <?php echo $checked('show_sharing_buttons', 0); ?> /> Exibir botões de redes sociais (WhatsApp, Face, etc) no início do post.</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Lightbox Nativo</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[enable_lightbox]" value="1" <?php echo $checked('enable_lightbox', 0); ?> /> Habilitar Lightbox (clique para ampliar imagem) no conteúdo de Artigos/Posts.</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Barra de Tempo de Leitura</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[enable_reading_progress]" value="1" <?php echo $checked('enable_reading_progress', 0); ?> /> Exibir a "Linha do Tempo" preenchendo no topo superior durante o scroll.</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Leitor de Áudio (TTS)</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[enable_tts]" value="1" <?php echo $checked('enable_tts', 0); ?> /> Habilitar Leitor de Texto em Áudio nativo (Web Speech API) no topo dos Artigos.</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Botão Voltar ao Topo</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[enable_back_to_top]" value="1" <?php echo $checked('enable_back_to_top', 0); ?> /> Exibir o botão flutuante "Voltar ao Topo" (Back to Top).</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Posts Relacionados (Fim do Artigo)</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[enable_related_posts]" value="1" <?php echo $checked('enable_related_posts', 0); ?> /> Exibir seção de posts relacionados no final dos artigos (mesma categoria).</label>
                                </td>
                            </tr>
                        </table>

                        <h3 class="fwx-section-title" style="margin-top:30px;">Sidebar & Widgets Dinâmicos</h3>
                        <?php $loja_enabled = fwx_get_option( 'loja_enabled', 0 ); ?>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Sidebar Inteligente</label></th>
                                <td>
                                    <label style="display:block;margin-bottom:10px;"><input type="checkbox" name="fast_webx_options[sidebar_posts]" value="1" <?php echo $checked('sidebar_posts', 0); ?> /> Exibir Sidebar em Artigos (Posts)</label>
                                    <label style="display:block;margin-bottom:10px;"><input type="checkbox" name="fast_webx_options[sidebar_pages]" value="1" <?php echo $checked('sidebar_pages', 0); ?> /> Exibir Sidebar em Páginas (Pages)</label>
                                    <label style="display:block;margin-bottom:10px;"><input type="checkbox" name="fast_webx_options[sidebar_archive]" value="1" <?php echo $checked('sidebar_archive', 0); ?> /> Exibir Sidebar nas Listagens (Categorias, Tags, Arquivos &mdash; Layouts A a F)</label>
                                    <label style="display:block;margin-bottom:10px; <?php if ( ! $loja_enabled ) echo 'opacity:0.35; pointer-events:none;'; ?>"><input type="checkbox" name="fast_webx_options[sidebar_loja]" value="1" <?php echo $checked('sidebar_loja', 0); ?> <?php disabled( $loja_enabled, 0 ); ?> /> Exibir Sidebar no Catálogo da Loja (<code>/loja/</code>)</label>
                                    <label style="display:block;margin-bottom:10px; <?php if ( ! $loja_enabled ) echo 'opacity:0.35; pointer-events:none;'; ?>"><input type="checkbox" name="fast_webx_options[sidebar_loja_single]" value="1" <?php echo $checked('sidebar_loja_single', 0); ?> <?php disabled( $loja_enabled, 0 ); ?> /> Exibir Sidebar na Página do Produto (single <code>fwx_produto</code>)</label>
                                    <label style="display:block;"><input type="checkbox" name="fast_webx_options[hide_sidebar_mobile]" value="1" <?php echo $checked('hide_sidebar_mobile', 0); ?> /> Ocultar Sidebar automaticamente em dispositivos móveis (Celulares).</label>
                                    <p class="description">Quando ativada, a grade de posts ocupa a área principal e a sidebar aparece ao lado direito.</p>
                                </td>
                            </tr>
                                <th><label>Widgets Dinâmicos (Sidebar)</label></th>
                                <td>
                                    <?php
                                    $sidebar_posts_active       = fwx_get_option( 'sidebar_posts', 0 );
                                    $sidebar_loja_single_active = fwx_get_option( 'sidebar_loja_single', 0 );
                                    $sidebar_archive_active     = fwx_get_option( 'sidebar_archive', 0 );
                                    $sidebar_loja_active        = fwx_get_option( 'sidebar_loja', 0 );

                                    $posts_style        = ! $sidebar_posts_active ? 'opacity:0.35; pointer-events:none;' : '';
                                    $loja_single_style  = ( ! $loja_enabled || ! $sidebar_loja_single_active ) ? 'opacity:0.35; pointer-events:none;' : '';
                                    $archive_style      = ! $sidebar_archive_active ? 'opacity:0.35; pointer-events:none;' : '';
                                    $loja_archive_style = ( ! $loja_enabled || ! $sidebar_loja_active ) ? 'opacity:0.35; pointer-events:none;' : '';
                                    ?>
                                    <!-- Widget Autor -->
                                    <label style="display:block;margin-bottom:5px;"><input type="checkbox" name="fast_webx_options[enable_sidebar_author]" value="1" <?php echo $checked('enable_sidebar_author', 0); ?> /> <strong>Widget do Autor:</strong> Exibir bloco E-E-A-T com foto de perfil e Bio no topo.</label>
                                    <div class="fwx-widget-display-conds" style="margin-left: 20px; margin-bottom: 15px; background: #fafafa; padding: 8px 12px; border-radius: 4px; border: 1px dashed #ccc; display: inline-block;">
                                        <span style="font-size: 11px; color: #666; font-weight: 600; display: inline-block; margin-right: 10px;">Exibir em:</span>
                                        <label style="margin-right: 10px; font-size: 12px; <?php echo $posts_style; ?>"><input type="checkbox" name="fast_webx_options[author_widget_in_single_post]" value="1" <?php echo $checked('author_widget_in_single_post', 0); ?> /> Artigo Blog (Single)</label>
                                        <label style="margin-right: 10px; font-size: 12px; <?php if ( ! $loja_enabled ) { echo 'opacity:0.35; pointer-events:none;'; } else { echo $loja_single_style; } ?>"><input type="checkbox" name="fast_webx_options[author_widget_in_single_product]" value="1" <?php echo $checked('author_widget_in_single_product', 0); ?> <?php disabled( $loja_enabled, 0 ); ?> /> Produto Loja (Single)</label>
                                        <label style="margin-right: 10px; font-size: 12px; <?php echo $archive_style; ?>"><input type="checkbox" name="fast_webx_options[author_widget_in_archive_post]" value="1" <?php echo $checked('author_widget_in_archive_post', 0); ?> /> Arquivos Blog</label>
                                        <label style="font-size: 12px; <?php if ( ! $loja_enabled ) { echo 'opacity:0.35; pointer-events:none;'; } else { echo $loja_archive_style; } ?>"><input type="checkbox" name="fast_webx_options[author_widget_in_archive_product]" value="1" <?php echo $checked('author_widget_in_archive_product', 0); ?> <?php disabled( $loja_enabled, 0 ); ?> /> Arquivos Loja</label>
                                    </div>
 
                                    <!-- Widget Leads -->
                                    <label style="display:block;margin-bottom:5px;"><input type="checkbox" name="fast_webx_options[enable_sidebar_lead_widget]" value="1" <?php echo $checked('enable_sidebar_lead_widget', 0); ?> /> <strong>Widget de Leads:</strong> Injetar formulário automático da Isca Digital.</label>
                                    <div class="fwx-widget-display-conds" style="margin-left: 20px; margin-bottom: 15px; background: #fafafa; padding: 8px 12px; border-radius: 4px; border: 1px dashed #ccc; display: inline-block;">
                                        <span style="font-size: 11px; color: #666; font-weight: 600; display: inline-block; margin-right: 10px;">Exibir em:</span>
                                        <label style="margin-right: 10px; font-size: 12px; <?php echo $posts_style; ?>"><input type="checkbox" name="fast_webx_options[lead_widget_in_single_post]" value="1" <?php echo $checked('lead_widget_in_single_post', 0); ?> /> Artigo Blog (Single)</label>
                                        <label style="margin-right: 10px; font-size: 12px; <?php if ( ! $loja_enabled ) { echo 'opacity:0.35; pointer-events:none;'; } else { echo $loja_single_style; } ?>"><input type="checkbox" name="fast_webx_options[lead_widget_in_single_product]" value="1" <?php echo $checked('lead_widget_in_single_product', 0); ?> <?php disabled( $loja_enabled, 0 ); ?> /> Produto Loja (Single)</label>
                                        <label style="margin-right: 10px; font-size: 12px; <?php echo $archive_style; ?>"><input type="checkbox" name="fast_webx_options[lead_widget_in_archive_post]" value="1" <?php echo $checked('lead_widget_in_archive_post', 0); ?> /> Arquivos Blog</label>
                                        <label style="font-size: 12px; <?php if ( ! $loja_enabled ) { echo 'opacity:0.35; pointer-events:none;'; } else { echo $loja_archive_style; } ?>"><input type="checkbox" name="fast_webx_options[lead_widget_in_archive_product]" value="1" <?php echo $checked('lead_widget_in_archive_product', 0); ?> <?php disabled( $loja_enabled, 0 ); ?> /> Arquivos Loja</label>
                                    </div>
 
                                    <!-- Widget Relacionados -->
                                    <label style="display:block;margin-bottom:5px;"><input type="checkbox" name="fast_webx_options[enable_sidebar_related]" value="1" <?php echo $checked('enable_sidebar_related', 0); ?> /> <strong>Widget Posts Recentes:</strong> Exibir grade de posts recomendados na sidebar.</label>
                                    <div class="fwx-widget-display-conds" style="margin-left: 20px; margin-bottom: 15px; background: #fafafa; padding: 8px 12px; border-radius: 4px; border: 1px dashed #ccc; display: inline-block;">
                                        <span style="font-size: 11px; color: #666; font-weight: 600; display: inline-block; margin-right: 10px;">Exibir em:</span>
                                        <label style="margin-right: 10px; font-size: 12px; <?php echo $posts_style; ?>"><input type="checkbox" name="fast_webx_options[related_widget_in_single_post]" value="1" <?php echo $checked('related_widget_in_single_post', 0); ?> /> Artigo Blog (Single)</label>
                                        <label style="margin-right: 10px; font-size: 12px; <?php if ( ! $loja_enabled ) { echo 'opacity:0.35; pointer-events:none;'; } else { echo $loja_single_style; } ?>"><input type="checkbox" name="fast_webx_options[related_widget_in_single_product]" value="1" <?php echo $checked('related_widget_in_single_product', 0); ?> <?php disabled( $loja_enabled, 0 ); ?> /> Produto Loja (Single)</label>
                                        <label style="margin-right: 10px; font-size: 12px; <?php echo $archive_style; ?>"><input type="checkbox" name="fast_webx_options[related_widget_in_archive_post]" value="1" <?php echo $checked('related_widget_in_archive_post', 0); ?> /> Arquivos Blog</label>
                                        <label style="font-size: 12px; <?php if ( ! $loja_enabled ) { echo 'opacity:0.35; pointer-events:none;'; } else { echo $loja_archive_style; } ?>"><input type="checkbox" name="fast_webx_options[related_widget_in_archive_product]" value="1" <?php echo $checked('related_widget_in_archive_product', 0); ?> <?php disabled( $loja_enabled, 0 ); ?> /> Arquivos Loja</label>
                                    </div>
 
                                    <!-- Widget Produtos Recentes (Loja) -->
                                    <div style="<?php if ( ! $loja_enabled ) echo 'opacity:0.35; pointer-events:none;'; ?>">
                                        <label style="display:block;margin-bottom:5px;"><input type="checkbox" name="fast_webx_options[loja_enable_sidebar_recent]" value="1" <?php echo $checked('loja_enable_sidebar_recent', 0); ?> <?php disabled( $loja_enabled, 0 ); ?> /> <strong>Widget Produtos Recentes:</strong> Exibir widget de produtos recentes na barra lateral.</label>
                                        <div class="fwx-widget-display-conds" style="margin-left: 20px; margin-bottom: 15px; background: #fafafa; padding: 8px 12px; border-radius: 4px; border: 1px dashed #ccc; display: inline-block;">
                                            <span style="font-size: 11px; color: #666; font-weight: 600; display: inline-block; margin-right: 10px;">Exibir em:</span>
                                            <label style="margin-right: 10px; font-size: 12px; <?php echo $posts_style; ?>"><input type="checkbox" name="fast_webx_options[recent_products_widget_in_single_post]" value="1" <?php echo $checked('recent_products_widget_in_single_post', 0); ?> <?php disabled( $loja_enabled, 0 ); ?> /> Artigo Blog (Single)</label>
                                            <label style="margin-right: 10px; font-size: 12px; <?php echo $loja_single_style; ?>"><input type="checkbox" name="fast_webx_options[recent_products_widget_in_single_product]" value="1" <?php echo $checked('recent_products_widget_in_single_product', 0); ?> <?php disabled( $loja_enabled, 0 ); ?> /> Produto Loja (Single)</label>
                                            <label style="margin-right: 10px; font-size: 12px; <?php echo $archive_style; ?>"><input type="checkbox" name="fast_webx_options[recent_products_widget_in_archive_post]" value="1" <?php echo $checked('recent_products_widget_in_archive_post', 0); ?> <?php disabled( $loja_enabled, 0 ); ?> /> Arquivos Blog</label>
                                            <label style="font-size: 12px; <?php echo $loja_archive_style; ?>"><input type="checkbox" name="fast_webx_options[recent_products_widget_in_archive_product]" value="1" <?php echo $checked('recent_products_widget_in_archive_product', 0); ?> <?php disabled( $loja_enabled, 0 ); ?> /> Arquivos Loja</label>
                                        </div>
                                    </div>
 
                                    <!-- Widget Categorias -->
                                    <label style="display:block;margin-bottom:5px;"><input type="checkbox" name="fast_webx_options[enable_sidebar_categories]" value="1" <?php echo $checked('enable_sidebar_categories', 0); ?> /> <strong>Widget de Categorias:</strong> Exibir as 5 categorias com mais artigos.</label>
                                    <div class="fwx-widget-display-conds" style="margin-left: 20px; margin-bottom: 15px; background: #fafafa; padding: 8px 12px; border-radius: 4px; border: 1px dashed #ccc; display: inline-block;">
                                        <span style="font-size: 11px; color: #666; font-weight: 600; display: inline-block; margin-right: 10px;">Exibir em:</span>
                                        <label style="margin-right: 10px; font-size: 12px; <?php echo $posts_style; ?>"><input type="checkbox" name="fast_webx_options[categories_widget_in_single_post]" value="1" <?php echo $checked('categories_widget_in_single_post', 0); ?> /> Artigo Blog (Single)</label>
                                        <label style="margin-right: 10px; font-size: 12px; <?php if ( ! $loja_enabled ) { echo 'opacity:0.35; pointer-events:none;'; } else { echo $loja_single_style; } ?>"><input type="checkbox" name="fast_webx_options[categories_widget_in_single_product]" value="1" <?php echo $checked('categories_widget_in_single_product', 0); ?> <?php disabled( $loja_enabled, 0 ); ?> /> Produto Loja (Single)</label>
                                        <label style="margin-right: 10px; font-size: 12px; <?php echo $archive_style; ?>"><input type="checkbox" name="fast_webx_options[categories_widget_in_archive_post]" value="1" <?php echo $checked('categories_widget_in_archive_post', 0); ?> /> Arquivos Blog</label>
                                        <label style="font-size: 12px; <?php if ( ! $loja_enabled ) { echo 'opacity:0.35; pointer-events:none;'; } else { echo $loja_archive_style; } ?>"><input type="checkbox" name="fast_webx_options[categories_widget_in_archive_product]" value="1" <?php echo $checked('categories_widget_in_archive_product', 0); ?> <?php disabled( $loja_enabled, 0 ); ?> /> Arquivos Loja</label>
                                    </div>

                                    <label style="display:block;margin-bottom:15px;"><input type="checkbox" name="fast_webx_options[sticky_sidebar_widget]" value="1" <?php echo $checked('sticky_sidebar_widget', 0); ?> /> <strong>Efeito Sticky:</strong> Fixar o último item da barra lateral ao rolar a página para baixo.</label>
                                    
                                    <div style="background: rgba(0,0,0,0.02); padding: 15px; border-left: 3px solid var(--fwx-primary); margin-top: 10px;">
                                        <label style="display:block;margin-bottom:5px;font-size:0.95em;color:#555;font-weight:600;">Qual widget será o último? (Alvo do Efeito Sticky)</label>
                                        <select name="fast_webx_options[sidebar_last_widget]" style="width:100%;max-width:300px;margin-bottom:5px;">
                                            <option value="wp_widgets" <?php selected($val('sidebar_last_widget', 'wp_widgets'), 'wp_widgets'); ?>>Widgets Padrão do WordPress</option>
                                            <option value="author" <?php selected($val('sidebar_last_widget', 'wp_widgets'), 'author'); ?>>Widget do Autor</option>
                                            <option value="lead" <?php selected($val('sidebar_last_widget', 'wp_widgets'), 'lead'); ?>>Formulário de Leads</option>
                                            <option value="related" <?php selected($val('sidebar_last_widget', 'wp_widgets'), 'related'); ?>>Posts Relacionados</option>
                                            <option value="categories" <?php selected($val('sidebar_last_widget', 'wp_widgets'), 'categories'); ?>>Widget de Categorias</option>
                                            <option value="loja_recent" <?php selected($val('sidebar_last_widget', 'wp_widgets'), 'loja_recent'); ?> <?php disabled( $loja_enabled, 0 ); ?>>Produtos Recentes (Loja)</option>
                                        </select>
                                        <p class="description" style="margin:0;">Como os widgets são injetados sozinhos, esta opção define a ordem deles, escolhendo qual ficará por último para receber a propriedade Sticky (se ativada).</p>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <h3 class="fwx-section-title" style="margin-top:20px;">Refinamentos de Leitura</h3>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Espaçamento de Links</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[enable_link_margin]" value="1" <?php echo $checked('enable_link_margin', 0); ?> /> Adicionar margem inferior automática em links dentro do texto.</label>
                                    <p class="description">Aplica um leve distanciamento vertical nos links do conteúdo, facilitando a leitura e o clique em listas ou botões manuais.</p>
                                </td>
                            </tr>
                        </table>
                    <?php else : ?>
                        <?php fwx_render_lock_overlay(); ?>
                    <?php endif; ?>
                    </div>

                    <!-- ABA BIO -->
                    <div id="tab-bio" class="fwx-tab-content">
                    <?php if ( fwx_is_active() ) : ?>
                        <h2 class="fwx-section-title">Redes Sociais Oficiais</h2>
                        <p class="description" style="margin-bottom:15px;">Estes links exibirão ícones correspondentes no Widget de Autor e na Página de Bio.</p>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Facebook URL</label></th>
                                <td><input type="url" name="fast_webx_options[social_facebook]" class="regular-text" value="<?php echo esc_url($val('social_facebook')); ?>" placeholder="https://facebook.com/perfil" /></td>
                            </tr>
                            <tr>
                                <th><label>Instagram URL</label></th>
                                <td><input type="url" name="fast_webx_options[social_instagram]" class="regular-text" value="<?php echo esc_url($val('social_instagram')); ?>" placeholder="https://instagram.com/perfil" /></td>
                            </tr>
                            <tr>
                                <th><label>YouTube URL</label></th>
                                <td><input type="url" name="fast_webx_options[social_youtube]" class="regular-text" value="<?php echo esc_url($val('social_youtube')); ?>" placeholder="https://youtube.com/@canal" /></td>
                            </tr>
                            <tr>
                                <th><label>TikTok URL</label></th>
                                <td><input type="url" name="fast_webx_options[social_tiktok]" class="regular-text" value="<?php echo esc_url($val('social_tiktok')); ?>" placeholder="https://tiktok.com/@user" /></td>
                            </tr>
                            <tr>
                                <th><label>LinkedIn URL</label></th>
                                <td><input type="url" name="fast_webx_options[social_linkedin]" class="regular-text" value="<?php echo esc_url($val('social_linkedin')); ?>" placeholder="https://linkedin.com/in/nome" /></td>
                            </tr>
                            <tr>
                                <th><label>Twitter / X URL</label></th>
                                <td><input type="url" name="fast_webx_options[social_twitter]" class="regular-text" value="<?php echo esc_url($val('social_twitter')); ?>" placeholder="https://twitter.com/user" /></td>
                            </tr>
                            <tr>
                                <th><label>Pinterest URL</label></th>
                                <td><input type="url" name="fast_webx_options[social_pinterest]" class="regular-text" value="<?php echo esc_url($val('social_pinterest')); ?>" placeholder="https://pinterest.com/perfil" /></td>
                            </tr>
                            <tr>
                                <th><label>WhatsApp URL</label></th>
                                <td>
                                    <input type="url" name="fast_webx_options[social_whatsapp]" class="regular-text" value="<?php echo esc_url($val('social_whatsapp')); ?>" placeholder="https://wa.me/5511999999999" />
                                    <p class="description">Exemplo de link direto: <code>https://wa.me/5511999999999</code> (insira o link com DDI + DDD + número).</p>
                                </td>
                            </tr>
                        </table>

                        <h2 class="fwx-section-title" style="margin-top:20px;">Página Link In Bio [fwx_link_in_bio]</h2>
                        <p class="description" style="margin-bottom:15px;">Configure o perfil e os botões personalizados que aparecerão na sua página de Bio.</p>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Perfil a ser Exibido</label></th>
                                <td>
                                    <select name="fast_webx_options[bio_user_id]" class="regular-text fwx-input-large">
                                        <option value="">-- Padrão (Autor do Post / Admin) --</option>
                                        <?php 
                                        $all_users = get_users( array( 'role__in' => array( 'administrator', 'editor', 'author' ) ) );
                                        $current_bio_user = $val('bio_user_id');
                                        foreach( $all_users as $u ) {
                                            $selected = selected( $current_bio_user, $u->ID, false );
                                            echo '<option value="' . esc_attr($u->ID) . '" ' . $selected . '>' . esc_html($u->display_name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <p class="description">Selecione de qual usuário o sistema deve puxar a foto, o nome e a biografia para a página Link in Bio e bloco na LP.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Nome Customizado</label></th>
                                <td>
                                    <input type="text" name="fast_webx_options[bio_custom_name]" class="regular-text fwx-input-large" value="<?php echo esc_attr($val('bio_custom_name')); ?>" placeholder="Deixe vazio para usar o nome do perfil selecionado" />
                                </td>
                            </tr>
                            <tr>
                                <th><label>Biografia Customizada</label></th>
                                <td>
                                    <textarea name="fast_webx_options[bio_custom_text]" rows="3" class="large-text fwx-input-large" placeholder="Deixe vazio para usar a biografia do perfil selecionado"><?php echo esc_textarea($val('bio_custom_text')); ?></textarea>
                                </td>
                            </tr>
                        </table>

                        <h2 class="fwx-section-title" style="margin-top:20px;">Botões e Rodapé (Link In Bio)</h2>
                        <table class="form-table fwx-form-table">
                            <?php for($i=1; $i<=5; $i++): ?>
                            <tr>
                                <th><label>Link Personalizado <?php echo $i; ?></label></th>
                                <td>
                                    <input type="text" name="fast_webx_options[bio_link_text_<?php echo $i; ?>]" class="regular-text" placeholder="Texto do Botão" value="<?php echo esc_attr($val('bio_link_text_' . $i)); ?>" style="width:48%;" />
                                    <input type="url" name="fast_webx_options[bio_link_url_<?php echo $i; ?>]" class="regular-text" placeholder="https://url-de-destino.com" value="<?php echo esc_url($val('bio_link_url_' . $i)); ?>" style="width:48%;" />
                                </td>
                            </tr>
                            <?php endfor; ?>
                            <tr>
                                <th><label>Botão em Destaque</label></th>
                                <td>
                                    <select name="fast_webx_options[bio_highlight_id]" class="regular-text fwx-input-large">
                                        <option value="">-- Nenhum destaque --</option>
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php selected($val('bio_highlight_id'), $i); ?>>Botão <?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <p class="description">O botão selecionado receberá a cor primária do tema para ganhar maior evidência visual.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Texto do Rodapé (Bio)</label></th>
                                <td>
                                    <input type="text" name="fast_webx_options[bio_footer_text]" class="regular-text fwx-input-large" value="<?php echo esc_attr($val('bio_footer_text')); ?>" placeholder="Ex: Criado com Amor por Fast WebX" />
                                    <p class="description">Este texto aparecerá discretamente abaixo de todos os botões na página de Bio.</p>
                                </td>
                            </tr>
                        </table>
                    <?php else : ?>
                        <?php fwx_render_lock_overlay(); ?>
                    <?php endif; ?>
                    </div>

                    <!-- ABA RODAPÉ, LGPD & SCRIPTS -->
                    <div id="tab-lgpd" class="fwx-tab-content">
                    <?php if ( fwx_is_active() ) : ?>
                        <h2 class="fwx-section-title">Rodapé</h2>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Conteúdo 'Sobre' (Coluna 1)</label></th>
                                <td>
                                    <textarea name="fast_webx_options[footer_desc]" rows="3" class="large-text fwx-input-large" placeholder="Ex: <strong>[nome_site]</strong>..."><?php echo esc_textarea($val('footer_desc', '<strong>[nome_site]</strong><br>[fwx_site_desc]')); ?></textarea>
                                    <p class="description">Dica: Use <code>&lt;strong&gt;[nome_site]&lt;/strong&gt;&lt;br&gt;[fwx_site_desc]</code> para o padrão dinâmico. Aceita Shortcodes e HTML.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Conteúdo 'Acompanhe' (Coluna 4)</label></th>
                                <td>
                                    <textarea name="fast_webx_options[footer_social]" rows="3" class="large-text fwx-input-large" placeholder="Ex: [fwx_social_links]"><?php echo esc_textarea($val('footer_social', '[fwx_social_links]')); ?></textarea>
                                    <p class="description">Dica: Use <code>[fwx_social_links]</code> para carregar os ícones configurados no tema. Aceita Shortcodes e HTML.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Texto Auxiliar (Base Direita)</label></th>
                                <td>
                                    <input type="text" name="fast_webx_options[footer_aux_text]" class="regular-text fwx-input-large" value="<?php echo esc_attr($val('footer_aux_text')); ?>" placeholder="Ex: Built for readers who want signal, not noise." />
                                </td>
                            </tr>
                            <tr>
                                <th><label>Mensagem de Copyright</label></th>
                                <td>
                                    <textarea name="fast_webx_options[footer_text]" rows="2" class="large-text fwx-input-large"><?php echo esc_textarea($val('footer_text', '© [ano] [nome_site]. Todos os direitos reservados.')); ?></textarea>
                                    <p class="description">Tags permitidas: [ano], [nome_site]. Aceita Shortcodes.</p>
                                </td>
                            </tr>
                        </table>

                        <h2 class="fwx-section-title" style="margin-top:20px;">LGPD &amp; Privacidade</h2>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Banner LGPD (Texto)</label></th>
                                <td>
                                    <textarea name="fast_webx_options[lgpd_text]" rows="3" class="large-text fwx-input-large"><?php echo esc_textarea($val('lgpd_text', 'Nós usamos cookies para melhorar sua experiência diária.')); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th><label>URL Políticas de Privacidade</label></th>
                                <td>
                                    <input type="text" name="fast_webx_options[lgpd_policy_url]" class="regular-text fwx-input-large" value="<?php echo esc_url($val('lgpd_policy_url')); ?>" placeholder="https://seudominio.com/politicas" />
                                    <p class="description">Forneça o Link da sua política para surgir o botão auxiliar na LGPD.</p>
                                </td>
                            </tr>
                        </table>

                        <h2 class="fwx-section-title" style="margin-top:20px;">Injeção de Scripts (Pixel &amp; Analytics)</h2>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Stealth Mode</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[stealth_mode]" value="1" <?php echo $checked('stealth_mode', 0); ?> /> <strong style="color:#d63638;">Ativar Modo Furtivo.</strong> (Atrasar execução até o primeiro scroll. TBT = 0ms)</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Scripts no Cabeçalho</label></th>
                                <td>
                                    <textarea name="fast_webx_options[head_scripts]" rows="3" class="large-text code fwx-input-large" placeholder="&lt;script&gt;..."><?php echo esc_textarea($val('head_scripts')); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Scripts no Body (Abertura)</label></th>
                                <td>
                                    <textarea name="fast_webx_options[body_scripts]" rows="3" class="large-text code fwx-input-large" placeholder="&lt;noscript&gt;..."><?php echo esc_textarea($val('body_scripts')); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Scripts no Rodapé</label></th>
                                <td>
                                    <textarea name="fast_webx_options[footer_scripts]" rows="3" class="large-text code fwx-input-large"><?php echo esc_textarea($val('footer_scripts')); ?></textarea>
                                </td>
                            </tr>
                        </table>
                    <?php else : ?>
                        <?php fwx_render_lock_overlay(); ?>
                    <?php endif; ?>
                    </div>

                    <!-- ABA ADS & SCRIPTS -->
                    <div id="tab-ads" class="fwx-tab-content">
                    <?php if ( fwx_is_active() ) : ?>
<?php
$ad_options = [
    'none' => 'Desligado (Não injetar)',
    'before_everything' => 'Topo Absoluto (Antes de Título/Thumb)',
    'before_content' => 'Início do Post (Antes do Conteúdo)',
    'after_toc' => 'Após o Sumário (Dentro do Artigo)',
    'after_p_1' => 'Após 1º Parágrafo',
    'after_p_3' => 'Após 3º Parágrafo',
    'after_p_5' => 'Após 5º Parágrafo',
    'after_p_7' => 'Após 7º Parágrafo',
    'after_p_9' => 'Após 9º Parágrafo',
    'after_p_11' => 'Após 11º Parágrafo',
    'after_p_13' => 'Após 13º Parágrafo',
    'after_p_15' => 'Após 15º Parágrafo',
    'after_p_17' => 'Após 17º Parágrafo',
    'after_p_19' => 'Após 19º Parágrafo',
    'before_h2_1' => 'Antes do 1º H2 (Subtítulo)',
    'before_h2_2' => 'Antes do 2º H2',
    'before_h2_3' => 'Antes do 3º H2',
    'before_h2_4' => 'Antes do 4º H2',
    'before_h2_5' => 'Antes do 5º H2',
    'before_h2_6' => 'Antes do 6º H2',
    'before_h2_7' => 'Antes do 7º H2',
    'after_content' => 'Final do Post (Fim do Artigo)',
];
?>
                        <h2 class="fwx-section-title">Gestor de Blocos Automáticos (Ads & Leads)</h2>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Ocultar em Produtos</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[disable_ads_loja_single]" value="1" <?php echo $checked('disable_ads_loja_single', 0); ?> /> Não injetar anúncios/capturas nas páginas de produtos da Loja (single-fwx_produto).</label>
                                    <p class="description">Marque esta opção para suprimir a exibição automática de anúncios e formulários nos detalhes de produtos.</p>
                                </td>
                            </tr>
                            <!-- BLOCO 1 -->
                            <tr>
                                <th><label>Bloco 1: Conteúdo/Shortcode</label></th>
                                <td>
                                    <textarea name="fast_webx_options[ad_block_content_1]" rows="2" class="large-text code fwx-input-large" placeholder="Ex: [fwx_lead_form] ou código AdSense..."><?php echo esc_textarea($val('ad_block_content_1')); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Bloco 1: Posição de Injeção</label></th>
                                <td>
                                    <select name="fast_webx_options[ad_injection_point_1]" style="margin-bottom: 20px;">
                                        <?php foreach($ad_options as $k => $v) : ?>
                                            <option value="<?php echo esc_attr($k); ?>" <?php selected($val('ad_injection_point_1'), $k); ?>><?php echo esc_html($v); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>

                            <!-- BLOCO 2 -->
                            <tr>
                                <th><label>Bloco 2: Conteúdo/Shortcode</label></th>
                                <td>
                                    <textarea name="fast_webx_options[ad_block_content_2]" rows="2" class="large-text code fwx-input-large"><?php echo esc_textarea($val('ad_block_content_2')); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Bloco 2: Posição de Injeção</label></th>
                                <td>
                                    <select name="fast_webx_options[ad_injection_point_2]" style="margin-bottom: 20px;">
                                        <?php foreach($ad_options as $k => $v) : ?>
                                            <option value="<?php echo esc_attr($k); ?>" <?php selected($val('ad_injection_point_2'), $k); ?>><?php echo esc_html($v); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>

                            <!-- BLOCO 3 -->
                            <tr>
                                <th><label>Bloco 3: Conteúdo/Shortcode</label></th>
                                <td>
                                    <textarea name="fast_webx_options[ad_block_content_3]" rows="2" class="large-text code fwx-input-large"><?php echo esc_textarea($val('ad_block_content_3')); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Bloco 3: Posição de Injeção</label></th>
                                <td>
                                    <select name="fast_webx_options[ad_injection_point_3]">
                                        <?php foreach($ad_options as $k => $v) : ?>
                                            <option value="<?php echo esc_attr($k); ?>" <?php selected($val('ad_injection_point_3'), $k); ?>><?php echo esc_html($v); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        </table>

                        <h2 class="fwx-section-title" style="margin-top:20px;">Configurações de Captura [fwx_lead_form]</h2>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Capturar WhatsApp</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[enable_whatsapp_capture]" value="1" <?php echo $checked('enable_whatsapp_capture', 0); ?> /> Habilitar campo de WhatsApp nos formulários de captura.</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Somente WhatsApp</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[lead_email_optional]" value="1" <?php echo $checked('lead_email_optional', 0); ?> /> <strong>Ocultar campo de e-mail</strong> (O formulário exibirá apenas o campo de WhatsApp).</label>
                                    <p class="description">Nota: Esta opção só tem efeito se "Capturar WhatsApp" estiver ativo.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Título da Isca</label></th>
                                <td>
                                    <input type="text" name="fast_webx_options[lead_form_title]" class="regular-text fwx-input-large" value="<?php echo esc_attr($val('lead_form_title', 'Cadastre-se na nossa Newsletter')); ?>" />
                                </td>
                            </tr>
                            <tr>
                                <th><label>Subtítulo / Descrição</label></th>
                                <td>
                                    <input type="text" name="fast_webx_options[lead_form_desc]" class="regular-text fwx-input-large" value="<?php echo esc_attr($val('lead_form_desc', 'Receba as melhores dicas e atualizações semanais gratuitamente.')); ?>" />
                                </td>
                            </tr>
                            <tr>
                                <th><label>Mensagem LGPD (Consentimento)</label></th>
                                <td>
                                    <textarea name="fast_webx_options[lead_form_lgpd_msg]" rows="2" class="large-text code fwx-input-large"><?php echo esc_textarea($val('lead_form_lgpd_msg', 'Eu concordo com a Política de Privacidade e aceito receber comunicações (LGPD).')); ?></textarea>
                                    <p class="description">Você pode usar HTML básico aqui (como &lt;a&gt; para linkar as políticas). O checkbox é obrigatório nativamente.</p>
                                </td>
                            </tr>
                        </table>

                        <h2 class="fwx-section-title" style="margin-top:20px;">Integrações e Contato</h2>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Formulário de Contato Nativo</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[enable_contact_form]" value="1" <?php echo $checked('enable_contact_form', 0); ?> /> Habilitar o processamento do shortcode <code>[fwx_contact_form]</code>.</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Webhook URL (Automação)</label></th>
                                <td>
                                    <input type="url" name="fast_webx_options[webhook_url]" class="regular-text fwx-input-large" value="<?php echo esc_url($val('webhook_url')); ?>" placeholder="https://hook.us1.make.com/..." />
                                    <p class="description">URL que receberá um POST com JSON contendo dados do Lead ou Contato (Integração Make, n8n, Zapier).</p>
                                </td>
                            </tr>

                            <tr>
                                <th><label>Página de Obrigado (Redirecionamento)</label></th>
                                <td>
                                    <input type="url" name="fast_webx_options[lead_redirect_url]" class="regular-text fwx-input-large" value="<?php echo esc_url($val('lead_redirect_url')); ?>" placeholder="https://seusite.com/obrigado/" />
                                    <p class="description">Após o cadastro bem-sucedido, o usuário será redirecionado para esta URL. Se vazio, exibe mensagem de sucesso no lugar. Ideal para páginas de obrigado e funis de conversão.</p>
                                </td>
                            </tr>

                            <tr>
                                <th><label>Exit Intent Popup (Captura)</label></th>
                                <td>
                                    <label style="display:block;margin-bottom:10px;"><input type="checkbox" name="fast_webx_options[enable_exit_intent]" value="1" <?php echo $checked('enable_exit_intent', 0); ?> /> Ativar Popup de Saída (Desktop apenas).</label>
                                    <select name="fast_webx_options[exit_intent_frequency]" id="exit_intent_frequency" style="display:block;margin-bottom:10px;max-width:300px;">
                                        <option value="always" <?php selected($val('exit_intent_frequency', 'always'), 'always'); ?>>Sempre (a cada saída/recarga)</option>
                                        <option value="session" <?php selected($val('exit_intent_frequency'), 'session'); ?>>Uma vez por sessão</option>
                                        <option value="1m" <?php selected($val('exit_intent_frequency'), '1m'); ?>>1 Minuto (para testes)</option>
                                        <option value="1" <?php selected($val('exit_intent_frequency'), '1'); ?>>1 Dia (a cada 24h)</option>
                                        <option value="7" <?php selected($val('exit_intent_frequency'), '7'); ?>>1 Semana (a cada 7 dias)</option>
                                        <option value="30" <?php selected($val('exit_intent_frequency'), '30'); ?>>1 Mês (a cada 30 dias)</option>
                                        <option value="permanent" <?php selected($val('exit_intent_frequency'), 'permanent'); ?>>Exibir apenas uma vez (Permanente)</option>
                                    </select>
                                    <input type="text" name="fast_webx_options[exit_intent_title]" class="regular-text fwx-input-large" value="<?php echo esc_attr($val('exit_intent_title', 'Espere! Não vá embora ainda...')); ?>" placeholder="Título do Popup" style="display:block;margin-bottom:10px;" />
                                    <textarea name="fast_webx_options[exit_intent_desc]" rows="2" class="large-text fwx-input-large" placeholder="Descrição curta..." style="display:block;margin-bottom:10px;"><?php echo esc_textarea($val('exit_intent_desc', 'Temos um presente especial para você antes de sair.')); ?></textarea>
                                    <p class="description">Suporta HTML e Shortcodes. (Ex: Use <code>[fwx_lead_form]</code> para exibir o formulário de captura).</p>
                                </td>
                            </tr>
                        </table>
                    <?php else : ?>
                        <?php fwx_render_lock_overlay(); ?>
                    <?php endif; ?>
                    </div>

                    <!-- ABA DESEMPENHO -->
                    <div id="tab-performance" class="fwx-tab-content">
                    <?php if ( fwx_is_active() ) : ?>
                        <h2 class="fwx-section-title">Otimizações de Velocidade</h2>
                        <p style="color:#666;margin-bottom:20px;">Desative recursos nativos do WordPress que não são necessários para o seu site e que impactam negativamente no Lighthouse.</p>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Emojis do WordPress</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[perf_disable_emojis]" value="1" <?php echo $checked('perf_disable_emojis', 0); ?> /> Desativar scripts de emojis (já desativado por padrão neste tema).</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Gravatar (Avaliação de Autores)</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[perf_disable_gravatar]" value="1" <?php echo $checked('perf_disable_gravatar', 0); ?> /> <strong style="color:#d63638;">Desativar Gravatar.</strong> Remove requisições externas de avatares. (Substitui por placeholder local)</label>
                                </td>
                            </tr>

                            <tr>
                                <th><label>RSS Feeds</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[perf_disable_rss]" value="1" <?php echo $checked('perf_disable_rss', 0); ?> /> Redirecionar feeds RSS para a página inicial (se não usar RSS).</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Tamanhos de Imagem Extra</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[perf_disable_extra_image_sizes]" value="1" <?php echo $checked('perf_disable_extra_image_sizes', 0); ?> /> Bloquear geração de tamanhos extras de imagem do WP (thumbnail, medium_large, etc). Usar apenas os tamanhos do Fast WebX.</label>
                                    <p class="description">⚠️ Recomendado. Após ativar, use "Otimizar Mídias" para regenerar o banco de imagens.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Conversão WebP no Upload</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[perf_auto_webp_upload]" value="1" <?php echo $checked('perf_auto_webp_upload', 0); ?> /> <strong style="color:#0073aa;">Converter automaticamente JPEG e PNG para WebP</strong> em todo novo upload de imagem.</label>
                                    <p class="description">⚡ Recomendado. Aplica a conversão no momento do upload sem precisar rodar o otimizador em lote. Requer que o servidor suporte a extensão GD ou Imagick com WebP. Imagens já existentes precisam ser regeneradas via "Otimizar Mídias".</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Otimização Extrema: Transients</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[perf_widget_transients]" value="1" <?php echo $checked('perf_widget_transients', 0); ?> /> <strong style="color:#0073aa;">Ativar Cache em RAM (Recomendado).</strong> Salva Blocos de Recentes no Cache Local por 12 horas.</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>YouTube Fast/Lazy Load</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[perf_lazy_youtube]" value="1" <?php echo $checked('perf_lazy_youtube', 0); ?> /> <strong style="color:#0073aa;">Ativar Carregamento por Clique.</strong> Substitui scripts do YouTube por imagem estática até o clique no player.</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Heartbeat API</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[perf_disable_heartbeat]" value="1" <?php echo $checked('perf_disable_heartbeat', 0); ?> /> Desativar o Heartbeat da API do WP no front-end (reduz requisições AJAX desnecessárias).</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Sistema de Comentários</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[disable_comments]" value="1" <?php echo $checked('disable_comments', 0); ?> /> <strong style="color:#d63638;">Desativar Comentários.</strong> Remover a área de discussão do fim dos artigos (Ganho de processamento e Queries SQL).</label>
                                </td>
                            </tr>
                        </table>
                    <?php else : ?>
                        <?php fwx_render_lock_overlay(); ?>
                    <?php endif; ?>
                    </div>

                    <!-- ABA CORE CLEAN (MANUTENÇÃO) -->
                    <div id="tab-core" class="fwx-tab-content">
                    <?php if ( fwx_is_active() ) : ?>
                        <h2 class="fwx-section-title">Manutenção & Ferramentas Core</h2>
                        <p style="color:#666;margin-bottom:20px;">Utilize estas ferramentas para garantir que o seu banco de dados e caches estejam sempre otimizados.</p>
                        
                        <div class="fwx-core-tools-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom:30px;">
                            <div class="fwx-core-tool-card" style="background:#fff; padding:20px; border-radius:10px; border:1px solid #ddd;">
                                <h3 style="margin-top:0;">Limpar Transients</h3>
                                <p style="font-size:0.9rem;">Remove todos os caches temporários de widgets e queries do tema.</p>
                                <p style="font-size:0.75rem; color:#888; margin-bottom:15px;">Última execução: <?php echo get_option('fwx_last_transients_clear', 'Nunca'); ?></p>
                                <button type="submit" name="fwx_core_action" value="clear_transients" class="button button-secondary">Limpar Transients Agora</button>
                            </div>
                            
                            <div class="fwx-core-tool-card" style="background:#fff; padding:20px; border-radius:10px; border:1px solid #ddd;">
                                <h3 style="margin-top:0;">Otimizar Core</h3>
                                <p style="font-size:0.9rem;">Executa uma limpeza leve em opções órfãs e dados temporários.</p>
                                <p style="font-size:0.75rem; color:#888; margin-bottom:15px;">Última execução: <?php echo get_option('fwx_last_core_optimize', 'Nunca'); ?></p>
                                <button type="submit" name="fwx_core_action" value="optimize_db" class="button button-secondary">Otimizar Core</button>
                            </div>

                            <div class="fwx-core-tool-card" style="background:#fff; padding:20px; border-radius:10px; border:1px solid #ddd;">
                                <h3 style="margin-top:0;">Logs de Mídia</h3>
                                <p style="font-size:0.9rem;">Status das últimas otimizações de imagem.</p>
                                <div class="fwx-log-box" style="background:#f9f9f9;padding:10px;font-size:0.75rem;border-radius:5px;max-height:80px;overflow-y:auto;border:1px solid #eee;margin-bottom:10px;">
                                    <?php 
                                    $logs = get_option('fwx_media_logs', 'Sem logs recentes.'); 
                                    echo nl2br(esc_html($logs));
                                    ?>
                                </div>
                                <button type="submit" name="fwx_core_action" value="clear_media_logs" class="button button-secondary" style="color:#d63638; border-color:rgba(214, 54, 56, 0.3); padding: 0 15px;">Limpar Histórico</button>
                            </div>
                        </div>


                        <h3 class="fwx-section-title" style="margin-top:40px;">Segurança &amp; Limpeza Core (Clean)</h3>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Application Passwords</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[disable_app_passwords]" value="1" <?php echo $checked('disable_app_passwords', 0); ?> /> Desativar "Senhas de Aplicativos" (Application Passwords) para maior segurança.</label>
                                    <p class="description">⚠️ <strong>Cuidado:</strong> Se você usa automações externas (Make, Zapier, n8n) que se conectam ao WP via API usando senhas específicas, desativar isso impedirá a conexão.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>REST API (Limpeza)</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[clean_rest_api]" value="1" <?php echo $checked('clean_rest_api', 0); ?> /> Limpar endpoints não essenciais da REST API para visitantes (Users, Types, etc).</label>
                                    <p class="description">🛡️ Remove dados públicos da API (como lista de usuários expostos). <strong>Não quebra o editor Gutenberg</strong>, mas pode afetar plugins de terceiros que dependem da API no front-end para visitantes.</p>
                                </td>
                            </tr>
                        </table>

                    <?php else : ?>
                        <?php fwx_render_lock_overlay(); ?>
                    <?php endif; ?>
                    </div>

                    <!-- ABA SEO -->
                    <div id="tab-seo" class="fwx-tab-content">
                    <?php if ( fwx_is_active() ) : ?>

                        <?php
                        // Detecta plugins de SEO externos ativos
                        $seo_plugin_active = '';
                        if ( class_exists( 'WPSEO_Options' ) ) {
                            $seo_plugin_active = 'Yoast SEO';
                        } elseif ( defined( 'RANK_MATH_VERSION' ) || function_exists( 'rank_math' ) ) {
                            $seo_plugin_active = 'Rank Math SEO';
                        } elseif ( defined( 'SEOPRESS_VERSION' ) || function_exists( 'seopress_activation' ) ) {
                            $seo_plugin_active = 'SEOPress';
                        } elseif ( class_exists( 'AIOSEO' ) || defined( 'AIOSEO_VERSION' ) ) {
                            $seo_plugin_active = 'All in One SEO';
                        } elseif ( function_exists( 'the_seo_framework' ) ) {
                            $seo_plugin_active = 'The SEO Framework';
                        }
                        ?>

                        <?php if ( ! empty( $seo_plugin_active ) ) : ?>
                        <div class="notice notice-warning inline" style="margin: 0 0 20px 0; padding: 12px 16px; border-left-color: #f0b429;">
                            <p><strong>⚠️ Plugin SEO externo detectado: <?php echo esc_html( $seo_plugin_active ); ?></strong></p>
                            <p style="margin-top:6px;">O tema Fast WebX detectou que o <strong><?php echo esc_html( $seo_plugin_active ); ?></strong> está ativo. Por isso, as funções de <strong>Meta Description automática do tema estão desativadas</strong> para evitar duplicidade de tags.</p>
                            <p style="margin-top:6px;">Configure as meta descriptions diretamente no plugin <strong><?php echo esc_html( $seo_plugin_active ); ?></strong>. Os campos abaixo (Meta Description Home e Automação de Descrições) <strong>não terão efeito</strong> enquanto o plugin estiver ativo.</p>
                        </div>
                        <?php endif; ?>

                        <?php if ( ! empty( $seo_plugin_active ) ) : ?>
                        <style>
                        .fwx-seo-plugin-blocked {
                            position: relative;
                            opacity: 0.45;
                            pointer-events: none;
                            user-select: none;
                        }
                        .fwx-seo-plugin-blocked::after {
                            content: '';
                            position: absolute;
                            inset: 0;
                            z-index: 10;
                            cursor: not-allowed;
                            background: transparent;
                            pointer-events: all;
                        }
                        .fwx-seo-plugin-blocked input,
                        .fwx-seo-plugin-blocked textarea,
                        .fwx-seo-plugin-blocked select,
                        .fwx-seo-plugin-blocked label,
                        .fwx-seo-plugin-blocked button {
                            cursor: not-allowed !important;
                        }
                        </style>
                        <?php endif; ?>

                        <h2 class="fwx-section-title">SEO Nativo (Meta Tags)</h2>
                        <div class="<?php echo ! empty( $seo_plugin_active ) ? 'fwx-seo-plugin-blocked' : ''; ?>">
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Meta Description (Home)</label></th>
                                <td>
                                    <textarea name="fast_webx_options[home_meta_desc]" rows="3" class="large-text fwx-input-large" placeholder="Insira a descrição curta do seu site para o Google..."<?php echo ! empty( $seo_plugin_active ) ? ' disabled' : ''; ?>><?php echo esc_textarea($val('home_meta_desc')); ?></textarea>
                                    <p class="description">Esta descrição será exibida na Home do site (caso você não use uma página estática como Home ou a página não tenha um resumo definido).</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Automação de Descrições</label></th>
                                <td>
                                    <label style="display:block;margin-bottom:10px;"><input type="checkbox" name="fast_webx_options[enable_excerpt_as_meta]" value="1" <?php echo $checked('enable_excerpt_as_meta', 0); ?><?php echo ! empty( $seo_plugin_active ) ? ' disabled' : ''; ?> /> Usar <strong>Resumo (Excerpt)</strong> como Meta Description em posts e páginas.</label>
                                    <label><input type="checkbox" name="fast_webx_options[enable_cat_desc_as_meta]" value="1" <?php echo $checked('enable_cat_desc_as_meta', 0); ?><?php echo ! empty( $seo_plugin_active ) ? ' disabled' : ''; ?> /> Usar a <strong>Descrição da Categoria/Tag</strong> como Meta Description nas listagens.</label>
                                    <p class="description">Se desativado, o tema não injetará meta descriptions automaticamente, permitindo o uso de outros plugins de SEO.</p>
                                </td>
                            </tr>
                        </table>
                        </div>

                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>SEO de Imagens (Auto Alt)</label></th>
                                <td>
                                    <label style="display:block;margin-bottom:10px;"><input type="checkbox" name="fast_webx_options[auto_img_alt]" value="1" <?php echo $checked('auto_img_alt', 0); ?> /> <strong>Preencher ALT automaticamente no Upload.</strong> Caso a imagem não tenha um texto alternativo definido, o tema usará o título da imagem para preencher a tag <code>alt</code>.</label>
                                    <label><input type="checkbox" name="fast_webx_options[batch_img_alt]" value="1" <?php echo $checked('batch_img_alt', 0); ?> /> <strong>Habilitar Gerador de ALT em Lote.</strong> Quando ativo, exibe um botão dedicado no submenu "Otimizar Mídias" para varrer e preencher fisicamente a tag <code>alt</code> de todas as imagens do banco que não possuem o atributo.</label>
                                    <p class="description" style="margin-top:10px;">🚀 Melhora a indexação no Google Imagens de forma automatizada sem precisar editar post por post.</p>
                                </td>
                            </tr>
                        </table>

                        <h3 class="fwx-section-title" style="margin-top:40px;">Otimização de Imagens &amp; LCP (Lazy Load Pro)</h3>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Lazy Load Nativo</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[enable_lazy_load_global]" value="1" <?php echo $checked('enable_lazy_load_global', 0); ?> /> Habilitar o Lazy Load nativo do WordPress para imagens e iframes.</label>
                                    <p class="description">Ativa o atributo <code>loading="lazy"</code> nativo do WordPress. Desative apenas se você utilizar um plugin externo (ex: WP Rocket, LiteSpeed Cache) para gerenciar o carregamento preguiçoso de imagens.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Lazy Load Inteligente (Exceção LCP)</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[enable_smart_lazy_load]" value="1" <?php echo $checked('enable_smart_lazy_load', 0); ?> /> <strong>Evitar Lazy Load na 1ª Imagem do Conteúdo.</strong> Remove automaticamente o carregamento preguiçoso da primeira imagem de posts e páginas singulares.</label>
                                    <p class="description">🚀 <strong>Melhoria de Performance Crítica:</strong> Força <code>loading="eager"</code> e injeta <code>fetchpriority="high"</code> na primeira imagem real do conteúdo. Isso acelera drasticamente o tempo de carregamento da dobra superior do post (LCP), alavancando os scores de Core Web Vitals e PageSpeed Insights do Google.</p>
                                </td>
                            </tr>
                        </table>

                        <h3 class="fwx-section-title" style="margin-top:40px;">Redirecionamentos 301 (SEO & 404)</h3>
                        <p>O Fast WebX monitora automaticamente mudanças de URL para evitar erros 404 e perda de autoridade.</p>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Monitorar Slugs</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[enable_auto_301]" value="1" <?php echo $checked('enable_auto_301', 0); ?> /> Redirecionar automaticamente erros 404 (Páginas não encontradas).</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label>URL de Destino Customizada</label></th>
                                <td>
                                    <input type="url" name="fast_webx_options[custom_301_url]" class="regular-text fwx-input-large" value="<?php echo esc_url($val('custom_301_url')); ?>" placeholder="Deixe em branco para redirecionar para a Home" />
                                    <p class="description">Defina uma URL específica para onde o tráfego de links quebrados (404) será direcionado. Útil para páginas de captura ou ofertas especiais. Se em branco, vai para a raiz do site.</p>
                                </td>
                            </tr>
                        </table>

                        <h3 class="fwx-section-title" style="margin-top:40px;">Otimizações Globais de SEO</h3>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>SEO (Title Case)</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[seo_title_case]" value="1" <?php echo $checked('seo_title_case', 0); ?> /> <strong>Capitalizar títulos de posts e páginas automaticamente.</strong></label>
                                    <p class="description">Converte o título de posts e páginas para o formato de letra maiúscula na primeira letra de cada palavra (Title Case) no front-end, mantendo preposições e artigos em minúscula (ex: <em>"Guia de SEO para Iniciantes"</em>).</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>SEO (Links Externos)</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[seo_external_links_blank]" value="1" <?php echo $checked('seo_external_links_blank', 0); ?> /> <strong>Forçar links externos a abrirem em nova guia.</strong></label>
                                    <p class="description">Insere automaticamente <code>target="_blank" rel="noopener"</code> em todos os links externos contidos no corpo de posts e páginas, preservando a navegação interna na mesma aba.</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>SEO (Compatibilidade TOC)</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[seo_toc_compatibility]" value="1" <?php echo $checked('seo_toc_compatibility', 0); ?> /> <strong>Compatibilidade do TOC com Yoast SEO e RankMath.</strong></label>
                                    <p class="description">Informa ativamente os plugins de SEO que o tema possui um Sumário de Conteúdo nativo ativo, permitindo a validação de legibilidade e a remoção de alertas/erros nas análises dos plugins.</p>
                                </td>
                            </tr>
                        </table>
                    <?php else : ?>
                        <?php fwx_render_lock_overlay(); ?>
                    <?php endif; ?>
                    </div>

                    <!-- ABA EXTRAS -->
                    <div id="tab-extras" class="fwx-tab-content">
                        <h2 class="fwx-section-title">Backup, Migração & Shortcodes</h2>
                        
                        <div class="fwx-core-tools-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom:30px;">
                            <div class="fwx-core-tool-card" style="background:#fff; padding:20px; border-radius:10px; border:1px solid #ddd;">
                                <h3 style="margin-top:0;">📥 Exportar</h3>
                                <p style="font-size:0.85rem;">Crie um backup das suas configurações atuais.</p>
                                <button type="button" id="fwx-btn-export" class="button button-primary">Exportar (.json)</button>
                            </div>

                            <div class="fwx-core-tool-card" style="background:#fff; padding:20px; border-radius:10px; border:1px solid #ddd;">
                                <h3 style="margin-top:0;">📤 Importar</h3>
                                <p style="font-size:0.85rem;">Restaure configurações de um arquivo exportado.</p>
                                <input type="file" id="fwx-import-file" accept=".json" style="margin-bottom:10px; width:100%; font-size:11px;">
                                <button type="button" id="fwx-btn-import" class="button button-secondary">Importar Agora</button>
                                <div id="fwx-import-msg" style="margin-top:10px;"></div>
                            </div>

                            <div class="fwx-core-tool-card" style="background:#fff; padding:20px; border-radius:10px; border:1px solid #fcebea; border-color:#d63638;">
                                <h3 style="margin-top:0; color:#d63638;">🔄 Resetar</h3>
                                <p style="font-size:0.85rem;">Volta todas as opções para o padrão de fábrica.</p>
                                <button type="button" id="fwx-btn-reset" class="button" style="color:#d63638; border-color:#d63638;">Resetar Tudo</button>
                                <div id="fwx-reset-msg" style="margin-top:10px;"></div>
                            </div>
                        </div>

                        <h3 class="fwx-section-title" style="margin-top:40px;">Configurações de Idioma</h3>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Idioma do Tema</label></th>
                                <td>
                                    <select name="fast_webx_options[theme_language]" class="regular-text fwx-input-large">
                                        <option value="pt_BR" <?php selected($val('theme_language', 'pt_BR'), 'pt_BR'); ?>>Português (Brasil)</option>
                                        <option value="en_US" <?php selected($val('theme_language'), 'en_US'); ?>>Inglês (English)</option>
                                        <option value="es_ES" <?php selected($val('theme_language'), 'es_ES'); ?>>Espanhol (Español)</option>
                                        <option value="fr_FR" <?php selected($val('theme_language'), 'fr_FR'); ?>>Francês (Français)</option>
                                    </select>
                                    <p class="description">Escolha o idioma nativo do tema para tradução simplificada das strings estáticas (TOC, tempo de leitura, botões, etc.).</p>
                                    <p class="description" style="color: #999; font-size: 11px; margin-top: 5px;">Fase 1: Estruturação concluída e dicionário de Português configurado. Demais idiomas serão integrados nas próximas fases.</p>
                                </td>
                            </tr>
                        </table>

                        <h3 class="fwx-section-title" style="margin-top:40px;">Preferências do Painel Administrativo</h3>
                        <table class="form-table fwx-form-table">
                            <tr>
                                <th><label>Exibição de IDs</label></th>
                                <td>
                                    <label><input type="checkbox" name="fast_webx_options[show_admin_ids]" value="1" <?php echo $checked('show_admin_ids', 0); ?> /> Exibir coluna com <strong>ID numérico</strong> nas listagens de Posts e Páginas.</label>
                                    <p class="description">Facilita a identificação de IDs para uso em shortcodes e configurações avançadas sem precisar abrir a edição do post.</p>
                                </td>
                            </tr>
                        </table>



                        <hr style="margin: 40px 0; border: none; border-top: 1px solid rgba(0,0,0,0.05);">

                        <h3 class="fwx-section-title" style="margin-top:40px;">Guia de Shortcodes Fast WebX</h3>
                        <p class="description">Use estes shortcodes em widgets de texto ou posts para personalização extrema:</p>
                        
                        <table class="wp-list-table widefat fixed striped fwx-form-table" style="margin-top:20px; border: 1px solid rgba(0,0,0,0.05);">
                            <thead>
                                <tr style="background: rgba(0,0,0,0.02);">
                                    <th style="width:220px; font-weight:700;">Shortcode</th>
                                    <th style="font-weight:700;">O que ele exibe?</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td><code>[fwx_lead_form]</code></td><td>Formulário de captura de leads.</td></tr>
                                <tr><td><code>[fwx_contact_form]</code></td><td>Formulário de contato.</td></tr>
                                <tr><td><code>[fwx_author_box]</code></td><td>Box de autor completo (Single Post).</td></tr>
                                <tr><td><code>[fwx_sidebar_author]</code></td><td>Widget de autor resumido (Sidebar).</td></tr>
                                <tr><td><code>[fwx_sidebar_related]</code></td><td>Posts relacionados (Sidebar).</td></tr>
                                <tr><td><code>[fwx_related_posts]</code></td><td>Grid de relacionados (Single Post).</td></tr>
                                <tr><td><code>[fwx_share_buttons]</code></td><td>Botões de compartilhamento.</td></tr>
                                <tr><td><code>[fwx_link_in_bio]</code></td><td>Página de Bio (Link Tree).</td></tr>
                                <tr><td><code>[fwx_social_links]</code></td><td>Ícones sociais configurados.</td></tr>
                                <tr><td><code>[fwx_loja]</code></td><td>Catálogo de produtos (vitrine). Parâmetros: <code>columns</code>, <code>per_page</code>, <code>categoria</code>, <code>orderby</code>.</td></tr>
                            </tbody>
                        </table>
                    </div>

                    
                </div>
                
                <div style="margin-top: 20px;">
                    <?php submit_button('Salvar Configurações', 'primary', 'submit_bottom', false); ?>
                </div>
            </form>
        </div>

        <script>
            // Lógica simples e vanilla para as abas do admin com persistência local
            document.addEventListener('DOMContentLoaded', function() {
                const tabs = document.querySelectorAll('.fwx-tab-btn');
                const contents = document.querySelectorAll('.fwx-tab-content');
                
                // Restaura a aba ativa salva anteriormente
                const activeTabTarget = localStorage.getItem('fwx_active_tab');
                if (activeTabTarget && document.getElementById(activeTabTarget)) {
                    tabs.forEach(t => t.classList.remove('active'));
                    contents.forEach(c => c.classList.remove('active'));
                    
                    const activeTabBtn = document.querySelector('.fwx-tab-btn[data-target="' + activeTabTarget + '"]');
                    if (activeTabBtn) {
                        activeTabBtn.classList.add('active');
                    }
                    document.getElementById(activeTabTarget).classList.add('active');
                }
                
                tabs.forEach(tab => {
                    tab.addEventListener('click', function(e) {
                        e.preventDefault();
                        const target = this.getAttribute('data-target');
                        
                        tabs.forEach(t => t.classList.remove('active'));
                        contents.forEach(c => c.classList.remove('active'));
                        
                        this.classList.add('active');
                        document.getElementById(target).classList.add('active');
                        
                        // Salva o alvo da aba no localStorage
                        localStorage.setItem('fwx_active_tab', target);
                    });
                });

                // Controle dinâmico do campo 'Fundo do Hero (Claro/Escuro)' e 'Texto do Hero (Claro/Escuro)'
                const heroLayoutSelect = document.getElementById('fwx_hero_layout');
                const heroBgRow = document.getElementById('fwx_hero_bg_row');
                const heroTextRow = document.getElementById('fwx_hero_text_row');
                
                if (heroLayoutSelect) {
                    function updateHeroBgStatus() {
                        const isLayout1 = heroLayoutSelect.value === 'layout-1';
                        
                        if (heroBgRow) {
                            if (isLayout1) {
                                heroBgRow.classList.remove('fwx-disabled-row');
                            } else {
                                heroBgRow.classList.add('fwx-disabled-row');
                            }
                        }
                        
                        if (heroTextRow) {
                            if (isLayout1) {
                                heroTextRow.classList.remove('fwx-disabled-row');
                            } else {
                                heroTextRow.classList.add('fwx-disabled-row');
                            }
                        }
                    }
                    
                    // Executa na inicialização
                    updateHeroBgStatus();
                    
                    // Escuta alterações
                    heroLayoutSelect.addEventListener('change', updateHeroBgStatus);
                }

                // Controle dinâmico do Tipo de Mídia da Hero
                const mediaTypeSelect = document.getElementById('home_hero_media_type_select');
                const heroImageRow = document.getElementById('fwx_hero_image_row');
                const heroVideoRow = document.getElementById('fwx_hero_video_row');
                
                if (mediaTypeSelect) {
                    function updateHeroMediaStatus() {
                        const isVideo = mediaTypeSelect.value === 'video';
                        if (heroVideoRow) {
                            if (isVideo) {
                                heroVideoRow.classList.remove('fwx-disabled-row');
                            } else {
                                heroVideoRow.classList.add('fwx-disabled-row');
                            }
                        }
                    }
                    updateHeroMediaStatus();
                    mediaTypeSelect.addEventListener('change', updateHeroMediaStatus);
                }

                // Controle dinâmico do Fallback de Thumbnail
                const fallbackTypeSelect = document.getElementById('thumbnail_fallback_type');
                const customFallbackWrapper = document.getElementById('fwx_custom_fallback_wrapper');
                
                if (fallbackTypeSelect && customFallbackWrapper) {
                    function updateFallbackStatus() {
                        if (fallbackTypeSelect.value === 'custom') {
                            customFallbackWrapper.style.display = 'block';
                        } else {
                            customFallbackWrapper.style.display = 'none';
                        }
                    }
                    updateFallbackStatus();
                    fallbackTypeSelect.addEventListener('change', updateFallbackStatus);
                }
            });

            // Media Uploader Geral (Suporta múltiplos botões)
            jQuery(document).ready(function($){
                var custom_uploader;
                $('.fwx-upload-logo-btn, .fwx-upload-img-btn, .fwx-upload-video-btn').click(function(e) {
                    e.preventDefault();
                    var button = $(this);
                    var targetId = button.attr('data-target');
                    var inputField = targetId ? $('#' + targetId) : button.prev();
                    
                    var isVideo = button.hasClass('fwx-upload-video-btn');
                    var titleText = isVideo ? 'Selecionar Vídeo' : 'Selecionar Imagem';
                    var btnText = isVideo ? 'Usar este vídeo' : 'Usar esta imagem';
                    var libraryType = isVideo ? 'video' : 'image';
                    
                    custom_uploader = wp.media.frames.file_frame = wp.media({
                        title: titleText,
                        button: { text: btnText },
                        library: { type: libraryType },
                        multiple: false
                    });
                    
                    custom_uploader.on('select', function() {
                        var attachment = custom_uploader.state().get('selection').first().toJSON();
                        inputField.val(attachment.url);
                    });
                    
                    custom_uploader.open();
                });
            });

            // ========================================
            // EXPORTAR / IMPORTAR / RESETAR — Vanilla JS
            // ========================================
            (function() {
                var ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
                var nonce   = '<?php echo esc_js( wp_create_nonce( 'fwx_settings_tool' ) ); ?>';

                // --- EXPORTAR ---
                var btnExport = document.getElementById('fwx-btn-export');
                if (btnExport) {
                    btnExport.addEventListener('click', function() {
                        btnExport.disabled = true;
                        btnExport.textContent = 'Gerando...';
                        fetch(ajaxUrl + '?action=fwx_export_settings&nonce=' + nonce)
                            .then(function(r) {
                                return r.text().then(function(text) {
                                    try {
                                        return JSON.parse(text);
                                    } catch(e) {
                                        throw new Error(text || 'Falha ao processar resposta.');
                                    }
                                });
                            })
                            .then(function(res) {
                                if (res.success) {
                                    var blob = new Blob([JSON.stringify(res.data, null, 2)], {type: 'application/json'});
                                    var a = document.createElement('a');
                                    a.style.display = 'none';
                                    a.href = URL.createObjectURL(blob);
                                    a.download = 'fast-webx-backup-' + new Date().toISOString().slice(0,10) + '.json';
                                    document.body.appendChild(a);
                                    a.click();
                                    setTimeout(function() {
                                        document.body.removeChild(a);
                                        URL.revokeObjectURL(a.href);
                                    }, 200);
                                } else {
                                    alert('Erro: ' + (res.data || 'Falha ao exportar.'));
                                }
                                btnExport.disabled = false;
                                btnExport.textContent = 'Exportar (.json)';
                            })
                            .catch(function(err) {
                                alert('Erro ao exportar: ' + (err.message || 'Verifique a conexão.'));
                                btnExport.disabled = false;
                                btnExport.textContent = 'Exportar (.json)';
                            });
                    });
                }

                // --- IMPORTAR ---
                var btnImport = document.getElementById('fwx-btn-import');
                var fileInput = document.getElementById('fwx-import-file');
                var importMsg = document.getElementById('fwx-import-msg');
                if (btnImport && fileInput) {
                    btnImport.addEventListener('click', function() {
                        if (!fileInput.files.length) {
                            importMsg.innerHTML = '<span style="color:#d63638;">Selecione um arquivo .json primeiro.</span>';
                            return;
                        }
                        if (!confirm('Tem certeza? Isso sobrescreverá TODAS as configurações atuais do tema.')) return;

                        var reader = new FileReader();
                        reader.onload = function(e) {
                            try {
                                var parsed = JSON.parse(e.target.result);
                            } catch(err) {
                                importMsg.innerHTML = '<span style="color:#d63638;">Arquivo JSON inválido.</span>';
                                return;
                            }

                            btnImport.disabled = true;
                            btnImport.textContent = 'Importando...';
                            importMsg.innerHTML = '<span style="color:#2271b1;">⏳ Importando configurações...</span>';

                            var fd = new FormData();
                            fd.append('action', 'fwx_import_settings');
                            fd.append('nonce', nonce);
                            fd.append('settings', JSON.stringify(parsed));

                            fetch(ajaxUrl, { method: 'POST', body: fd })
                                .then(function(r) {
                                    return r.text().then(function(text) {
                                        try {
                                            return JSON.parse(text);
                                        } catch(e) {
                                            throw new Error(text || 'Falha na importação.');
                                        }
                                    });
                                })
                                .then(function(res) {
                                    if (res.success) {
                                        importMsg.innerHTML = '<span style="color:#46b450; font-weight:600;">✅ ' + res.data + '</span>';
                                        localStorage.setItem('fwx_active_tab', 'tab-extras');
                                        setTimeout(function() { window.location.reload(); }, 1000);
                                    } else {
                                        importMsg.innerHTML = '<span style="color:#d63638;">❌ ' + (res.data || 'Erro.') + '</span>';
                                        btnImport.disabled = false;
                                        btnImport.textContent = 'Importar Agora';
                                    }
                                })
                                .catch(function(err) {
                                    importMsg.innerHTML = '<span style="color:#d63638;">Erro: ' + (err.message || 'Erro de conexão.') + '</span>';
                                    btnImport.disabled = false;
                                    btnImport.textContent = 'Importar Agora';
                                });
                        };
                        reader.readAsText(fileInput.files[0]);
                    });
                }

                // --- RESETAR ---
                var btnReset = document.getElementById('fwx-btn-reset');
                var resetMsg = document.getElementById('fwx-reset-msg');
                if (btnReset) {
                    btnReset.addEventListener('click', function() {
                        if (!confirm('⚠️ ATENÇÃO: Todas as configurações do tema serão restauradas ao padrão de fábrica.\n\nRecomendamos exportar um backup antes.\n\nDeseja continuar?')) return;
                        if (!confirm('🔴 CONFIRMAÇÃO FINAL: Tem absoluta certeza? Essa ação NÃO pode ser desfeita.')) return;

                        btnReset.disabled = true;
                        btnReset.textContent = 'Resetando...';
                        resetMsg.innerHTML = '<span style="color:#2271b1;">⏳ Restaurando configurações de fábrica...</span>';

                        var fd = new FormData();
                        fd.append('action', 'fwx_reset_settings');
                        fd.append('nonce', nonce);

                        fetch(ajaxUrl, { method: 'POST', body: fd })
                            .then(function(r) {
                                return r.text().then(function(text) {
                                    try {
                                        return JSON.parse(text);
                                    } catch(e) {
                                        throw new Error(text || 'Falha na resposta do servidor.');
                                    }
                                });
                            })
                            .then(function(res) {
                                if (res.success) {
                                    resetMsg.innerHTML = '<span style="color:#46b450; font-weight:600;">✅ ' + res.data + '</span>';
                                    localStorage.setItem('fwx_active_tab', 'tab-extras');
                                    setTimeout(function() { window.location.reload(); }, 1000);
                                } else {
                                    resetMsg.innerHTML = '<span style="color:#d63638;">❌ ' + (res.data || 'Erro.') + '</span>';
                                    btnReset.disabled = false;
                                    btnReset.textContent = 'Resetar Tema';
                                }
                            })
                            .catch(function(err) {
                                resetMsg.innerHTML = '<span style="color:#d63638;">Erro: ' + (err.message || 'Erro de conexão.') + '</span>';
                                btnReset.disabled = false;
                                btnReset.textContent = 'Resetar Tema';
                            });
                    });
                }
            })();

        </script>
        <?php
    }
}

new Fast_WebX_Admin();

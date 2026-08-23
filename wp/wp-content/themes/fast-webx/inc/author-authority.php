<?php
/**
 * Fast WebX - Sistema de Autoridade & E-E-A-T (Fase 8.2)
 *
 * @package Fast_WebX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Função Auxiliar: Renderiza ícones sociais SVG
 */
function fwx_get_social_icon_svg( $network ) {
    $icons = array(
        'facebook'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>',
        'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>',
        'youtube'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.42a2.78 2.78 0 0 0-1.94 2C1 8.14 1 12 1 12s0 3.86.42 5.58a2.78 2.78 0 0 0 1.94 2c1.71.42 8.6.42 8.6.42s6.88 0 8.6-.42a2.78 2.78 0 0 0 1.94-2C23 15.86 23 12 23 12s0-3.86-.42-5.58z"></path><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"></polygon></svg>',
        'tiktok'    => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path></svg>',
        'linkedin'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>',
        'twitter'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4l11.733 16h4.267l-11.733 -16z"></path><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772"></path></svg>',
        'pinterest' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 22c.9-2.86 1.88-6 1.88-6s-.46-.92-.46-2.3c0-2.15 1.24-3.76 2.8-3.76 1.32 0 1.96.99 1.96 2.18 0 1.33-.85 3.32-1.29 5.16-.36 1.54.78 2.79 2.3 2.79 2.76 0 4.88-2.91 4.88-7.11 0-3.72-2.67-6.31-6.48-6.31-4.41 0-7 3.31-7 6.74 0 1.33.51 2.76 1.15 3.54.13.15.15.28.1.48l-.43 1.76c-.07.28-.23.38-.53.24-1.94-.9-3.14-3.73-3.14-6 0-4.89 3.56-9.39 10.25-9.39 5.38 0 9.56 3.83 9.56 8.95 0 5.35-3.37 9.65-8.06 9.65-1.57 0-3.05-.82-3.56-1.78l-1.03 3.92A10.2 10.2 0 0 1 8 22z"></path></svg>',
        'whatsapp'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>',
    );
    return isset( $icons[$network] ) ? $icons[$network] : '';
}

/**
 * Função Auxiliar: Renderiza a lista de redes sociais configuradas no painel
 */
function fwx_render_author_social_icons() {
    $networks = array(
        'facebook'  => fwx_get_option( 'social_facebook' ),
        'instagram' => fwx_get_option( 'social_instagram' ),
        'youtube'   => fwx_get_option( 'social_youtube' ),
        'tiktok'    => fwx_get_option( 'social_tiktok' ),
        'linkedin'  => fwx_get_option( 'social_linkedin' ),
        'twitter'   => fwx_get_option( 'social_twitter' ),
        'pinterest' => fwx_get_option( 'social_pinterest' ),
        'whatsapp'  => fwx_get_option( 'social_whatsapp' ),
    );

    $output = '<div class="fwx-author-socials">';
    foreach ( $networks as $name => $url ) {
        if ( ! empty( $url ) ) {
            $output .= sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer" class="fwx-social-link fwx-social-%s" aria-label="%s">%s</a>',
                esc_url( $url ),
                esc_attr( $name ),
                esc_attr( ucfirst( $name ) ),
                fwx_get_social_icon_svg( $name )
            );
        }
    }
    $output .= '</div>';
    return $output;
}

/**
 * 1. Widget de Bio do Autor (Sidebar)
 * Injetado automaticamente na sidebar caso a opção esteja ativa.
 */
function fwx_sidebar_author_widget() {
    if ( ! fwx_get_option( 'enable_sidebar_author', 0 ) ) {
        return;
    }

    $show = false;
    if ( is_singular( 'post' ) && fwx_get_option( 'author_widget_in_single_post', 0 ) ) {
        $show = true;
    } elseif ( is_singular( 'fwx_produto' ) && fwx_get_option( 'author_widget_in_single_product', 0 ) ) {
        $show = true;
    } elseif ( ( is_home() || is_archive() || is_search() ) && ! fwx_is_loja_page() && fwx_get_option( 'author_widget_in_archive_post', 0 ) ) {
        $show = true;
    } elseif ( fwx_is_loja_page() && fwx_get_option( 'author_widget_in_archive_product', 0 ) ) {
        $show = true;
    } elseif ( is_page() && ! fwx_is_loja_page() && fwx_get_option( 'author_widget_in_single_post', 0 ) ) {
        $show = true;
    }

    if ( ! $show ) {
        return;
    }

    $options = get_option( 'fast_webx_options' );
    $bio_user_id = isset( $options['bio_user_id'] ) ? $options['bio_user_id'] : '';

    if ( ! empty( $bio_user_id ) ) {
        $author_id = absint( $bio_user_id );
    } else {
        $author_id = get_the_author_meta( 'ID' );
        if ( ! $author_id ) {
            $users = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
            $author_id = ! empty( $users ) ? $users[0]->ID : 0;
        }
    }

    if ( ! $author_id ) return;

    // Fallbacks Customizados
    $custom_name = isset( $options['bio_custom_name'] ) ? $options['bio_custom_name'] : '';
    $custom_bio  = isset( $options['bio_custom_text'] ) ? $options['bio_custom_text'] : '';

    $author_name = ! empty( $custom_name ) ? $custom_name : get_the_author_meta( 'display_name', $author_id );
    $author_bio  = ! empty( $custom_bio ) ? $custom_bio : get_the_author_meta( 'description', $author_id );
    $author_url  = get_the_author_meta( 'user_url', $author_id );

    if ( empty( $author_bio ) ) {
        $author_bio = sprintf( fwx_t( 'author_bio_fallback' ), get_bloginfo( 'name' ) );
    }

    echo '<section class="widget fwx-sidebar-author">';
    echo '<div class="fwx-sidebar-author-inner">';
    echo '<div class="fwx-sidebar-author-avatar">';
    echo get_avatar( $author_id, 96 );
    echo '</div>';
    echo '<h2 class="fwx-sidebar-author-name">' . esc_html( $author_name ) . '</h2>';
    echo '<p class="fwx-sidebar-author-bio">' . esc_html( $author_bio ) . '</p>';
    
    if ( ! empty( $author_url ) ) {
        echo '<a href="' . esc_url( $author_url ) . '" class="fwx-btn-primary fwx-sidebar-btn-author">' . esc_html( fwx_t( 'author_profile_btn' ) ) . '</a>';
    }

    echo fwx_render_author_social_icons();

    echo '</div>';
    echo '</section>';
}

/**
 * 2. Página de Bio Completa (Shortcode Link in Bio)
 * Uso: [fwx_link_in_bio]
 */
function fwx_render_link_in_bio_shortcode() {
    ob_start();
    
    $options = get_option( 'fast_webx_options' );
    $bio_user_id = isset( $options['bio_user_id'] ) ? $options['bio_user_id'] : '';

    if ( ! empty( $bio_user_id ) ) {
        $author_id = absint( $bio_user_id );
    } else {
        $author_id = get_the_author_meta( 'ID' );
        if ( ! $author_id ) {
            $users = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
            $author_id = ! empty( $users ) ? $users[0]->ID : 0;
        }
    }

    if ( ! $author_id ) return '';

    // Fallbacks Customizados
    $custom_name = isset( $options['bio_custom_name'] ) ? $options['bio_custom_name'] : '';
    $custom_bio  = isset( $options['bio_custom_text'] ) ? $options['bio_custom_text'] : '';

    $author_name = ! empty( $custom_name ) ? $custom_name : get_the_author_meta( 'display_name', $author_id );
    $author_bio  = ! empty( $custom_bio ) ? $custom_bio : get_the_author_meta( 'description', $author_id );
    
    $highlight_id = isset( $options['bio_highlight_id'] ) ? $options['bio_highlight_id'] : '';
    $footer_text = isset( $options['bio_footer_text'] ) ? $options['bio_footer_text'] : '';

    // Links virão do painel Fast WebX
    $links = array();
    for ( $i = 1; $i <= 5; $i++ ) {
        $url = isset( $options['bio_link_url_' . $i] ) ? $options['bio_link_url_' . $i] : '';
        $text = isset( $options['bio_link_text_' . $i] ) ? $options['bio_link_text_' . $i] : '';
        if ( ! empty( $url ) && ! empty( $text ) ) {
            $links[] = array( 
                'id' => $i,
                'url' => $url, 
                'text' => $text 
            );
        }
    }
    ?>
    <div class="fwx-link-tree-wrapper">
        <div class="fwx-link-tree-header">
            <div class="fwx-link-tree-avatar">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="Voltar para a Home" class="fwx-bio-avatar-link">
                    <?php echo get_avatar( $author_id, 120 ); ?>
                </a>
            </div>
            <h2 class="fwx-link-tree-name"><?php echo esc_html( $author_name ); ?></h2>
            <p class="fwx-link-tree-bio"><?php echo esc_html( $author_bio ); ?></p>
            <?php echo fwx_render_author_social_icons(); ?>
        </div>

        <div class="fwx-link-tree-links">
            <?php foreach ( $links as $link ) : 
                $class = 'fwx-link-tree-btn fwx-btn-404 btn-primary';
                if ( (string)$highlight_id === (string)$link['id'] ) {
                    $class .= ' fwx-link-tree-highlight';
                }
            ?>
                <a href="<?php echo esc_url( $link['url'] ); ?>" class="<?php echo esc_attr( $class ); ?>" target="_blank" rel="noopener noreferrer">
                    <?php echo esc_html( $link['text'] ); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ( ! empty( $footer_text ) ) : ?>
            <div class="fwx-link-tree-footer">
                <p><?php echo esc_html( $footer_text ); ?></p>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'fwx_link_in_bio', 'fwx_render_link_in_bio_shortcode' );

/**
 * 3. Widget de Captura de Leads (Sidebar)
 */
function fwx_sidebar_lead_widget() {
    if ( ! fwx_get_option( 'enable_sidebar_lead_widget', 0 ) ) {
        return;
    }

    $show = false;
    if ( is_singular( 'post' ) && fwx_get_option( 'lead_widget_in_single_post', 0 ) ) {
        $show = true;
    } elseif ( is_singular( 'fwx_produto' ) && fwx_get_option( 'lead_widget_in_single_product', 0 ) ) {
        $show = true;
    } elseif ( ( is_home() || is_archive() || is_search() ) && ! fwx_is_loja_page() && fwx_get_option( 'lead_widget_in_archive_post', 0 ) ) {
        $show = true;
    } elseif ( fwx_is_loja_page() && fwx_get_option( 'lead_widget_in_archive_product', 0 ) ) {
        $show = true;
    } elseif ( is_page() && ! fwx_is_loja_page() && fwx_get_option( 'lead_widget_in_single_post', 0 ) ) {
        $show = true;
    }

    if ( ! $show ) {
        return;
    }

    echo '<section class="widget fwx-sidebar-lead">';
    // Reutiliza o shortcode de captura de leads existente
    echo do_shortcode( '[fwx_lead_form]' );
    echo '</section>';
}

// Removido o hook automático: os widgets agora são controlados por fwx_render_sidebar() em functions.php

// Estilos específicos para sociais migrados para style.css

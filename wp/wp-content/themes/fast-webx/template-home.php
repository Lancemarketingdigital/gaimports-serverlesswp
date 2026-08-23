<?php
/**
 * Template Name: Home
 *
 * @package Fast_WebX
 */

get_header();
?>
<main id="primary" class="site-main fwx-home-lp-main">
<?php
// Configurações da Home
$home_type       = fwx_get_option( 'home_lp_type', 'autor' );
$hero_title      = fwx_get_option( 'home_hero_title', get_bloginfo( 'name' ) );
$hero_desc       = fwx_get_option( 'home_hero_desc', get_bloginfo( 'description' ) );
$hero_btn_txt    = fwx_get_option( 'home_hero_btn_text', fwx_t( 'lp_hero_btn_default' ) );
$hero_btn_url    = fwx_get_option( 'home_hero_btn_url', '#' );
$hero_img        = fwx_get_option( 'home_hero_img', '' );
$hero_media_type = fwx_get_option( 'home_hero_media_type', 'image' );
$hero_video      = fwx_get_option( 'home_hero_video', '' );
$lp_main_color   = fwx_get_option( 'home_lp_color', '#dd3333' );

// Detecta se a URL é YouTube
$is_youtube = false;
$youtube_id = '';
if ( ! empty( $hero_video ) ) {
    if ( preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $hero_video, $match ) ) {
        $is_youtube = true;
        $youtube_id = $match[1];
    }
}

// Detecta se a URL é Vimeo
$is_vimeo = false;
$vimeo_id = '';
if ( ! empty( $hero_video ) && ! $is_youtube ) {
    if ( preg_match( '%vimeo\.com/(?:channels/(?:\w+/)?|groups/([^/]*)/videos/|album/(\d+)/video/|video/|)(\d+)(?:$|[?&])%i', $hero_video, $match ) ) {
        $is_vimeo = true;
        $vimeo_id = $match[3];
    }
}

$is_external_video = $is_youtube || $is_vimeo;
$video_provider = '';
$video_src = '';
$fallback_thumb = '';

if ( $is_youtube ) {
    $video_provider = 'youtube';
    $video_src = 'https://www.youtube.com/embed/' . $youtube_id . '?autoplay=1&rel=0';
    $fallback_thumb = 'https://img.youtube.com/vi/' . $youtube_id . '/maxresdefault.jpg';
} elseif ( $is_vimeo ) {
    $video_provider = 'vimeo';
    $video_src = 'https://player.vimeo.com/video/' . $vimeo_id . '?autoplay=1';
}

$thumb_url = ! empty( $hero_img ) ? $hero_img : ( ! empty( $fallback_thumb ) ? $fallback_thumb : '' );

// Cores Específicas do Home Hero
$home_hero_bg = fwx_get_option( 'home_hero_bg_color', '' );
$home_hero_bg_dark = fwx_get_option( 'home_hero_bg_color_dark', '' );
$home_hero_text = fwx_get_option( 'home_hero_text_color', '' );
$home_hero_text_dark = fwx_get_option( 'home_hero_text_color_dark', '' );

$bg_var = ! empty( $home_hero_bg ) ? $home_hero_bg : 'var(--fwx-primary)';
$text_var = ! empty( $home_hero_text ) ? $home_hero_text : '#ffffff';

$bg_var_dark = ! empty( $home_hero_bg_dark ) ? $home_hero_bg_dark : $bg_var;
$text_var_dark = ! empty( $home_hero_text_dark ) ? $home_hero_text_dark : $text_var;
?>

<style>
    /* Variáveis locais para a LP */
    .fwx-lp-hero, .fwx-lp-capture-cta {
        background-color: <?php echo esc_attr( $bg_var ); ?> !important;
        color: <?php echo esc_attr( $text_var ); ?> !important;
    }
    .fwx-lp-hero-title, .fwx-lp-hero-desc, .fwx-lp-capture-title, .fwx-lp-capture-desc {
        color: <?php echo esc_attr( $text_var ); ?> !important;
    }
    html[data-theme='dark'] .fwx-lp-hero, html[data-theme='dark'] .fwx-lp-capture-cta {
        background-color: <?php echo esc_attr( $bg_var_dark ); ?> !important;
        color: <?php echo esc_attr( $text_var_dark ); ?> !important;
    }
    html[data-theme='dark'] .fwx-lp-hero-title, html[data-theme='dark'] .fwx-lp-hero-desc, html[data-theme='dark'] .fwx-lp-capture-title, html[data-theme='dark'] .fwx-lp-capture-desc {
        color: <?php echo esc_attr( $text_var_dark ); ?> !important;
    }

    .fwx-lp-capture-title {
        font-size: clamp(2rem, 4vw, 2.8rem);
        font-weight: 800;
        margin-bottom: 20px;
        text-align: left;
    }

    /* Diferenciação de Estilos (Presets) */
    
    /* 1. AUTOR: Layout de Alto Impacto (Centralizado e Empilhado) */
    .fwx-home-lp.fwx-home-type-autor .fwx-lp-hero {
        background: <?php echo esc_attr( $bg_var ); ?> !important;
        text-align: center !important;
    }
    html[data-theme='dark'] .fwx-home-lp.fwx-home-type-autor .fwx-lp-hero {
        background: <?php echo esc_attr( $bg_var_dark ); ?> !important;
    }
    @media (min-width: 992px) {
        .fwx-home-lp.fwx-home-type-autor .fwx-lp-hero {
            padding: 60px 0 100px 0 !important;
        }
    }
    .fwx-home-lp .fwx-scroll-indicator {
        z-index: 20 !important;
        display: block !important;
    }
    .fwx-home-lp.fwx-home-type-autor .fwx-lp-hero-inner {
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        max-width: 1000px !important;
        margin: 0 auto !important;
    }
    .fwx-home-lp.fwx-home-type-autor .fwx-lp-hero-content,
    .fwx-home-lp.fwx-home-type-autor .fwx-lp-hero-media {
        flex: none !important;
        width: 100% !important;
        max-width: 800px !important;
        margin: 0 auto !important;
    }
    @media (min-width: 992px) {
        .fwx-home-lp.fwx-home-type-autor .fwx-lp-hero-inner {
            gap: 30px !important;
        }
        .fwx-home-lp.fwx-home-type-autor .fwx-lp-hero-title {
            font-size: clamp(2.8rem, 6vw, 4.8rem) !important;
        }
        .fwx-home-lp.fwx-home-type-autor .fwx-lp-hero-desc {
            font-size: 1.4rem !important;
        }
    }
    .fwx-home-lp.fwx-home-type-autor .fwx-lp-hero-title {
        margin-bottom: 30px !important;
    }
    .fwx-home-lp.fwx-home-type-autor .fwx-lp-hero-desc {
        margin: 0 auto 40px auto !important;
        max-width: 700px !important;
    }
    .fwx-home-lp.fwx-home-type-autor .fwx-hero-img-wrapper {
        border-radius: 24px;
        overflow: hidden;
    }

    /* 2. EDITORIAL: Visual de Revista (Imagem à Esquerda no Desktop) */
    .fwx-home-lp.fwx-home-type-editorial .fwx-lp-hero {
        background: <?php echo esc_attr( $bg_var ); ?> !important;
    }
    html[data-theme='dark'] .fwx-home-lp.fwx-home-type-editorial .fwx-lp-hero {
        background: <?php echo esc_attr( $bg_var_dark ); ?> !important;
    }
    @media (min-width: 992px) {
        .fwx-home-lp.fwx-home-type-editorial .fwx-lp-hero {
            border-bottom: 8px solid var(--fwx-secondary);
        }
        .fwx-home-lp.fwx-home-type-editorial .fwx-lp-hero-inner {
            flex-direction: row-reverse !important; 
            text-align: left;
        }
        .fwx-home-lp.fwx-home-type-editorial .fwx-lp-hero-content {
            flex: 1;
            padding-right: 40px;
        }
        .fwx-home-lp.fwx-home-type-editorial .fwx-lp-hero-desc {
            max-width: 550px;
            font-style: italic;
            border-left: 3px solid rgba(255,255,255,0.3);
            padding-left: 20px;
        }
    }
    .fwx-home-lp.fwx-home-type-editorial .fwx-lp-hero-title {
        font-weight: 900;
        letter-spacing: -2px;
    }

    /* 3. CORPORATIVO: Clássico (Texto à Esquerda, Imagem à Direita no Desktop) */
    .fwx-home-lp.fwx-home-type-corporativo .fwx-lp-hero {
        background: <?php echo esc_attr( $bg_var ); ?> !important;
    }
    html[data-theme='dark'] .fwx-home-lp.fwx-home-type-corporativo .fwx-lp-hero {
        background: <?php echo esc_attr( $bg_var_dark ); ?> !important;
    }
    @media (min-width: 992px) {
        .fwx-home-lp.fwx-home-type-corporativo .fwx-lp-hero-inner {
            flex-direction: row !important;
            text-align: left !important;
        }
        .fwx-home-lp.fwx-home-type-corporativo .fwx-lp-hero-content {
            flex: 1.0 !important;
        }
        .fwx-home-lp.fwx-home-type-corporativo .fwx-lp-hero-media {
            flex: 1.2 !important;
        }
    }
    .fwx-home-lp .fwx-hero-img-wrapper {
        border-radius: 24px;
        overflow: hidden;
    }

    /* OTIMIZAÇÃO MOBILE UNIFICADA (Identidade Única) */
    @media (max-width: 992px) {
        .fwx-home-lp .fwx-lp-hero {
            padding: 20px 0 60px 0 !important;
            min-height: 85vh !important;
            text-align: center !important;
        }
        .fwx-home-lp .fwx-lp-hero-inner {
            flex-direction: column !important;
            align-items: center !important;
            gap: 10px !important; /* Espaço mínimo entre imagem e título */
        }
        .fwx-home-lp .fwx-lp-hero-content,
        .fwx-home-lp .fwx-lp-hero-media {
            width: 100% !important;
            max-width: 100% !important;
            margin: 10px !important;
            padding: 0 !important;
        }
        .fwx-home-lp .fwx-lp-hero-title {
            font-size: 1.8rem !important;
            margin-bottom: 12px !important;
            line-height: 1.2 !important;
            text-align: center !important;
        }
        .fwx-home-lp .fwx-lp-hero-desc {
            font-size: 1.05rem !important;
            margin: 0 auto 20px auto !important;
            padding: 0 !important;
            border: none !important;
            text-align: center !important;
            font-style: normal !important;
        }


        .fwx-home-lp .fwx-lp-hero-actions {
            justify-content: center !important;
        }
        .fwx-home-lp .fwx-scroll-indicator {
            bottom: 15px !important;
        }
    }
</style>

<div class="fwx-home-lp fwx-home-type-<?php echo esc_attr( $home_type ); ?>">

    <!-- SEÇÃO HERO -->
    <?php if ( fwx_get_option( 'home_show_hero', 0 ) ) : ?>
    <section class="fwx-lp-hero">
        <div class="fwx-container fwx-lp-hero-inner">
            <div class="fwx-lp-hero-content">
                <h1 class="fwx-lp-hero-title"><?php echo wp_kses_post( $hero_title ); ?></h1>
                <p class="fwx-lp-hero-desc"><?php echo esc_html( $hero_desc ); ?></p>
                <?php if ( $hero_btn_txt ) : ?>
                <div class="fwx-lp-hero-actions">
                    <a href="<?php echo esc_url( $hero_btn_url ); ?>" class="fwx-btn-lp btn-primary"><?php echo esc_html( $hero_btn_txt ); ?></a>
                </div>
                <?php endif; ?>
            </div>
            <?php if ( ! empty( $hero_img ) || ( $hero_media_type === 'video' && ! empty( $hero_video ) ) ) : ?>
            <div class="fwx-lp-hero-media">
                <div class="fwx-hero-img-wrapper <?php echo ( $hero_media_type === 'video' && ! empty( $hero_video ) ) ? 'fwx-hero-video-wrapper' : ''; ?> <?php echo ( $hero_media_type === 'video' && $is_external_video ) ? 'fwx-hero-video-lazy' : ''; ?>" <?php echo ( $hero_media_type === 'video' && $is_external_video ) ? 'data-provider="' . esc_attr( $video_provider ) . '" data-src="' . esc_url( $video_src ) . '"' : ''; ?>>
                    <?php if ( $hero_media_type === 'video' && ! empty( $hero_video ) ) : ?>
                        <?php if ( $is_external_video ) : ?>
                            <?php if ( ! empty( $thumb_url ) ) : ?>
                                <?php 
                                $thumb_id = fwx_get_attachment_id_by_url( $thumb_url );
                                if ( $thumb_id ) {
                                    echo wp_get_attachment_image( $thumb_id, 'full', false, array( 
                                        'loading'       => 'eager', 
                                        'fetchpriority' => 'high',
                                        'decoding'      => 'async',
                                        'alt'           => esc_attr( $hero_title ),
                                        'class'         => 'fwx-hero-video-thumb'
                                    ) );
                                } else {
                                    echo '<img src="' . esc_url( $thumb_url ) . '" alt="' . esc_attr( $hero_title ) . '" loading="eager" fetchpriority="high" decoding="async" class="fwx-hero-video-thumb">';
                                }
                                ?>
                            <?php endif; ?>
                            <button class="fwx-hero-video-play-btn" aria-label="Play Video">
                                <svg viewBox="0 0 24 24" width="48" height="48" fill="currentColor">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </button>
                        <?php else : ?>
                            <video class="fwx-hero-video-local" autoplay loop muted playsinline controls>
                                <source src="<?php echo esc_url( $hero_video ); ?>">
                                Seu navegador não suporta tags de vídeo.
                            </video>
                        <?php endif; ?>
                    <?php else : ?>
                        <?php 
                        $hero_img_id = fwx_get_attachment_id_by_url( $hero_img );
                        if ( $hero_img_id ) {
                            echo wp_get_attachment_image( $hero_img_id, 'full', false, array( 
                                'loading'       => 'eager', 
                                'fetchpriority' => 'high',
                                'alt'           => esc_attr( $hero_title )
                            ) );
                        } else {
                            echo '<img src="' . esc_url( $hero_img ) . '" alt="' . esc_attr( $hero_title ) . '" loading="eager" fetchpriority="high">';
                        }
                        ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- INDICADOR DE ROLAGEM -->
        <div class="fwx-scroll-indicator">
            <div class="fwx-mouse">
                <div class="fwx-wheel"></div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- SEÇÃO SOBRE / BIO -->
    <?php 
    $show_about  = fwx_get_option( 'home_show_about', 0 );
    $about_title = fwx_get_option( 'home_about_title', 'Sobre Nós' );
    $about_text  = fwx_get_option( 'home_about_text', '' );
    if ( $show_about && $about_text ) : 
    ?>
    <section class="fwx-lp-about">
        <div class="fwx-container">
            <div class="fwx-lp-about-grid">
                <div class="fwx-lp-about-image-side">
                    <div class="fwx-lp-about-avatar-wrapper">
                        <?php 
                        $bio_user_id = fwx_get_option( 'bio_user_id', '' );
                        if ( ! empty( $bio_user_id ) ) {
                            $author_id = absint( $bio_user_id );
                        } else {
                            $author_id = get_the_author_meta( 'ID' );
                            if ( ! $author_id ) {
                                $users = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
                                $author_id = ! empty( $users ) ? $users[0]->ID : 1;
                            }
                        }
                        echo '<a href="' . esc_url( home_url( '/' ) ) . '" title="Voltar para a Home" class="fwx-bio-avatar-link">';
                        echo get_avatar( $author_id, 300 ); 
                        echo '</a>';
                        ?>
                        <div class="fwx-lp-about-badge"><?php echo esc_html( fwx_t( 'lp_about_badge' ) ); ?></div>
                    </div>
                </div>
                <div class="fwx-lp-about-content-side">
                    <h2 class="fwx-lp-section-title"><?php echo esc_html( $about_title ); ?></h2>
                    <div class="fwx-lp-about-text">
                        <?php echo wp_kses_post( wpautop( $about_text ) ); ?>
                    </div>
                    <?php if ( function_exists('fwx_render_author_social_icons') ) : ?>
                        <div class="fwx-lp-about-socials">
                            <?php echo fwx_render_author_social_icons(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- SEÇÃO SERVIÇOS -->
    <?php if ( fwx_get_option( 'home_show_services', 0 ) ) : ?>
    <section class="fwx-lp-services">
        <div class="fwx-container">
            <div class="fwx-lp-section-header">
                <h2 class="fwx-lp-section-title"><?php echo esc_html( fwx_get_option( 'home_services_title', 'Nossos Serviços' ) ); ?></h2>
                <?php $services_desc = fwx_get_option( 'home_services_desc', '' ); if ( $services_desc ) : ?>
                    <p class="fwx-lp-section-desc"><?php echo esc_html( $services_desc ); ?></p>
                <?php endif; ?>
            </div>
            <div class="fwx-lp-services-grid">
                <?php 
                $s_count = intval( fwx_get_option( 'home_services_count', '3' ) );
                for ( $i = 1; $i <= $s_count; $i++ ) : 
                    $s_title = fwx_get_option( "home_service_title_$i" );
                    $s_desc  = fwx_get_option( "home_service_desc_$i" );
                    if ( $s_title ) :
                    ?>
                    <div class="fwx-lp-service-item">
                        <div class="fwx-lp-service-icon">
                            <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                        </div>
                        <h3><?php echo esc_html( $s_title ); ?></h3>
                        <p><?php echo esc_html( $s_desc ); ?></p>
                        <?php $s_link = fwx_get_option( "home_service_link_$i" ); if ( $s_link ) : 
                            $s_target = fwx_get_option( "home_service_new_tab_$i", 0 ) ? ' target="_blank" rel="noopener"' : ''; ?>
                            <a href="<?php echo esc_url( $s_link ); ?>" class="fwx-service-cta"<?php echo $s_target; ?>><?php echo esc_html( fwx_t( 'lp_service_read_more' ) ); ?></a>
                        <?php endif; ?>
                    </div>
                    <?php 
                    endif;
                endfor; 
                ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- SEÇÃO 3 ETAPAS (FLUXO) -->
    <?php if ( fwx_get_option( 'home_show_steps', 0 ) ) : ?>
    <section class="fwx-lp-steps">
        <div class="fwx-container">
            <div class="fwx-lp-section-header">
                <h2 class="fwx-lp-section-title"><?php echo esc_html( fwx_get_option( 'home_steps_title', 'Como Funciona' ) ); ?></h2>
                <?php $steps_desc = fwx_get_option( 'home_steps_desc', '' ); if ( $steps_desc ) : ?>
                    <p class="fwx-lp-section-desc"><?php echo esc_html( $steps_desc ); ?></p>
                <?php endif; ?>
            </div>
            <div class="fwx-lp-steps-grid">
                <?php 
                for ( $i = 1; $i <= 3; $i++ ) : 
                    $st_title = fwx_get_option( "home_step_{$i}_title" );
                    $st_desc  = fwx_get_option( "home_step_{$i}_desc" );
                    if ( $st_title ) :
                    ?>
                    <div class="fwx-lp-step-item">
                        <div class="fwx-lp-step-number"><?php echo $i; ?></div>
                        <h3><?php echo esc_html( $st_title ); ?></h3>
                        <p><?php echo esc_html( $st_desc ); ?></p>
                    </div>
                    <?php 
                    endif;
                endfor; 
                ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- SEÇÃO DE CAPTURA (LEAD MAGNET) -->
    <?php 
    $show_capture = fwx_get_option( 'home_show_capture', 0 );
    $cap_title    = fwx_get_option( 'home_capture_title' );
    $cap_short    = fwx_get_option( 'home_capture_shortcode' );
    if ( $show_capture && ( $cap_title || $cap_short ) ) : 
    ?>
    <section class="fwx-lp-capture-cta">
        <div class="fwx-container">
            <div class="fwx-lp-capture-inner">
                <div class="fwx-lp-capture-content">
                    <h2 class="fwx-lp-capture-title"><?php echo esc_html( $cap_title ); ?></h2>
                    <p class="fwx-lp-capture-desc"><?php echo esc_html( fwx_get_option( 'home_capture_desc' ) ); ?></p>
                </div>
                <div class="fwx-lp-capture-form">
                    <?php echo do_shortcode( $cap_short ); ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- SEÇÃO TESTEMUNHOS -->
    <?php if ( fwx_get_option( 'home_show_testimonials', 0 ) ) : ?>
    <section class="fwx-lp-testimonials">
        <div class="fwx-container">
            <div class="fwx-lp-section-header">
                <h2 class="fwx-lp-section-title"><?php echo esc_html( fwx_get_option( 'home_testimonials_title', 'O que dizem' ) ); ?></h2>
                <?php $test_desc = fwx_get_option( 'home_testimonials_desc', '' ); if ( ! empty( $test_desc ) ) : ?>
                    <p class="fwx-lp-section-desc"><?php echo wp_kses_post( $test_desc ); ?></p>
                <?php endif; ?>
            </div>
            <div class="fwx-lp-testimonials-grid">
                <?php 
                $t_count = intval( fwx_get_option( 'home_testimonials_count', '3' ) );
                for ( $i = 1; $i <= $t_count; $i++ ) : 
                    $t_text   = fwx_get_option( "home_testimonial_{$i}_text" );
                    $t_author = fwx_get_option( "home_testimonial_{$i}_author" );
                    $t_img    = fwx_get_option( "home_testimonial_{$i}_img" );
                    if ( $t_text ) :
                    ?>
                    <div class="fwx-lp-testimonial-item">
                        <div class="fwx-testimonial-quote">“</div>
                        <p><?php echo esc_html( $t_text ); ?></p>
                        <?php if ( ! empty( $t_img ) ) : 
                            $t_img_id = fwx_get_attachment_id_by_url( $t_img );
                            $t_img_url = $t_img;
                            if ( $t_img_id ) {
                                $t_img_url_src = wp_get_attachment_image_url( $t_img_id, 'fwx-avatar' );
                                if ( $t_img_url_src ) {
                                    $t_img_url = $t_img_url_src;
                                } else {
                                    $t_img_url_src_thumb = wp_get_attachment_image_url( $t_img_id, 'thumbnail' );
                                    if ( $t_img_url_src_thumb ) {
                                        $t_img_url = $t_img_url_src_thumb;
                                    }
                                }
                            }
                        ?>
                            <div class="fwx-testimonial-meta">
                                <img src="<?php echo esc_url( $t_img_url ); ?>" class="fwx-testimonial-avatar" alt="<?php echo esc_attr( $t_author ); ?>" loading="lazy" width="60" height="60" />
                                <cite><?php echo esc_html( $t_author ); ?></cite>
                            </div>
                        <?php else : ?>
                            <cite><?php echo esc_html( $t_author ); ?></cite>
                        <?php endif; ?>
                    </div>
                    <?php 
                    endif;
                endfor; 
                ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- SEÇÃO BLOG FEED -->
    <?php if ( fwx_get_option( 'home_show_blog', 0 ) ) : ?>
    <section class="fwx-lp-blog">
        <div class="fwx-container">
            <div class="fwx-lp-section-header">
                <h2 class="fwx-lp-section-title"><?php echo esc_html( fwx_get_option( 'home_blog_title', 'Últimas do Blog' ) ); ?></h2>
                <?php $blog_desc = fwx_get_option( 'home_blog_desc', '' ); if ( ! empty( $blog_desc ) ) : ?>
                    <p class="fwx-lp-section-desc"><?php echo wp_kses_post( $blog_desc ); ?></p>
                <?php endif; ?>
            </div>
            <div class="fwx-posts-grid fwx-grid-layout-b">
                <?php
                $lp_posts = new WP_Query( array( 'posts_per_page' => 3, 'post_status' => 'publish' ) );
                if ( $lp_posts->have_posts() ) :
                    while ( $lp_posts->have_posts() ) : $lp_posts->the_post();
                        ?>
                        <article class="fwx-post-item">
                            <div class="fwx-post-thumb">
                                <a href="<?php the_permalink(); ?>"><?php fwx_the_post_thumbnail( get_the_ID(), 'fwx-home-grid' ); ?></a>
                            </div>
                            <div class="fwx-post-content">
                                <h3 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <?php fwx_render_post_meta(); ?>
                            </div>
                        </article>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- SEÇÃO CONTATO -->
    <?php if ( fwx_get_option( 'home_show_contact', 0 ) ) : ?>
    <section class="fwx-lp-contact">
        <div class="fwx-container">
            <div class="fwx-lp-contact-box">
                <div class="fwx-lp-section-header">
                    <h2 class="fwx-lp-section-title"><?php echo esc_html( fwx_get_option( 'home_contact_title', 'Fale Conosco' ) ); ?></h2>
                    <p><?php echo esc_html( fwx_get_option( 'home_contact_desc', 'Tire suas dúvidas ou peça um orçamento sem compromisso.' ) ); ?></p>
                </div>
                <div class="fwx-lp-contact-form">
                    <?php 
                    $c_content = fwx_get_option( 'home_contact_content', '[fwx_contact_form]' );
                    if ( ! empty( $c_content ) ) {
                        echo do_shortcode( wp_kses_post( $c_content ) );
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var lazyVideos = document.querySelectorAll('.fwx-hero-video-lazy');
    lazyVideos.forEach(function(video) {
        video.addEventListener('click', function() {
            var src = video.getAttribute('data-src');
            var iframe = document.createElement('iframe');
            iframe.setAttribute('src', src);
            iframe.setAttribute('frameborder', '0');
            iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
            iframe.setAttribute('allowfullscreen', '1');
            
            iframe.style.width = '100%';
            iframe.style.height = '100%';
            iframe.style.position = 'absolute';
            iframe.style.top = '0';
            iframe.style.left = '0';
            iframe.style.borderRadius = 'inherit';
            
            video.innerHTML = '';
            video.appendChild(iframe);
            video.classList.add('fwx-video-loaded');
            video.classList.add('fwx-video-active');
        });
    });
});
</script>

</main>
<?php get_footer(); ?>

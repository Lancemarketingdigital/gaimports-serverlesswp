<?php
/**
 * Template de Página 404 do Fast WebX
 * Focado em retenção de usuário e identidade visual premium.
 *
 * @package Fast_WebX
 */

get_header(); 

$home_layout = fwx_get_option( 'home_layout', 'layout-a' );
?>

<main id="primary" class="site-main fwx-container">
    <section class="fwx-404-wrapper">
        <div class="fwx-404-content">
            <div class="fwx-404-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="fwx-primary-icon"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
            </div>
            
            <h1 class="fwx-404-title"><?php echo esc_html( fwx_t( '404_title' ) ); ?></h1>
            <p class="fwx-404-text"><?php echo esc_html( fwx_t( '404_text' ) ); ?></p>

            <div class="fwx-404-actions">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="fwx-btn-404 btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    <?php echo esc_html( fwx_t( '404_btn_home' ) ); ?>
                </a>
                <button id="fwx-404-search-trigger" class="fwx-btn-404 btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <?php echo esc_html( fwx_t( '404_btn_search' ) ); ?>
                </button>
            </div>
        </div>

        <div class="fwx-404-recent">
            <h2 class="fwx-recent-title"><?php echo esc_html( fwx_t( '404_recent_title' ) ); ?></h2>
            
            <div class="fwx-posts-grid fwx-grid-<?php echo esc_attr( $home_layout ); ?>">
                <?php
                $recent_posts = new WP_Query( array(
                    'posts_per_page'      => 3,
                    'post_status'         => 'publish',
                    'ignore_sticky_posts' => 1
                ) );

                if ( $recent_posts->have_posts() ) :
                    while ( $recent_posts->have_posts() ) : $recent_posts->the_post(); ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class( 'fwx-post-item' ); ?>>
                            <div class="fwx-post-thumb">
                                <a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
                                    <?php fwx_the_post_thumbnail( get_the_ID(), 'fwx-home-grid' ); ?>
                                </a>
                            </div>
                            <div class="fwx-post-content">
                                <?php
                                $categories = get_the_category();
                                if ( ! empty( $categories ) ) {
                                    echo '<a href="' . esc_url( get_category_link( $categories[0]->term_id ) ) . '" class="fwx-cat-label fwx-cat-id-' . esc_attr( $categories[0]->term_id ) . '">' . esc_html( $categories[0]->name ) . '</a>';
                                }
                                ?>
                                <h3 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            </div>
                        </article>
                    <?php endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchTrigger = document.getElementById('fwx-404-search-trigger');
    const searchToggle = document.getElementById('fwx-search-toggle');
    
    if (searchTrigger && searchToggle) {
        searchTrigger.addEventListener('click', function() {
            searchToggle.click();
        });
    }
});
</script>

<?php get_footer(); ?>

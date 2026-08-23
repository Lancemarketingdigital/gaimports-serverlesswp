<?php
/**
 * Template Name: Full Width
 * Template Post Type: page, post
 *
 * @package Fast_WebX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header(); ?>

<style>
    /* Reset absoluto de margens e espaçamentos para o Template Full Width */
    #page {
        margin: 0 !important;
        padding: 0 !important;
    }
    .fwx-page-full-width,
    .fwx-full-width-content,
    .entry-content {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    /* Zera margens do primeiro e último elementos gerados pelos builders */
    .entry-content > *:first-child {
        margin-top: 0 !important;
    }
    .entry-content > *:last-child {
        margin-bottom: 0 !important;
    }
    /* Remove a margem superior padrão do rodapé nesta página */
    .site-footer {
        margin-top: 0 !important;
    }
</style>

<main id="primary" class="site-main fwx-page-full-width">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'fwx-full-width-content' ); ?>>
			<?php if ( get_post_meta( get_the_ID(), '_fwx_hide_title', true ) !== '1' ) : ?>
				<header class="entry-header fwx-container">
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				</header>
			<?php endif; ?>

			<div class="entry-content">
				<?php
				the_content();

				wp_link_pages( array(
					'before' => '<div class="page-links">' . esc_html__( 'Páginas:', 'fast-webx' ),
					'after'  => '</div>',
				) );
				?>
			</div>
		</article>
		<?php
		if ( comments_open() || get_comments_number() ) :
			echo '<div class="fwx-container">';
			comments_template();
			echo '</div>';
		endif;

	endwhile;
	?>
</main>

<?php get_footer(); ?>

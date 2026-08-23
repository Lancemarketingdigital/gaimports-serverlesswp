<?php
/**
 * Template Name: Sem Sidebar
 *
 * @package Fast_WebX
 */

get_header(); ?>

<main id="primary" class="site-main fwx-container">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'fwx-page-wide-content' ); ?>>
			<?php if ( get_post_meta( get_the_ID(), '_fwx_hide_title', true ) !== '1' ) : ?>
				<header class="entry-header">
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
		// Se os comentários estiverem abertos ou tivermos pelo menos um comentário, carrega o template de comentários.
		if ( comments_open() || get_comments_number() ) :
			comments_template();
		endif;

	endwhile;
	?>
</main>

<?php get_footer(); ?>

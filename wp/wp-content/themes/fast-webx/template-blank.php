<?php
/**
 * Template Name: Full Blank
 * Template Post Type: page, post
 * 
 * Este template remove header, footer, sidebars e margens, mantendo apenas o wp_head() para CSS
 * e wp_footer() para scripts esseciais. Ideal para funis e Landing Pages externas.
 *
 * @package Fast_WebX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<?php wp_head(); ?>
    <style>
        /* CSS de Isolamento Total para Modelo Blank */
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            /* background: transparent — REMOVIDO: impedia que a LP definisse seu próprio fundo */
            min-height: 100vh;
        }
        .fwx-blank-canvas {
            margin: 0 !important;
            padding: 0 !important;
        }
        /* Nota: max-width e margin nos filhos diretos foram removidos para não interferir no layout da LP */
    </style>
</head>
<body <?php body_class( 'fwx-blank-canvas' ); ?>>
<?php wp_body_open(); ?>

	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile; 
	?>

<?php wp_footer(); ?>
</body>
</html>

<?php
if (!defined('ABSPATH')) exit;
?><!doctype html><html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head><body <?php body_class(); ?>>
<div class="ga-site-wrap"><?php ga_restored_header(); ?>
<section class="ga-blog-hero"><div class="ga-shell"><h1>Conteúdos e orientações</h1><p>Informações sobre medicamentos, importação regulada e acesso seguro, com linguagem clara e responsável.</p></div></section>
<main class="ga-shell">
<?php if (have_posts()): ?><div class="ga-post-grid"><?php while(have_posts()): the_post(); ?><article class="ga-card">
<a class="ga-card-image" href="<?php the_permalink(); ?>"><?php if(has_post_thumbnail()) the_post_thumbnail('large'); ?></a>
<div class="ga-card-body"><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><p><?php echo esc_html(wp_trim_words(get_the_excerpt(),24,'…')); ?></p><a class="ga-card-link" href="<?php the_permalink(); ?>">Ler artigo →</a></div></article><?php endwhile; ?></div><?php the_posts_pagination(); else: ?><div class="ga-empty">Nenhum artigo publicado.</div><?php endif; ?>
</main><?php ga_restored_footer(); ?></div><?php wp_footer(); ?></body></html>

<?php
if (!defined('ABSPATH')) exit;
?><!doctype html><html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head><body <?php body_class(); ?>>
<div class="ga-site-wrap"><?php ga_restored_header(); ?><main class="ga-article"><?php while(have_posts()): the_post(); ?><article><h1><?php the_title(); ?></h1><div class="ga-article-content"><?php the_content(); ?></div></article><?php endwhile; ?></main><?php ga_restored_footer(); ?></div><?php wp_footer(); ?></body></html>

<?php
if (!defined('ABSPATH')) exit;
function ga_restored_setup(){add_theme_support('title-tag');add_theme_support('post-thumbnails');add_theme_support('html5',['search-form','comment-form','comment-list','gallery','caption','style','script']);}
add_action('after_setup_theme','ga_restored_setup');
function ga_restored_assets(){
  $uri=get_stylesheet_directory_uri();
  if(is_front_page()){
    wp_enqueue_style('ga-home-main',$uri.'/home-1.css',[],'20260823-1');
    wp_enqueue_style('ga-home-bottom',$uri.'/home-2.css',['ga-home-main'],'20260823-1');
    wp_enqueue_script('ga-home-menu',$uri.'/home-menu.js',[], '20260823-1', true);
    wp_enqueue_script('ga-home-footer',$uri.'/home-footer.js',[], '20260823-1', true);
  }else{
    wp_enqueue_style('ga-restored-style',get_stylesheet_uri(),[],'1.0.1');
  }
}
add_action('wp_enqueue_scripts','ga_restored_assets',20);
function ga_restored_logo_url(){
  $logo=get_page_by_path('logo-ga-imports',OBJECT,'attachment');
  if($logo){$url=wp_get_attachment_url($logo->ID);if($url)return $url;}
  return '';
}
function ga_restored_header(){
  $logo=ga_restored_logo_url(); ?>
<header class="ga-site-header"><div class="ga-shell ga-site-header-inner"><a class="ga-site-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="G&A Imports Brasil"><?php if($logo): ?><img src="<?php echo esc_url($logo); ?>" alt="G&A Imports Brasil"><?php else: ?><strong>G&amp;A Imports Brasil</strong><?php endif; ?></a><nav class="ga-site-nav" aria-label="Menu principal"><a href="<?php echo esc_url(home_url('/#home')); ?>">Home</a><a href="<?php echo esc_url(home_url('/#quem-somos')); ?>">Quem Somos</a><a href="<?php echo esc_url(home_url('/#atuacao')); ?>">Atuação</a><a href="<?php echo esc_url(home_url('/#processo')); ?>">Processo</a><a href="<?php echo esc_url(home_url('/blog/')); ?>">Blog</a><a href="<?php echo esc_url(home_url('/#contato')); ?>">Contato</a><a class="ga-cta" href="<?php echo esc_url(home_url('/#contato')); ?>">Falar com a equipe</a></nav></div></header><?php
}
function ga_restored_footer(){ ?><footer class="ga-footer"><div class="ga-shell ga-footer-inner"><span>G&amp;A Imports Brasil</span><a href="<?php echo esc_url(home_url('/')); ?>">gaimportsbrasil.com.br</a></div></footer><?php }

<?php
/**
 * Plugin Name: Lance GA Migration & Blob Media
 * Description: Migrates GA Imports from the old WordPress and persists media in Vercel Blob.
 * Version: 1.1.0
 */

declare(strict_types=1);
if (!defined('ABSPATH')) exit;

function lga_store(): string { return (string)(getenv('MEDIA_BLOB_STORE_ID') ?: getenv('SERVERLESSWP_STREAM_VERCEL_STORE_ID') ?: ''); }
function lga_token(): string { return (string)(getenv('MEDIA_BLOB_READ_WRITE_TOKEN') ?: getenv('SERVERLESSWP_STREAM_VERCEL_TOKEN') ?: ''); }
function lga_blob_base(): string { $s=lga_store(); return $s ? "https://{$s}.public.blob.vercel-storage.com" : ''; }
function lga_local_base(): string { return '/tmp/gaimports-wordpress-uploads'; }
function lga_source_base(): string { return 'https://cms.gaimportsbrasil.com.br'; }
function lga_rel(string $p): string { return ltrim(str_replace('\\','/',$p),'/'); }
function lga_mime(string $p): string {
    $m=['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp','gif'=>'image/gif','svg'=>'image/svg+xml','css'=>'text/css','js'=>'application/javascript'];
    return $m[strtolower(pathinfo($p,PATHINFO_EXTENSION))] ?? 'application/octet-stream';
}
function lga_guard(): void { if (!is_user_logged_in() || !current_user_can('manage_options')) wp_die('Administrator permission required.','Forbidden',['response'=>403]); }

function lga_blob_put(string $rel,string $body,string $mime=''): array {
    $store=lga_store(); $token=lga_token(); $rel=lga_rel($rel);
    if (!$store || !$token) return ['ok'=>false,'error'=>'Blob credentials unavailable'];
    $path='wp-content/uploads/'.$rel;
    $r=wp_remote_request('https://blob.vercel-storage.com/?pathname='.rawurlencode($path),[
        'method'=>'PUT','timeout'=>45,'redirection'=>2,
        'headers'=>['Authorization'=>'Bearer '.$token,'x-vercel-blob-store-id'=>$store,'x-content-type'=>$mime ?: lga_mime($rel),'x-allow-overwrite'=>'1','Content-Type'=>$mime ?: lga_mime($rel)],
        'body'=>$body,
    ]);
    if (is_wp_error($r)) return ['ok'=>false,'error'=>$r->get_error_message()];
    $c=(int)wp_remote_retrieve_response_code($r);
    return ($c>=200 && $c<300) ? ['ok'=>true,'url'=>lga_blob_base().'/'.$path] : ['ok'=>false,'error'=>'Blob HTTP '.$c.' '.substr((string)wp_remote_retrieve_body($r),0,250)];
}
function lga_fetch(string $url): array {
    if (str_contains($url,'/wp-content/uploads/')) {
        $path=(string)parse_url($url,PHP_URL_PATH);
        $url=lga_source_base().$path;
    }
    $r=wp_remote_get($url,['timeout'=>30,'redirection'=>3]);
    if (is_wp_error($r)) return ['ok'=>false,'error'=>$r->get_error_message()];
    $c=(int)wp_remote_retrieve_response_code($r);
    if ($c!==200) return ['ok'=>false,'error'=>'Source HTTP '.$c.' '.$url];
    $mime=(string)wp_remote_retrieve_header($r,'content-type');
    if (str_contains($mime,';')) $mime=trim(explode(';',$mime,2)[0]);
    return ['ok'=>true,'body'=>(string)wp_remote_retrieve_body($r),'mime'=>$mime];
}
function lga_api(string $route,array $params=[]): array {
    $params=array_merge(['per_page'=>100],$params);
    $url=lga_source_base().'/wp-json/wp/v2/'.$route.'?'.http_build_query($params);
    $r=wp_remote_get($url,['timeout'=>40,'redirection'=>2]);
    if (is_wp_error($r)) return [];
    if ((int)wp_remote_retrieve_response_code($r)!==200) return [];
    $d=json_decode((string)wp_remote_retrieve_body($r),true);
    return is_array($d)?$d:[];
}
function lga_old_media_id(int $id): int { $m=(array)get_option('lga_media_map',[]); return (int)($m[(string)$id] ?? 0); }
function lga_rewrite(string $s): string {
    $b=lga_blob_base(); if (!$b) return $s; $t=$b.'/wp-content/uploads/';
    return str_replace([
        'https://gaimportsbrasil.com.br/wp-content/uploads/','http://gaimportsbrasil.com.br/wp-content/uploads/',
        'https://www.gaimportsbrasil.com.br/wp-content/uploads/','http://www.gaimportsbrasil.com.br/wp-content/uploads/',
        'https://cms.gaimportsbrasil.com.br/wp-content/uploads/','http://cms.gaimportsbrasil.com.br/wp-content/uploads/'
    ],array_fill(0,6,$t),$s);
}

add_filter('upload_dir',function(array $u): array {
    $b=lga_blob_base(); if (!$b) return $u;
    $sub=(string)($u['subdir']??''); $base=lga_local_base();
    $u['basedir']=$base; $u['path']=$base.$sub; $u['baseurl']=$b.'/wp-content/uploads'; $u['url']=$u['baseurl'].$sub;
    if (!is_dir($u['path'])) wp_mkdir_p($u['path']);
    return $u;
},99);
add_filter('wp_handle_upload',function(array $u): array {
    $base=rtrim(lga_local_base(),'/').'/'; $file=str_replace('\\','/',(string)($u['file']??''));
    if ($file && str_starts_with($file,$base) && is_readable($file)) {
        $rel=substr($file,strlen($base)); $r=lga_blob_put($rel,(string)file_get_contents($file),(string)($u['type']??''));
        if (!empty($r['ok'])) $u['url']=$r['url'];
    }
    return $u;
},99);
add_filter('wp_update_attachment_metadata',function($meta,int $id){
    if (!is_array($meta)) return $meta; $rel=(string)get_post_meta($id,'_wp_attached_file',true); if (!$rel) $rel=(string)($meta['file']??''); if (!$rel) return $meta;
    $rel=lga_rel($rel); $base=rtrim(lga_local_base(),'/').'/'; $f=$base.$rel;
    if (is_readable($f)) lga_blob_put($rel,(string)file_get_contents($f),(string)get_post_mime_type($id));
    $dir=trim(dirname($rel),'.');
    foreach (($meta['sizes']??[]) as $sz) if (is_array($sz)&&!empty($sz['file'])) {
        $sr=($dir?$dir.'/':'').$sz['file']; $sf=$base.$sr; if (is_readable($sf)) lga_blob_put($sr,(string)file_get_contents($sf),(string)($sz['mime-type']??''));
    }
    return $meta;
},99,2);
add_filter('wp_get_attachment_url',function(string $url,int $id): string { $r=(string)get_post_meta($id,'_wp_attached_file',true); return ($r&&lga_blob_base())?lga_blob_base().'/wp-content/uploads/'.lga_rel($r):$url; },99,2);
add_filter('get_attached_file',function(string $file,int $id): string {
    $rel=(string)get_post_meta($id,'_wp_attached_file',true); if (!$rel||!lga_blob_base()) return $file;
    $local=rtrim(lga_local_base(),'/').'/'.lga_rel($rel); if (!is_file($local)) { wp_mkdir_p(dirname($local)); $r=wp_remote_get(lga_blob_base().'/wp-content/uploads/'.lga_rel($rel),['timeout'=>30]); if (!is_wp_error($r)&&wp_remote_retrieve_response_code($r)===200) file_put_contents($local,wp_remote_retrieve_body($r)); }
    return $local;
},99,2);

add_action('admin_post_lga_migrate_media',function(): void {
    lga_guard(); $media=lga_api('media',['orderby'=>'id','order'=>'asc']); if (!$media) wp_die('Could not read old media API.');
    $i=max(0,(int)($_GET['i']??0)); if ($i>=count($media)) { wp_safe_redirect(admin_url('admin-post.php?action=lga_migrate_content')); exit; }
    $m=$media[$i]; $old=(int)$m['id']; $details=is_array($m['media_details']??null)?$m['media_details']:[];
    $file=(string)($details['file']??''); if (!$file && !empty($m['source_url'])) { $p=(string)parse_url((string)$m['source_url'],PHP_URL_PATH); $needle='/wp-content/uploads/'; $pos=strpos($p,$needle); if ($pos!==false) $file=substr($p,$pos+strlen($needle)); }
    $errors=(array)get_option('lga_errors',[]);
    $targets=[]; if ($file && !empty($m['source_url'])) $targets[$file]=(string)$m['source_url'];
    $dir=$file?trim(dirname($file),'.'):'';
    foreach (($details['sizes']??[]) as $sz) if (is_array($sz)&&!empty($sz['file'])&&!empty($sz['source_url'])) $targets[($dir?$dir.'/':'').$sz['file']]=$sz['source_url'];
    foreach ($targets as $rel=>$src) { $f=lga_fetch((string)$src); if (empty($f['ok'])) { $errors[$rel]=$f['error']; continue; } $p=lga_blob_put($rel,$f['body'],$f['mime']); if (empty($p['ok'])) $errors[$rel]=$p['error']; else unset($errors[$rel]); }
    $existing=get_page_by_path((string)$m['slug'],OBJECT,'attachment'); $postarr=['post_author'=>get_current_user_id(),'post_date'=>(string)$m['date'],'post_title'=>wp_strip_all_tags((string)($m['title']['rendered']??'')),'post_excerpt'=>lga_rewrite((string)($m['caption']['rendered']??'')),'post_content'=>lga_rewrite((string)($m['description']['rendered']??'')),'post_status'=>'inherit','post_name'=>(string)$m['slug'],'post_type'=>'attachment','post_mime_type'=>(string)($m['mime_type']??'')];
    if ($existing instanceof WP_Post) { $postarr['ID']=$existing->ID; $new=wp_update_post(wp_slash($postarr),true); } else { $postarr['import_id']=$old; $new=wp_insert_post(wp_slash($postarr),true); }
    if (!is_wp_error($new)&&$new) {
        $map=(array)get_option('lga_media_map',[]); $map[(string)$old]=(int)$new; update_option('lga_media_map',$map,false);
        if ($file) update_post_meta((int)$new,'_wp_attached_file',$file);
        if ($details) update_post_meta((int)$new,'_wp_attachment_metadata',$details);
        if (!empty($m['alt_text'])) update_post_meta((int)$new,'_wp_attachment_image_alt',(string)$m['alt_text']);
        if ($file&&lga_blob_base()) { global $wpdb; $wpdb->update($wpdb->posts,['guid'=>lga_blob_base().'/wp-content/uploads/'.lga_rel($file)],['ID'=>(int)$new]); clean_post_cache((int)$new); }
    }
    update_option('lga_errors',$errors,false);
    wp_safe_redirect(admin_url('admin-post.php?action=lga_migrate_media&i='.($i+1))); exit;
});

add_action('admin_post_lga_migrate_content',function(): void {
    lga_guard(); $cats=lga_api('categories',['hide_empty'=>'false']); $catmap=[];
    foreach ($cats as $c) { $e=term_exists((string)$c['slug'],'category'); if (!$e) $e=wp_insert_term((string)$c['name'],'category',['slug'=>(string)$c['slug'],'description'=>(string)($c['description']??'')]); if (!is_wp_error($e)) $catmap[(string)$c['id']]=(int)(is_array($e)?$e['term_id']:$e); }
    update_option('lga_category_map',$catmap,false);
    $all=array_merge(lga_api('pages',['orderby'=>'id','order'=>'asc']),lga_api('posts',['orderby'=>'id','order'=>'asc'])); $postmap=[];
    foreach ($all as $p) {
        $type=(string)$p['type']; $existing=get_page_by_path((string)$p['slug'],OBJECT,$type);
        $arr=['post_author'=>get_current_user_id(),'post_date'=>(string)$p['date'],'post_date_gmt'=>(string)$p['date_gmt'],'post_content'=>lga_rewrite((string)($p['content']['rendered']??'')),'post_title'=>wp_strip_all_tags((string)($p['title']['rendered']??'')),'post_excerpt'=>lga_rewrite((string)($p['excerpt']['rendered']??'')),'post_status'=>'publish','post_name'=>(string)$p['slug'],'post_type'=>$type];
        if ($existing instanceof WP_Post) { $arr['ID']=$existing->ID; $new=wp_update_post(wp_slash($arr),true); } else { $arr['import_id']=(int)$p['id']; $new=wp_insert_post(wp_slash($arr),true); }
        if (is_wp_error($new)||!$new) continue; $postmap[(string)$p['id']]=(int)$new;
        if ($type==='post') { $newcats=[]; foreach (($p['categories']??[]) as $oc) if (isset($catmap[(string)$oc])) $newcats[]=$catmap[(string)$oc]; if ($newcats) wp_set_post_categories((int)$new,$newcats,false); }
        $fm=(int)($p['featured_media']??0); $mapped=lga_old_media_id($fm); if ($mapped) set_post_thumbnail((int)$new,$mapped);
    }
    update_option('lga_post_map',$postmap,false);
    $home=get_page_by_path('home',OBJECT,'page'); $blog=get_page_by_path('blog',OBJECT,'page');
    update_option('blogname','G & A Imports Brasil');
    update_option('blogdescription','Importação regulada de medicamentos Acesso seguro a medicamentos não comercializados no Brasil Assessoria especializada para pacientes, médicos, clínicas, hospitais e farmácias — da análise documental ao acompanhamento do processo, com transparência, rastreabilidade e atendimento humano.');
    update_option('permalink_structure','/%postname%/'); update_option('show_on_front','page'); if ($home) update_option('page_on_front',$home->ID); if ($blog) update_option('page_for_posts',$blog->ID);
    $icon=lga_old_media_id(26); if ($icon) update_option('site_icon',$icon); update_option('blog_public','0'); flush_rewrite_rules(false);
    $errors=(array)get_option('lga_errors',[]);
    header('Content-Type:text/html; charset=utf-8'); echo '<h1>GA Imports migration complete</h1><p>Media: '.esc_html((string)count((array)get_option('lga_media_map',[]))).' | Posts/pages: '.esc_html((string)count($postmap)).' | Errors: '.esc_html((string)count($errors)).'</p>'; if ($errors) echo '<pre>'.esc_html(print_r($errors,true)).'</pre>'; echo '<p><a href="'.esc_url(admin_url()).'">Back to dashboard</a></p>'; exit;
});

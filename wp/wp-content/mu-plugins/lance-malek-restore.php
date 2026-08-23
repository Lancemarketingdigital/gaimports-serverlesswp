<?php
/**
 * Plugin Name: Lance - Malek Safe Restore
 * Description: One-time sanitized restore for Malek Palmilhas on ServerlessWP/Vercel.
 */
if (!defined('ABSPATH')) { exit; }

function lance_malek_restore_authorized(): bool {
    $expected = getenv('MALEK_RESTORE_TOKEN') ?: '';
    $provided = isset($_GET['lance_malek_restore']) ? (string) $_GET['lance_malek_restore'] : '';
    return $expected !== '' && $provided !== '' && hash_equals($expected, $provided);
}

function lance_malek_recursive_replace($value, string $old, string $new) {
    if (is_array($value)) {
        foreach ($value as $k => $v) $value[$k] = lance_malek_recursive_replace($v, $old, $new);
        return $value;
    }
    if (is_object($value)) {
        foreach (get_object_vars($value) as $k => $v) $value->$k = lance_malek_recursive_replace($v, $old, $new);
        return $value;
    }
    return is_string($value) ? str_replace($old, $new, $value) : $value;
}

function lance_malek_rewrite_value($value, string $old, string $new) {
    if (!is_string($value) || $value === '') return $value;
    if (function_exists('is_serialized') && is_serialized($value)) {
        $decoded = maybe_unserialize($value);
        return maybe_serialize(lance_malek_recursive_replace($decoded, $old, $new));
    }
    return str_replace($old, $new, $value);
}

function lance_malek_dataset(): array {
    $encoded = '';
    foreach ([1,2,3,4] as $n) {
        $path = __DIR__ . '/malek-restore-data-' . $n . '.dat';
        $piece = @file_get_contents($path);
        if ($piece === false) throw new RuntimeException('restore_data_missing_' . $n);
        $encoded .= trim($piece);
    }
    $decoded = base64_decode($encoded, true);
    if ($decoded === false) throw new RuntimeException('restore_dataset_base64_failed');
    $raw = gzuncompress($decoded);
    if ($raw === false) throw new RuntimeException('restore_dataset_decompress_failed');
    $data = json_decode($raw, true);
    if (!is_array($data)) throw new RuntimeException('restore_dataset_json_failed');
    return $data;
}

function lance_malek_ensure_custom_tables(): void {
    global $wpdb;
    $p = $wpdb->prefix;
    $wpdb->query("CREATE TABLE IF NOT EXISTS {$p}snippets (id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL,description TEXT NOT NULL,code TEXT NOT NULL,tags TEXT NOT NULL,scope TEXT NOT NULL DEFAULT 'global',condition_id INTEGER NOT NULL DEFAULT 0,priority INTEGER NOT NULL DEFAULT 10,active INTEGER NOT NULL DEFAULT 0,modified TEXT NOT NULL,revision INTEGER NOT NULL DEFAULT 1,cloud_id TEXT NULL)");
    $wpdb->query("CREATE TABLE IF NOT EXISTS {$p}trustindex_google_reviews (id INTEGER PRIMARY KEY AUTOINCREMENT,hidden INTEGER NOT NULL DEFAULT 0,user TEXT NULL,user_photo TEXT NULL,text TEXT NULL,rating REAL NULL,highlight TEXT NULL,date TEXT NULL,reviewId TEXT NULL,reply TEXT NULL)");
    $wpdb->query("CREATE TABLE IF NOT EXISTS {$p}trustindex_google_views (date TEXT PRIMARY KEY,viewed INTEGER NOT NULL)");
}

function lance_malek_activate_required_plugins(): array {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    $plugins = ['code-snippets/code-snippets.php','wp-reviews-plugin-for-google/wp-reviews-plugin-for-google.php','wpvivid-backuprestore/wpvivid-backuprestore.php'];
    $result = [];
    foreach ($plugins as $plugin) {
        if (!is_plugin_active($plugin)) {
            $r = activate_plugin($plugin, '', false, false);
            $result[$plugin] = is_wp_error($r) ? $r->get_error_message() : 'activated';
        } else $result[$plugin] = 'already_active';
    }
    return $result;
}

function lance_malek_ensure_admin(): int {
    $email = trim((string) (getenv('MALEK_ADMIN_EMAIL') ?: ''));
    $pass = (string) (getenv('MALEK_ADMIN_TEMP_PASSWORD') ?: '');
    if ($email === '' || $pass === '') throw new RuntimeException('admin_env_missing');
    $user = get_user_by('login', $email);
    if (!$user) $user = get_user_by('email', $email);
    if ($user) {
        $uid = (int) $user->ID;
        $r = wp_update_user(['ID'=>$uid,'user_email'=>$email,'display_name'=>$email,'user_pass'=>$pass]);
        if (is_wp_error($r)) throw new RuntimeException('admin_update_failed:' . $r->get_error_message());
    } else {
        $uid = wp_create_user($email, $pass, $email);
        if (is_wp_error($uid)) throw new RuntimeException('admin_create_failed:' . $uid->get_error_message());
        $uid = (int) $uid;
    }
    $u = new WP_User($uid); $u->set_role('administrator'); update_user_meta($uid, 'locale', 'pt_BR');
    return $uid;
}

function lance_malek_restore_content(): array {
    global $wpdb;
    $data = lance_malek_dataset();
    $plugins = lance_malek_activate_required_plugins();
    lance_malek_ensure_custom_tables();
    $admin_id = lance_malek_ensure_admin();
    $upload = wp_upload_dir();
    $old_upload = 'https://malekpalmilhas.com.br/wp-content/uploads';
    $new_upload = rtrim((string) $upload['baseurl'], '/');
    $delete_order = [$wpdb->prefix.'term_relationships',$wpdb->prefix.'term_taxonomy',$wpdb->prefix.'terms',$wpdb->prefix.'postmeta',$wpdb->prefix.'posts',$wpdb->prefix.'snippets',$wpdb->prefix.'trustindex_google_reviews',$wpdb->prefix.'trustindex_google_views'];
    foreach ($delete_order as $table) $wpdb->query("DELETE FROM `{$table}`");
    $inserted = [];
    foreach ($data['tables'] as $source_table => $block) {
        $target = $wpdb->prefix . substr($source_table, 3); $columns = $block['columns']; $count = 0;
        foreach ($block['rows'] as $row) {
            $assoc = [];
            foreach ($columns as $i => $col) {
                $val = $row[$i] ?? null;
                if ($source_table === 'wp_posts' && $col === 'post_author') $val = $admin_id;
                elseif ($source_table === 'wp_postmeta' && $col === 'meta_value') {
                    if (($assoc['meta_key'] ?? '') === '_edit_last') $val = (string) $admin_id;
                    else $val = lance_malek_rewrite_value($val, $old_upload, $new_upload);
                } elseif (is_string($val)) $val = lance_malek_rewrite_value($val, $old_upload, $new_upload);
                $assoc[$col] = $val;
            }
            if ($wpdb->insert($target, $assoc) === false) throw new RuntimeException('insert_failed:' . $target . ':' . $wpdb->last_error);
            $count++;
        }
        $inserted[$source_table] = $count;
    }
    foreach ($data['options'] as $opt) { [$name,$value] = $opt; update_option($name, lance_malek_rewrite_value($value, $old_upload, $new_upload)); }
    update_option('admin_email',(string)getenv('MALEK_ADMIN_EMAIL')); update_option('siteurl','https://malekpalmilhas.com.br'); update_option('home','https://malekpalmilhas.com.br'); update_option('WPLANG','pt_BR');
    $license = (string) (getenv('FWX_LICENSE_KEY') ?: '');
    if ($license !== '') { update_option('fwx_license_key',$license); update_option('fwx_license_status','active'); update_option('fwx_license_domain','malekpalmilhas.com.br'); }
    switch_theme('fast-webx'); update_option('lance_malek_restore_content_done',gmdate('c'));
    return ['ok'=>true,'phase'=>'content','admin_id'=>$admin_id,'plugins'=>$plugins,'inserted'=>$inserted,'media_total'=>count($data['media_paths']),'media_baseurl'=>$new_upload];
}

function lance_malek_restore_media(int $offset): array {
    $data = lance_malek_dataset(); $paths = $data['media_paths']; $batch = 12; $slice = array_slice($paths,$offset,$batch); $upload = wp_upload_dir(); $base = rtrim((string)$upload['basedir'],'/'); $source_base='https://malekpalmilhas.com.br/wp-content/uploads/'; $written=0; $errors=[];
    foreach ($slice as $rel) {
        $rel=ltrim((string)$rel,'/'); $parts=array_map('rawurlencode',explode('/',$rel)); $src=$source_base.implode('/',$parts); $dest=$base.'/'.$rel; wp_mkdir_p(dirname($dest));
        $resp=wp_remote_get($src,['timeout'=>20,'redirection'=>3]);
        if (is_wp_error($resp)) { $errors[]=[$rel,$resp->get_error_message()]; continue; }
        $code=(int)wp_remote_retrieve_response_code($resp); $body=wp_remote_retrieve_body($resp);
        if ($code<200 || $code>=300 || $body==='') { $errors[]=[$rel,'http_'.$code]; continue; }
        if (@file_put_contents($dest,$body)===false) { $errors[]=[$rel,'write_failed']; continue; }
        $written++;
    }
    $next=$offset+count($slice); update_option('lance_malek_restore_media_offset',$next);
    return ['ok'=>true,'phase'=>'media','offset'=>$offset,'processed'=>count($slice),'written'=>$written,'errors'=>$errors,'next_offset'=>$next,'total'=>count($paths),'finished'=>$next>=count($paths),'baseurl'=>(string)$upload['baseurl']];
}

function lance_malek_restore_finalize(): array {
    $admin_id=lance_malek_ensure_admin(); switch_theme('fast-webx'); update_option('permalink_structure','/%postname%/'); update_option('blog_public','1'); update_option('siteurl','https://malekpalmilhas.com.br'); update_option('home','https://malekpalmilhas.com.br'); update_option('WPLANG','pt_BR'); delete_option('rewrite_rules'); flush_rewrite_rules(false); wp_cache_flush(); update_option('lance_malek_restore_finished',gmdate('c'));
    return ['ok'=>true,'phase'=>'finalize','admin_id'=>$admin_id,'theme'=>wp_get_theme()->get('Name'),'version'=>wp_get_theme()->get('Version'),'home'=>home_url('/'),'siteurl'=>site_url('/')];
}

add_action('init', function () {
    if (!isset($_GET['lance_malek_restore'])) return;
    if (!lance_malek_restore_authorized()) { status_header(403); wp_send_json(['ok'=>false,'error'=>'forbidden'],403); }
    try {
        $phase=isset($_GET['phase'])?sanitize_key((string)$_GET['phase']):'status';
        if ($phase==='content') wp_send_json(lance_malek_restore_content());
        if ($phase==='media') { $offset=isset($_GET['offset'])?max(0,(int)$_GET['offset']):0; wp_send_json(lance_malek_restore_media($offset)); }
        if ($phase==='finalize') wp_send_json(lance_malek_restore_finalize());
        wp_send_json(['ok'=>true,'phase'=>'status','content_done'=>get_option('lance_malek_restore_content_done'),'media_offset'=>(int)get_option('lance_malek_restore_media_offset',0),'finished'=>get_option('lance_malek_restore_finished'),'theme'=>wp_get_theme()->get('Name'),'theme_version'=>wp_get_theme()->get('Version'),'uploads'=>wp_upload_dir()['baseurl']]);
    } catch (Throwable $e) { status_header(500); wp_send_json(['ok'=>false,'error'=>$e->getMessage()],500); }
},1);

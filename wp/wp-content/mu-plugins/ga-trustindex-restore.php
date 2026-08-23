<?php
/**
 * Plugin Name: GA Imports Trustindex Restore
 * Description: One-time restoration of the Trustindex widget configuration and public Google review cache from the verified GA Imports database backup.
 */
if (!defined('ABSPATH')) exit;

add_action('plugins_loaded', function () {
    if (get_option('ga_trustindex_restored_20260823') === '1') return;

    $active = (array) get_option('active_plugins', []);
    foreach (['code-snippets/code-snippets.php','wp-reviews-plugin-for-google/wp-reviews-plugin-for-google.php'] as $plugin) {
        if (!in_array($plugin, $active, true) && is_file(WP_PLUGIN_DIR . '/' . $plugin)) $active[] = $plugin;
    }
    update_option('active_plugins', array_values(array_unique($active)));

    $page = [
        'id' => 'ChIJ87oJ5LfDzpQR2HEH7Ex5O6A',
        'name' => 'G&A Imports Brasil',
        'avatar_url' => 'https://lh3.googleusercontent.com/gps-cs-s/AHRPTWnclBieOm6HNzFBR1bJ76qjHLHlI7uu21N0OQRkbVbNCOpQxbz2wqMKFc8fjWjHZ_YbEveqcbJA1hYnPChqC2JNZjAeIIoba2ObclEbpKqN6w-zp7gYNUtF8zGhHNgnxDWUykvmO95UAgHP=w225-h300-k-no',
        'address' => 'Airport Offices, R. Vieira de Morais, 2110 - Sl 811 - Campo Belo, São Paulo - SP, 04517-007, Brazil',
        'rating_number' => '35',
        'rating_numbers' => [1=>1,2=>0,3=>0,4=>1,5=>33],
        'rating_numbers_last' => [1=>0,2=>0,3=>0,4=>0,5=>2],
        'rating_score' => '4.9',
        'timestamp' => 0,
    ];
    $opts = [
        'trustindex-google-active' => '1',
        'trustindex-google-page-details' => $page,
        'trustindex-google-lang' => 'pt',
        'trustindex-google-style-id' => '5',
        'trustindex-google-scss-set' => 'drop-shadow',
        'trustindex-google-verified-icon' => '1',
        'trustindex-google-enable-animation' => '1',
        'trustindex-google-show-arrows' => '1',
        'trustindex-google-show-header-button' => '1',
        'trustindex-google-reviews-load-more' => '1',
        'trustindex-google-show-reviewers-photo' => '1',
        'trustindex-google-no-rating-text' => '0',
        'trustindex-google-disable-font' => '1',
        'trustindex-google-show-logos' => '1',
        'trustindex-google-show-stars' => '1',
        'trustindex-google-footer-filter-text' => '1',
        'trustindex-google-show-review-replies' => '1',
        'trustindex-google-filter' => ['stars'=>[1,2,3,4,5], 'only-ratings'=>true],
        'trustindex-google-fomo-open' => '1',
        'trustindex-google-fomo-link' => '0',
        'trustindex-google-fomo-border' => '1',
        'trustindex-google-fomo-arrow' => '1',
        'trustindex-google-fomo-icon-background' => '0',
        'trustindex-google-widget-setted-up' => '1',
        'trustindex-google-load-css-inline' => '1',
    ];
    foreach ($opts as $k=>$v) update_option($k,$v,false);
    delete_option('trustindex-core-shortcode-inited');

    global $wpdb;
    $table = $wpdb->prefix . 'trustindex_google_reviews';
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta("CREATE TABLE {$table} (
      id tinyint(1) NOT NULL,
      hidden tinyint(1) NOT NULL DEFAULT 0,
      user varchar(255) DEFAULT NULL,
      user_photo text DEFAULT NULL,
      text text DEFAULT NULL,
      rating decimal(3,1) DEFAULT NULL,
      highlight varchar(11) DEFAULT NULL,
      date date DEFAULT NULL,
      reviewId text DEFAULT NULL,
      reply text DEFAULT NULL,
      PRIMARY KEY  (id)
    );");

    $reviews = [
      [1,0,'Alexandre Antonio','https://lh3.googleusercontent.com/a/ACg8ocLP76u_RKm1JU8UR48FnkEjkp97YLNcZQAW5paFU57wUBWDSw=s120-c-rp-mo-ba12-br100','Encontrei o pessoal através de pesquisa na internet, e me surpreendi com o atendimento e com atenção e o suporte durante todo o processo de compra de medicamentos, super recomendo o atendimento é fenomenal e com certeza comprarei mais vezes.\n\nObrigado pessoal !!',5.0,null,'2026-07-31','Ci9DQUlRQUNvZENodHljRjlvT2xaaGVFUXpVMUpxUlhCU1Qyc3llVVExUjJOSlIzYxAB','Obrigada, Alexandre Antonio! Gratidão pelas palavras, é isso que nos move!! Ficaremos sempre à disposição para melhor atendê-los!!'],
      [2,0,'Daniel Jose Bahi Aymone','https://lh3.googleusercontent.com/a-/ALV-UjWxzNzDFgFWniGcrRzNSWtpQK6f1E_o4BHv7SvEwrUiO4kI8rKU8A=s120-c-rp-mo-br100','Excelente atendimento.',5.0,null,'2026-07-30','Ci9DQUlRQUNvZENodHljRjlvT21sZldHNU9ZMHBXYVRsVWJITjZWVEpvYzBOdmRGRRAB','Obrigada, Daniel! Ficaremos sempre à disposição para melhor atendê-los!'],
      [3,0,'liliane tomaz','https://lh3.googleusercontent.com/a-/ALV-UjUce73cvb63fV8Yt-yJCJFzWRkYSfT8tcw-naqVy8NNlp5BCk0=s120-c-rp-mo-br100','Foi excelente, ótimo atendimento,são bem atenciosos e chegou no tempo previsto muito obrigada.',5.0,null,'2026-07-16','Ci9DQUlRQUNvZENodHljRjlvT2sxMGEzUk5hRzlHWlMxdmFTMWtVbmRpVERscldYYxAB','Obrigada, Liliane Tomaz!! Ficaremos sempre à disposição para melhor atendê-los!!'],
      [4,0,'Lais Simoes','https://lh3.googleusercontent.com/a/ACg8ocJriVwcrVcr9fHaDEliN83HCWOoqHtT08EOHcjC7B2ivJKLiQ=s120-c-rp-mo-br100',null,4.0,null,'2026-07-13','Ci9DQUlRQUNvZENodHljRjlvT2taSWFtVklNWE5JUkU1Nk5tZDVTMWN4UkhaU1RXYxAB','Obrigada, Laís Simões. Ficaremos sempre à disposição para melhor atendê-los!!!'],
      [5,0,'Júlia Cruz','https://lh3.googleusercontent.com/a-/ALV-UjWQJ--Z7f9yi-PzLUYgtO1TVg9XmLrIuxDbPOvhzN369FN2af8j=s120-c-rp-mo-br100','Atendimento eficiente, claro e cuidadoso. Medicamento chegou em ótimas condições e antes do previsto.',5.0,null,'2026-07-13','Ci9DQUlRQUNvZENodHljRjlvT21acVIwTnRXVTl6WldOWldYWnhVVE5WWjBSdU9GRRAB','Obrigada, Julia Cruz!!! Ficaremos sempre à disposição para melhor atendê-los!!'],
      [6,0,'Flavia Fla','https://lh3.googleusercontent.com/a/ACg8ocJMCUMJs2wZxaQ9arrVeOCCZYFvVzCXP2_U8pTRwpWOXHHlCg=s120-c-rp-mo-br100','Atendimento excelente. Pós venda perfeito tbm. Parabéns e obrigada.',5.0,null,'2026-07-10','Ci9DQUlRQUNvZENodHljRjlvT2tNd2FEbEhOMG8yVjFSbGRHeE1UazEzWHpneFVXYxAB','Obrigada, Flávia! Ficaremos sempre à disposição para melhor atendê-los!!!'],
      [7,0,'Gilson divino Lima Dos Santos','https://lh3.googleusercontent.com/a/ACg8ocL3iv7DPio7-wW-clGL5h67NuLqGp2cnIBXalYMW3WWZ8tavQ=s120-c-rp-mo-br100',null,5.0,null,'2026-07-07','Ci9DQUlRQUNvZENodHljRjlvT2pkRk1taGhMV00xVG1kb2VWVTFZMHcyY2tKRGIxRRAB','Obrigada, Gilson! Ficaremos à disposição para melhor atendê-los sempre!!'],
      [8,0,'Kamila Manes','https://lh3.googleusercontent.com/a-/ALV-UjUVXgbnE0psSQKTvnJmpPkJuYWd08Ki5oIxYAQl43nwX4gatbNJ=s120-c-rp-mo-br100','Atendimento bom, empresa responsável, chegou certinho e antes do prazo final',5.0,null,'2026-07-07','Ci9DQUlRQUNvZENodHljRjlvT2pkMmQzTjBhQzFaTVd0TFNGOTZabUZKY0VsNGVYYxAB','Obrigada, Kamila Manes! Ficaremos à disposição para melhor atendê-los sempre!!!'],
      [9,0,'Leidiane Souza Almeida','https://lh3.googleusercontent.com/a/ACg8ocJukPqM9fiPTdYRMSHGVrr2OlQOGWdCGG365_EEgRVWAd0kwQ=s120-c-rp-mo-br100','Excelente experiência! A entrega foi rápida, o produto chegou dentro do prazo, muito bem embalado e exatamente conforme o anunciado. A adrenalina autoinjetável veio em perfeitas condições. Recomendo a empresa pela agilidade e a qualidade no atendimento.',5.0,null,'2026-07-07','Ci9DQUlRQUNvZENodHljRjlvT2xoSWVYYzVSWEYxZEVNeVpXbGxORTFrWVdkb2FIYxAB','Obrigada, Leidiane! Ficaremos sempre à disposição para melhor atendê-los!!!'],
      [10,0,'Tatiana Fonseca','https://lh3.googleusercontent.com/a-/ALV-UjX8QD63XWtcqPbEY6TiPsor0o2QMKlNUe2QeAjIf7faaOVwTlHH=s120-c-rp-mo-br100','Excelente atendimento e prazo de entrega',5.0,null,'2026-07-07','Ci9DQUlRQUNvZENodHljRjlvT2sxMVZHbGtkRjh5Y1VoRExVVnVkblJoTXpSTU9HYxAB','Obrigada, Tatiana Fonseca!! Ficaremos sempre à disposição para melhor atendê-los!!'],
    ];
    foreach ($reviews as $r) {
        $wpdb->replace($table,[
            'id'=>$r[0],'hidden'=>$r[1],'user'=>$r[2],'user_photo'=>$r[3],'text'=>$r[4],
            'rating'=>$r[5],'highlight'=>$r[6],'date'=>$r[7],'reviewId'=>$r[8],'reply'=>$r[9]
        ]);
    }

    $css = wp_remote_get('https://cdn.trustindex.io/assets/widget-presetted-css/v2/5-drop-shadow.css',['timeout'=>20]);
    if (!is_wp_error($css) && wp_remote_retrieve_response_code($css) === 200) {
        $body = (string) wp_remote_retrieve_body($css);
        $body = str_replace('../../../assets','https://cdn.trustindex.io/assets',$body);
        $body = str_replace(".ti-widget[data-layout-id='5'][data-set-id='drop-shadow']",'.ti-widget.ti-goog',$body);
        update_option('trustindex-google-css-content',$body,false);
    }

    $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    if ($count >= 10) update_option('ga_trustindex_restored_20260823','1',false);
}, 20);

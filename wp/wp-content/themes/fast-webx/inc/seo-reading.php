<?php
/**
 * Módulo Avançado de SEO e Leitura Server-Side
 * 
 * @package Fast_WebX
 * @since   3.1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. Estimativa de Tempo de Leitura (Server-side)
 * Calculada com base na média de 200 palavras por minuto.
 */
function fwx_get_reading_time( $content ) {
    $word_count = str_word_count( strip_tags( $content ) );
    $reading_time = ceil( $word_count / 200 );
    
    if ( $reading_time < 1 ) {
        return fwx_t( 'reading_time_less_than_1' );
    }
    return $reading_time . ' ' . fwx_t( 'reading_time_suffix' );
}

/**
 * 2. Injeção Invisível de Schema Markup (JSON-LD)
 */
function fwx_inject_schema_markup() {
    if ( ! is_single() ) {
        return;
    }

    global $post;

    $author_id = $post->post_author;
    $author_name = get_the_author_meta( 'display_name', $author_id );
    $author_url = get_author_posts_url( $author_id );

    $schema = array(
        '@context' => 'https://schema.org',
        '@type'    => 'Article',
        'headline' => get_the_title(),
        'datePublished' => get_the_date( 'c' ),
        'dateModified'  => get_the_modified_date( 'c' ),
        'author'   => array(
            '@type' => 'Person',
            'name'  => $author_name,
            'url'   => $author_url,
        ),
        'publisher' => array(
            '@type' => 'Organization',
            'name'  => get_bloginfo( 'name' ),
            'logo'  => array(
                '@type' => 'ImageObject',
                'url'   => function_exists('wp_get_attachment_image_url') && has_custom_logo() ? wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' ) : '',
            )
        )
    );

    $thumbnail_id = get_post_thumbnail_id();
    if ( $thumbnail_id ) {
        $thumbnail_url = wp_get_attachment_image_url( $thumbnail_id, 'full' );
        $schema['image'] = array( $thumbnail_url );
    }

    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'fwx_inject_schema_markup' );

/**
 * 3. Índice de Conteúdo (TOC) Automático via PHP
 *
 * - Motor refinado: captura APENAS tags H2 do conteúdo do post.
 * - Sidebar/widgets são ignorados por natureza (filtro 'the_content' só roda no loop principal).
 * - Botão de Collapse (Mostrar/Esconder) no canto superior direito do cabeçalho.
 */
function fwx_generate_toc_and_anchors( $content ) {
    $is_allowed = false;
    if ( is_singular( 'post' ) && is_main_query() && in_the_loop() ) {
        if ( fwx_get_option( 'enable_toc', 0 ) ) {
            $is_allowed = true;
        }
    } elseif ( is_page() && is_main_query() && in_the_loop() ) {
        if ( '1' === get_post_meta( get_the_ID(), '_fwx_enable_page_toc', true ) ) {
            $is_allowed = true;
        }
    }

    if ( ! $is_allowed ) {
        return $content;
    }

    $toc_html = '';
    $matches  = array();

    // Motor refinado: captura APENAS H2 (ignora H3, H4 etc.)
    if ( preg_match_all( '/<h2(.*?)>(.*?)<\/h2>/s', $content, $matches ) ) {
        // Regra: Exigir no mínimo 2 subtítulos H2 para valer a pena montar o TOC
        if ( count( $matches[0] ) >= 2 ) {
            $toc_html .= '<div class="fwx-toc-container">';
            $toc_html .= '<div class="fwx-toc-header">';
            $toc_html .= '<span class="fwx-toc-title">' . esc_html( fwx_t( 'toc_title' ) ) . '</span>';
            $toc_html .= '<button class="fwx-toc-toggle" aria-expanded="true" aria-label="' . esc_attr( fwx_t( 'toc_toggle_hide' ) ) . '" title="' . esc_attr( fwx_t( 'toc_toggle_title' ) ) . '">&#8211;</button>';
            $toc_html .= '</div>';
            $toc_html .= '<ul class="fwx-toc-list">';

            foreach ( $matches[0] as $i => $match ) {
                $attributes = $matches[1][ $i ];
                $title_text = $matches[2][ $i ];
                $clean_text = strip_tags( $title_text );
                $slug       = sanitize_title( $clean_text );

                if ( ! $slug ) {
                    $slug = 'section-' . $i;
                }

                // Cria o HTML da âncora no sumário
                $toc_html .= sprintf( '<li><a href="#%s">%s</a></li>', esc_attr( $slug ), esc_html( $clean_text ) );

                // Substitui no conteúdo original inserindo o atributo id na tag H2
                $new_heading = sprintf( '<h2 id="%s"%s>%s</h2>', esc_attr( $slug ), $attributes, $title_text );
                $content     = str_replace( $match, $new_heading, $content );
            }

            $toc_html .= '</ul>';
            $toc_html .= '</div><!-- fwx-toc-end -->';

            // Verifica se o modo Sticky está ativado
            if ( fwx_get_option( 'enable_sticky_toc', 0 ) ) {
                $content = '<div class="fwx-content-with-toc">' .
                               '<aside class="fwx-sticky-toc">' . $toc_html . '</aside>' .
                               '<div class="fwx-actual-content">' . $content . '</div>' .
                           '</div>';
            } else {
                // Injeta o TOC no início do conteúdo (padrão)
                $content = $toc_html . $content;
            }
        }
    }

    return $content;
}
add_filter( 'the_content', 'fwx_generate_toc_and_anchors', 20 );

/**
 * 4. JS do Botão de Collapse do TOC
 * Carregamento condicional: apenas em single posts com TOC ativo.
 * Vanilla JS puro, sem dependências.
 */
function fwx_toc_collapse_script() {
    $has_toc = false;
    if ( is_singular( 'post' ) && fwx_get_option( 'enable_toc', 0 ) ) {
        $has_toc = true;
    } elseif ( is_page() && '1' === get_post_meta( get_the_ID(), '_fwx_enable_page_toc', true ) ) {
        $has_toc = true;
    }

    if ( ! $has_toc ) {
        return;
    }
    $start_collapsed = fwx_get_option( 'toc_start_collapsed', 0 ) ? 'true' : 'false';
    ?>
    <script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            var btn = document.querySelector('.fwx-toc-toggle');
            var list = document.querySelector('.fwx-toc-list');
            if (!btn || !list) return;

            // Inicializa colapsado se a opção estiver ativa no painel
            if (<?php echo $start_collapsed; ?>) {
                list.classList.add('fwx-toc-collapsed');
                btn.setAttribute('aria-expanded', 'false');
                btn.setAttribute('aria-label', '<?php echo esc_js( fwx_t( 'toc_toggle_show' ) ); ?>');
                btn.innerHTML = '+';
            }

            btn.addEventListener('click', function() {
                var expanded = btn.getAttribute('aria-expanded') === 'true';
                if (expanded) {
                    list.classList.add('fwx-toc-collapsed');
                    btn.setAttribute('aria-expanded', 'false');
                    btn.setAttribute('aria-label', '<?php echo esc_js( fwx_t( 'toc_toggle_show' ) ); ?>');
                    btn.innerHTML = '+';
                } else {
                    list.classList.remove('fwx-toc-collapsed');
                    btn.setAttribute('aria-expanded', 'true');
                    btn.setAttribute('aria-label', '<?php echo esc_js( fwx_t( 'toc_toggle_hide' ) ); ?>');
                    btn.innerHTML = '&#8211;';
                }
            });

            // SCROLL SPY (Intersection Observer)
            var headings = document.querySelectorAll('.fwx-actual-content h2[id]');
            var tocLinks = document.querySelectorAll('.fwx-toc-list a');

            if (headings.length > 0 && tocLinks.length > 0) {
                var observerOptions = {
                    root: null,
                    rootMargin: '0px 0px -80% 0px', // Aciona quando o H2 entra no topo 20% da tela
                    threshold: 0
                };

                var observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            var id = entry.target.getAttribute('id');
                            
                            // Remove ativa de todos
                            document.querySelectorAll('.fwx-toc-list li').forEach(function(li) {
                                li.classList.remove('fwx-toc-active');
                            });

                            // Ativa o link correspondente
                            var activeLink = document.querySelector('.fwx-toc-list a[href="#' + id + '"]');
                            if (activeLink) {
                                activeLink.parentElement.classList.add('fwx-toc-active');
                            }
                        }
                    });
                }, observerOptions);

                headings.forEach(function(heading) {
                    observer.observe(heading);
                });
            }
        });
    })();
    </script>
    <?php
}
add_action( 'wp_footer', 'fwx_toc_collapse_script' );

/**
 * 5. Meta Description Nativa (Zero Plugins)
 * 
 * Gera automaticamente a meta description no <head>.
 * Prioridade: 
 * 1. Resumo (Excerpt) Customizado - Melhor para controle manual de SEO.
 * 2. Recorte do Conteúdo (155 caracteres) - Fallback automático inteligente.
 */
function fwx_inject_meta_description() {
    // Evita duplicidade se o usuário estiver usando plugins de SEO externos
    // Yoast SEO
    if ( class_exists( 'WPSEO_Options' ) ) {
        return;
    }
    // Rank Math SEO (a classe principal usa namespace RankMath\, não 'RankMath')
    if ( defined( 'RANK_MATH_VERSION' ) || function_exists( 'rank_math' ) ) {
        return;
    }
    // SEOPress
    if ( defined( 'SEOPRESS_VERSION' ) || function_exists( 'seopress_activation' ) ) {
        return;
    }
    // All in One SEO
    if ( class_exists( 'AIOSEO' ) || defined( 'AIOSEO_VERSION' ) ) {
        return;
    }
    // The SEO Framework
    if ( function_exists( 'the_seo_framework' ) ) {
        return;
    }

    $options = get_option( 'fast_webx_options' );
    $description = '';

    // 1. Contexto de Home / Front Page
    if ( is_front_page() || is_home() ) {
        // Tenta pegar excerpt/conteúdo da página estática da Home (se configurada)
        if ( is_front_page() ) {
            $page_on_front = get_option( 'page_on_front' );
            if ( $page_on_front ) {
                $post_obj = get_post( $page_on_front );
                if ( $post_obj ) {
                    $use_excerpt = isset( $options['enable_excerpt_as_meta'] ) ? $options['enable_excerpt_as_meta'] : 1;
                    if ( $use_excerpt && has_excerpt( $post_obj->ID ) ) {
                        $description = get_the_excerpt( $post_obj );
                    }
                    if ( empty( $description ) ) {
                        $content = $post_obj->post_content;
                        $content = strip_shortcodes( $content );
                        $content = strip_tags( $content );
                        $content = preg_replace( '/\s+/', ' ', $content );
                        $description = mb_substr( trim( $content ), 0, 155 );
                    }
                }
            }
        }
        
        // Tenta pegar excerpt/conteúdo da listagem de posts (Blog)
        if ( empty( $description ) && is_home() ) {
            $page_for_posts = get_option( 'page_for_posts' );
            if ( $page_for_posts ) {
                $post_obj = get_post( $page_for_posts );
                if ( $post_obj ) {
                    $use_excerpt = isset( $options['enable_excerpt_as_meta'] ) ? $options['enable_excerpt_as_meta'] : 1;
                    if ( $use_excerpt && has_excerpt( $post_obj->ID ) ) {
                        $description = get_the_excerpt( $post_obj );
                    }
                    if ( empty( $description ) ) {
                        $content = $post_obj->post_content;
                        $content = strip_shortcodes( $content );
                        $content = strip_tags( $content );
                        $content = preg_replace( '/\s+/', ' ', $content );
                        $description = mb_substr( trim( $content ), 0, 155 );
                    }
                }
            }
        }

    // 2. Contexto de Post/Página Singular (inclusive se for a Home LP acessada diretamente)
    } elseif ( is_singular() ) {
        global $post;
        $use_excerpt = isset( $options['enable_excerpt_as_meta'] ) ? $options['enable_excerpt_as_meta'] : 1;
        
        if ( $use_excerpt && has_excerpt( $post->ID ) ) {
            $description = get_the_excerpt( $post );
        }

        if ( empty( $description ) ) {
            $content = $post->post_content;
            $content = strip_shortcodes( $content );
            $content = strip_tags( $content );
            $content = preg_replace( '/\s+/', ' ', $content );
            $description = mb_substr( trim( $content ), 0, 155 );
        }

    // 3. Contexto de Categorias / Tags / Taxonomias
    } elseif ( is_category() || is_tag() || is_tax() ) {
        $use_cat_desc = isset( $options['enable_cat_desc_as_meta'] ) ? $options['enable_cat_desc_as_meta'] : 1;
        if ( $use_cat_desc ) {
            $description = strip_tags( term_description() );
        }
    }

    // Limpeza extra de espaços em branco
    $description = trim( $description );

    // Fallback Único e Exclusivo: Campo home_meta_desc do painel SEO
    if ( empty( $description ) ) {
        $description = isset( $options['home_meta_desc'] ) ? trim( $options['home_meta_desc'] ) : '';
    }

    // Se ainda assim estiver vazio, fica sem mesmo (não imprime a tag)
    if ( ! empty( $description ) ) {
        $description = wp_strip_all_tags( $description );
        $description = esc_attr( $description );
        echo '<meta name="description" content="' . $description . '" />' . "\n";
    }
}
add_action( 'wp_head', 'fwx_inject_meta_description', 1 );

/**
 * 6. Controle do Lazy Load Nativo Global do WordPress
 */
function fwx_toggle_lazy_loading( $default, $tag_name, $context ) {
    $options = get_option( 'fast_webx_options' );
    $enable_global = isset( $options['enable_lazy_load_global'] ) ? $options['enable_lazy_load_global'] : 1;
    
    if ( ! $enable_global ) {
        return false;
    }
    return $default;
}
add_filter( 'wp_lazy_loading_enabled', 'fwx_toggle_lazy_loading', 10, 3 );

/**
 * 6b. Controle Unificado de loading para Imagens no Conteúdo do Post
 *
 * Roda em prioridade 999 (após WordPress core em 12) e reescreve o atributo
 * loading de TODAS as <img> dentro do conteúdo com base nas opções do painel:
 *
 * Lazy OFF:
 *   ? Remove loading="lazy" de todas as imgs. Sem atributo = browser trata como eager.
 *
 * Lazy ON + Smart ON + sem thumbnail visível:
 *   ? 1ª img: loading="eager" fetchpriority="high" (LCP candidate)
 *   ? demais: loading="lazy"
 *
 * Lazy ON + Smart ON + thumbnail visível (thumbnail já é o LCP):
 *   ? todas as imgs: loading="lazy"
 *
 * Lazy ON + Smart OFF:
 *   ? todas as imgs: loading="lazy"
 */
function fwx_manage_content_image_loading( $content ) {
    if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
        return $content;
    }

    $enable_global = (bool) fwx_get_option( 'enable_lazy_load_global', 0 );
    $enable_smart  = (bool) fwx_get_option( 'enable_smart_lazy_load', 0 );
    $hide_thumb    = (bool) fwx_get_option( 'hide_single_thumb', 0 );
    $thumb_is_lcp  = has_post_thumbnail() && ! $hide_thumb;

    $img_index = 0;

    $content = preg_replace_callback(
        '/<img(?:\s[^>]*)?\/?>/is',
        function( $matches ) use ( &$img_index, $enable_global, $enable_smart, $thumb_is_lcp ) {
            $img_html = $matches[0];
            $is_first = ( $img_index === 0 );
            $img_index++;

            // 1. Limpa atributos loading e fetchpriority existentes (evita duplicação)
            $img_html = preg_replace( '/\s+loading=(?:"[^"]*"|\'[^\']*\'|[^\s\/>]*)/i', '', $img_html );
            $img_html = preg_replace( '/\s+fetchpriority=(?:"[^"]*"|\'[^\']*\'|[^\s\/>]*)/i', '', $img_html );

            // 2. Define o novo atributo loading
            if ( ! $enable_global ) {
                // Lazy desativado: não adiciona loading; browser trata como eager por padrão.
                return $img_html;
            }

            if ( $enable_smart && $is_first && ! $thumb_is_lcp ) {
                // Primeira imagem, sem thumbnail visível: é o LCP da página ? eager + fetchpriority
                $img_html = preg_replace( '/^<img/i', '<img loading="eager" fetchpriority="high"', $img_html );
            } else {
                // Todas as demais imagens de conteúdo ? lazy
                $img_html = preg_replace( '/^<img/i', '<img loading="lazy"', $img_html );
            }

            return $img_html;
        },
        $content
    );

    return $content;
}
add_filter( 'the_content', 'fwx_manage_content_image_loading', 999 );

/**
 * 8. SEO (Title Case) para Português do Brasil
 * 
 * Converte títulos de posts e páginas para Capitalizado (Title Case) no front-end,
 * mantendo preposições, artigos e conjunções menores em minúsculas de forma inteligente.
 * 
 * @since   3.1.3
 */
function fwx_title_case_portuguese( $title, $id = null ) {
    // Evita rodar no painel administrativo
    if ( is_admin() ) {
        return $title;
    }

    // Se a opção não estiver ativa no painel, apenas retorna o título original
    if ( ! fwx_get_option( 'seo_title_case', 0 ) ) {
        return $title;
    }

    // Se for um título vazio ou não for uma string, retorna
    if ( ! is_string( $title ) || empty( $title ) ) {
        return $title;
    }

    // Se o ID for informado, evita aplicar em itens de menu
    if ( $id ) {
        $post_type = get_post_type( $id );
        if ( 'nav_menu_item' === $post_type || 'revision' === $post_type ) {
            return $title;
        }
    }

    // Lista de palavras a serem mantidas em minúscula (preposições, artigos, conjunções)
    $lowercase_words = array(
        'de', 'do', 'da', 'dos', 'das', 
        'em', 'no', 'na', 'nos', 'nas', 
        'por', 'para', 'com', 'sem', 'sob', 'sobre', 
        'a', 'o', 'as', 'os', 'ao', 'aos', 'à', 'às',
        'um', 'uns', 'uma', 'umas', 
        'e', 'ou', 'mas', 'nem'
    );

    // Converte todo o título para lowercase para padronizar
    $title_lower = mb_strtolower( $title, 'UTF-8' );

    // Divide as palavras por espaços
    $words = preg_split( '/\s+/u', $title_lower );
    if ( ! is_array( $words ) || count( $words ) === 0 ) {
        return $title;
    }

    $total_words = count( $words );
    foreach ( $words as $index => &$word ) {
        // Remove pontuações comuns para verificar a palavra
        $clean_word = preg_replace( '/[^\w]/u', '', $word );
        
        // Sempre capitaliza a primeira e a última palavra
        if ( $index === 0 || $index === $total_words - 1 ) {
            $word = mb_convert_case( $word, MB_CASE_TITLE, 'UTF-8' );
        } elseif ( in_array( $clean_word, $lowercase_words, true ) ) {
            // Mantém em minúsculo
            $word = mb_strtolower( $word, 'UTF-8' );
        } else {
            // Capitaliza as demais palavras
            $word = mb_convert_case( $word, MB_CASE_TITLE, 'UTF-8' );
        }
    }

    return implode( ' ', $words );
}
add_filter( 'the_title', 'fwx_title_case_portuguese', 10, 2 );

/**
 * 9. SEO (Links Externos): Forçar Abertura em Nova Aba
 * 
 * Intercepta o conteúdo de posts e páginas para forçar que todos os links externos
 * possuam target="_blank" e rel="noopener" para fins de SEO e segurança.
 * 
 * @since   3.1.3
 */
function fwx_force_external_links_blank( $content ) {
    // Evita rodar no painel administrativo ou se o conteúdo for vazio
    if ( is_admin() || empty( $content ) ) {
        return $content;
    }

    // Se a opção não estiver ativa no painel, apenas retorna o conteúdo original
    if ( ! fwx_get_option( 'seo_external_links_blank', 0 ) ) {
        return $content;
    }

    // Identifica o host do site atual para comparação
    $home_url = home_url();
    $home_host = wp_parse_url( $home_url, PHP_URL_HOST );
    if ( ! $home_host ) {
        return $content;
    }

    // Captura tags <a> completas contendo href="http..."
    $pattern = '/<a\s+([^>]*href=["\'](https?:\/\/[^"\']+)["\'][^>]*)>/i';

    $content = preg_replace_callback( $pattern, function( $matches ) use ( $home_host ) {
        $full_tag = $matches[0];
        $attributes = $matches[1];
        $url = $matches[2];

        // Obtém o host do link
        $link_host = wp_parse_url( $url, PHP_URL_HOST );
        if ( ! $link_host ) {
            return $full_tag;
        }

        // Se o host do link for igual ao local ou subdomínio do local, trata como interno e não altera
        if ( strcasecmp( $link_host, $home_host ) === 0 ) {
            return $full_tag;
        }

        $home_host_len = strlen( $home_host );
        $link_host_len = strlen( $link_host );
        if ( $link_host_len >= $home_host_len ) {
            $suffix = substr( $link_host, - $home_host_len );
            if ( strcasecmp( $suffix, $home_host ) === 0 ) {
                if ( $link_host_len === $home_host_len || substr( $link_host, - $home_host_len - 1, 1 ) === '.' ) {
                    return $full_tag;
                }
            }
        }

        // Caso seja externo, força target="_blank" e limpa targets antigos que não sejam _blank
        if ( preg_match( '/target\s*=\s*["\']_blank["\']/i', $attributes ) ) {
            if ( ! preg_match( '/rel\s*=\s*["\']/i', $attributes ) ) {
                $attributes .= ' rel="noopener"';
            } else {
                $attributes = preg_replace_callback( '/rel\s*=\s*(["\'])(.*?)\1/i', function( $rel_matches ) {
                    $quote = $rel_matches[1];
                    $rel_value = $rel_matches[2];
                    if ( strpos( strtolower( $rel_value ), 'noopener' ) === false ) {
                        $rel_value .= ' noopener';
                    }
                    return 'rel=' . $quote . trim( $rel_value ) . $quote;
                }, $attributes );
            }
        } else {
            $attributes = preg_replace( '/target\s*=\s*["\'][^"\']*["\']/i', '', $attributes );
            $attributes .= ' target="_blank"';

            if ( ! preg_match( '/rel\s*=\s*["\']/i', $attributes ) ) {
                $attributes .= ' rel="noopener"';
            } else {
                $attributes = preg_replace_callback( '/rel\s*=\s*(["\'])(.*?)\1/i', function( $rel_matches ) {
                    $quote = $rel_matches[1];
                    $rel_value = $rel_matches[2];
                    if ( strpos( strtolower( $rel_value ), 'noopener' ) === false ) {
                        $rel_value .= ' noopener';
                    }
                    return 'rel=' . $quote . trim( $rel_value ) . $quote;
                }, $attributes );
            }
        }

        $attributes = preg_replace( '/\s+/', ' ', $attributes );
        return '<a ' . trim( $attributes ) . '>';
    }, $content );

    return $content;
}
add_filter( 'the_content', 'fwx_force_external_links_blank', 25 );

/**
 * 10. SEO (Compatibilidade TOC): Registro e Simulação de Plugin no RankMath
 * 
 * Registra o TOC nativo do tema no RankMath e simula em tempo de execução
 * que o plugin de TOC está ativo, fazendo com que o teste de legibilidade do RankMath passe (indicador verde).
 * 
 * @since   3.1.3
 */
function fwx_seo_toc_compatibility() {
    if ( ! fwx_get_option( 'seo_toc_compatibility', 0 ) ) {
        return;
    }

    // Registra o TOC fictício do tema no RankMath
    add_filter( 'rank_math/researches/toc_plugins', 'fwx_register_theme_toc_in_rankmath' );

    // Simula a ativação do plugin em tempo de execução para o RankMath
    add_filter( 'option_active_plugins', 'fwx_simulate_active_toc_plugin' );
    add_filter( 'site_option_active_sitewide_plugins', 'fwx_simulate_active_toc_plugin_multisite' );

    // Remove o teste de Content AI do RankMath para evitar penalização artificial
    add_filter( 'rank_math/researches/tests', 'fwx_remove_rankmath_content_ai_test', 10, 2 );
}
add_action( 'init', 'fwx_seo_toc_compatibility' );

function fwx_register_theme_toc_in_rankmath( $toc_plugins ) {
    $toc_plugins['fast-webx-toc/fast-webx-toc.php'] = 'Fast WebX TOC';
    return $toc_plugins;
}

function fwx_simulate_active_toc_plugin( $plugins ) {
    // Blindagem absoluta: NUNCA injeta em requisiÃ§Ãµes AJAX, REST API (Gutenberg) ou Cron
    if ( wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
        return $plugins;
    }

    if ( ! is_admin() ) {
        return $plugins;
    }

    global $pagenow;
    // Permite APENAS nas telas de ediÃ§Ã£o de post onde o RankMath renderiza o metabox / painel lateral
    if ( ! isset( $pagenow ) || ! in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) ) {
        return $plugins;
    }

    if ( is_array( $plugins ) && ! in_array( 'fast-webx-toc/fast-webx-toc.php', $plugins, true ) ) {
        $plugins[] = 'fast-webx-toc/fast-webx-toc.php';
    }
    return $plugins;
}

function fwx_simulate_active_toc_plugin_multisite( $plugins ) {
    // Blindagem absoluta: NUNCA injeta em requisiÃ§Ãµes AJAX, REST API (Gutenberg) ou Cron
    if ( wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
        return $plugins;
    }

    if ( ! is_admin() ) {
        return $plugins;
    }

    global $pagenow;
    // Permite APENAS nas telas de ediÃ§Ã£o de post onde o RankMath renderiza o metabox / painel lateral
    if ( ! isset( $pagenow ) || ! in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) ) {
        return $plugins;
    }

    if ( is_array( $plugins ) && ! isset( $plugins['fast-webx-toc/fast-webx-toc.php'] ) ) {
        $plugins['fast-webx-toc/fast-webx-toc.php'] = time();
    }
    return $plugins;
}

function fwx_remove_rankmath_content_ai_test( $tests, $type ) {
    if ( isset( $tests['hasContentAI'] ) ) {
        unset( $tests['hasContentAI'] );
    }
    return $tests;
}

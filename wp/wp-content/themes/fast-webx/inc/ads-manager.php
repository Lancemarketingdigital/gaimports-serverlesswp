<?php
/**
 * Módulo Avançado de Ads e Scripts (Zero CLS, Zero TBT)
 * 
 * @package Fast_WebX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. Módulo Stealth: Controle de Scripts Dinâmicos (Head e Footer)
 */
function fwx_output_head_scripts() {
    // Verifica se scripts globais foram desativados nesta página
    if ( is_singular() && '1' === get_post_meta( get_queried_object_id(), '_fwx_disable_global_scripts', true ) ) {
        return;
    }

    $head_scripts = fwx_get_option( 'head_scripts', '' );

    if ( empty( $head_scripts ) ) return;

    // Verifica se o Stealth Mode deve ser aplicado (respeita toggle global + por página + Blank)
    $apply_stealth = ( is_singular() && function_exists( 'fwx_should_apply_stealth_for_page' ) )
        ? fwx_should_apply_stealth_for_page( get_queried_object_id() )
        : (bool) fwx_get_option( 'stealth_mode', 0 );

    if ( $apply_stealth ) {
        // Modo Stealth: Transforma os scripts em inertes e carrega um carregador de interação.
        $head_scripts = str_replace( 'type="text/javascript"', '', $head_scripts );
        $head_scripts = str_replace( 'type=\'text/javascript\'', '', $head_scripts );
        $head_scripts = str_replace( '<script', '<script type="text/fwx-delayed-script"', $head_scripts );
        echo $head_scripts . "\n";
        fwx_print_stealth_loader();
    } else {
        echo $head_scripts . "\n";
    }
}
add_action( 'wp_head', 'fwx_output_head_scripts', 99 );

function fwx_output_footer_scripts() {
    // Verifica se scripts globais foram desativados nesta página
    if ( is_singular() && '1' === get_post_meta( get_queried_object_id(), '_fwx_disable_global_scripts', true ) ) {
        return;
    }

    $footer_scripts = fwx_get_option( 'footer_scripts', '' );

    if ( empty( $footer_scripts ) ) return;

    $apply_stealth = ( is_singular() && function_exists( 'fwx_should_apply_stealth_for_page' ) )
        ? fwx_should_apply_stealth_for_page( get_queried_object_id() )
        : (bool) fwx_get_option( 'stealth_mode', 0 );

    if ( $apply_stealth ) {
        $footer_scripts = str_replace( 'type="text/javascript"', '', $footer_scripts );
        $footer_scripts = str_replace( 'type=\'text/javascript\'', '', $footer_scripts );
        $footer_scripts = str_replace( '<script', '<script type="text/fwx-delayed-script"', $footer_scripts );
        echo $footer_scripts . "\n";
        fwx_print_stealth_loader(); // Garante o loader mesmo sem head_scripts
    } else {
        echo $footer_scripts . "\n";
    }
}
add_action( 'wp_footer', 'fwx_output_footer_scripts', 99 );

function fwx_output_body_scripts() {
    // Verifica se scripts globais foram desativados nesta página
    if ( is_singular() && '1' === get_post_meta( get_queried_object_id(), '_fwx_disable_global_scripts', true ) ) {
        return;
    }

    $body_scripts = fwx_get_option( 'body_scripts', '' );

    if ( empty( $body_scripts ) ) return;

    $apply_stealth = ( is_singular() && function_exists( 'fwx_should_apply_stealth_for_page' ) )
        ? fwx_should_apply_stealth_for_page( get_queried_object_id() )
        : (bool) fwx_get_option( 'stealth_mode', 0 );

    if ( $apply_stealth ) {
        $body_scripts = str_replace( 'type="text/javascript"', '', $body_scripts );
        $body_scripts = str_replace( 'type=\'text/javascript\'', '', $body_scripts );
        $body_scripts = str_replace( '<script', '<script type="text/fwx-delayed-script"', $body_scripts );
        echo $body_scripts . "\n";
        fwx_print_stealth_loader(); // Garante o loader mesmo sem head_scripts
    } else {
        echo $body_scripts . "\n";
    }
}
add_action( 'wp_body_open', 'fwx_output_body_scripts', 1 );

/**
 * O Gatilho do Stealth Mode
 * Esse JS puramente vanilla observa a 1ª interação do usuário (scroll, mouse, touch) e desperta os scripts.
 */
function fwx_print_stealth_loader() {
    static $stealth_loaded = false;
    if ( $stealth_loaded ) return;
    $stealth_loaded = true;
    ?>
    <script>
    (function() {
        let fwxScriptsFired = false;
        function fwxFireDelayedScripts() {
            if (fwxScriptsFired) return;
            fwxScriptsFired = true;
            
            // Remove os event listeners
            window.removeEventListener('scroll', fwxFireDelayedScripts, {passive: true});
            window.removeEventListener('mousemove', fwxFireDelayedScripts, {passive: true});
            window.removeEventListener('touchstart', fwxFireDelayedScripts, {passive: true});
            window.removeEventListener('keydown', fwxFireDelayedScripts, {passive: true});
            
            // Captura e acorda os scripts de forma robusta
            let delayedScripts = document.querySelectorAll('script[type="text/fwx-delayed-script"]');
            delayedScripts.forEach(function(s) {
                try {
                    let newScript = document.createElement('script');
                    
                    // Copia todos os atributos (exceto o type)
                    Array.from(s.attributes).forEach(attr => {
                        if (attr.name !== 'type') {
                            newScript.setAttribute(attr.name, attr.value);
                        }
                    });

                    // Copia o conteúdo interno de forma segura
                    if (s.src) {
                        newScript.src = s.src;
                    } else if (s.textContent) {
                        newScript.textContent = s.textContent;
                    } else if (s.innerText) {
                        newScript.innerText = s.innerText;
                    }

                    // Substitui o script inerte pelo real
                    if (s.parentNode) {
                        s.parentNode.replaceChild(newScript, s);
                    }
                } catch (e) {
                    console.warn("Fast WebX: Erro ao acordar um script específico.", e);
                }
            });
            console.log("Fast WebX: Modo Stealth acordado. Scripts disparados.");
        }
        
        window.addEventListener('scroll', fwxFireDelayedScripts, {passive: true});
        window.addEventListener('mousemove', fwxFireDelayedScripts, {passive: true});
        window.addEventListener('touchstart', fwxFireDelayedScripts, {passive: true});
        window.addEventListener('keydown', fwxFireDelayedScripts, {passive: true});
    })();
    </script>
    <?php
}

/**
 * Garantia de loader: imprime o stealth loader no rodapé caso nenhuma das
 * funções de scripts (head/footer/body) o tenha impresso ainda.
 * Cobre casos onde todos os campos de script global estão vazios mas o
 * stealth está ativo (ex: páginas com shortcodes sem scripts globais).
 */
function fwx_ensure_stealth_loader() {
    // Se stealth não está ativo globalmente, nada a fazer
    if ( ! fwx_get_option( 'stealth_mode', 0 ) ) {
        return;
    }

    // Se for página singular, respeita os toggles por página
    if ( is_singular() && function_exists( 'fwx_should_apply_stealth_for_page' ) ) {
        if ( ! fwx_should_apply_stealth_for_page( get_queried_object_id() ) ) {
            return;
        }
    }

    // A função usa static internamente, então é seguro chamá-la aqui:
    // só imprimirá o JS se ainda não foi impresso por outra função.
    fwx_print_stealth_loader();
}
add_action( 'wp_footer', 'fwx_ensure_stealth_loader', 999 );

/**
 * 2. Injetor Automático de Anúncios no Texto (Regex)
 * Evitando CLS com classes predefinidas.
 */
/**
 * Sanitiza o código de anúncio do AdSense para impedir o breakout mobile.
 * O atributo data-full-width-responsive="true" faz o Google aplicar
 * margin-left negativo e width:100vw, quebrando todo o layout.
 * Cobre todas as variações de encoding que o WordPress pode aplicar.
 */
function fwx_sanitize_ad_code( $ad_code ) {
    // Aspas duplas normais
    $ad_code = str_replace( 'data-full-width-responsive="true"', 'data-full-width-responsive="false"', $ad_code );
    // Aspas simples
    $ad_code = str_replace( "data-full-width-responsive='true'", "data-full-width-responsive='false'", $ad_code );
    // WordPress pode salvar com HTML entities
    $ad_code = str_replace( 'data-full-width-responsive=&quot;true&quot;', 'data-full-width-responsive=&quot;false&quot;', $ad_code );
    // Aspas curvas (smart quotes) do editor visual
    $ad_code = str_replace( 'data-full-width-responsive=&#8220;true&#8221;', 'data-full-width-responsive=&#8220;false&#8221;', $ad_code );
    return $ad_code;
}

function fwx_inject_ad_block( $content ) {
    if ( ! is_single() || ! in_the_loop() || ! is_main_query() ) {
        return $content;
    }

    if ( is_singular( 'fwx_produto' ) && fwx_get_option( 'disable_ads_loja_single', 0 ) ) {
        return $content;
    }

    $ads_html = array();

    // Processar os 3 blocos e inserir placeholders neutros
    for ( $i = 1; $i <= 3; $i++ ) {
        $ad_content = fwx_get_option( 'ad_block_content_' . $i, '' );
        $injection_point = fwx_get_option( 'ad_injection_point_' . $i, 'none' );

        if ( empty( $ad_content ) || 'none' === $injection_point ) {
            continue;
        }

        $ad_content = do_shortcode( $ad_content );
        $ad_content = fwx_sanitize_ad_code( $ad_content );
        $ad_html = '<div class="fwx-auto-ad-container fwx-ad-block-' . $i . '" style="overflow:hidden;max-width:100%;box-sizing:border-box;">' . $ad_content . '</div>';
        
        $placeholder = '<!--FWX_AD_PLACEHOLDER_' . $i . '-->';
        $ads_html[$placeholder] = $ad_html;

        if ( 'before_content' === $injection_point ) {
            $content = $placeholder . $content;
        }
        elseif ( 'after_toc' === $injection_point ) {
            if ( strpos( $content, '<!-- fwx-toc-end -->' ) !== false ) {
                $content = str_replace( '<!-- fwx-toc-end -->', '<!-- fwx-toc-end -->' . $placeholder, $content );
            } else {
                $content = $placeholder . $content; // Fallback para o topo se não houver TOC
            }
        }
        elseif ( strpos( $injection_point, 'after_p_' ) === 0 ) {
            $par_num = intval( str_replace( 'after_p_', '', $injection_point ) );
            $paragraphs = explode( '</p>', $content );
            
            if ( count( $paragraphs ) >= $par_num ) {
                $inserted = false;
                foreach ( $paragraphs as $index => $paragraph ) {
                    if ( trim( $paragraph ) ) {
                        $paragraphs[$index] .= '</p>';
                        if ( $index + 1 == $par_num ) {
                            $paragraphs[$index] .= $placeholder;
                            $inserted = true;
                        }
                    }
                }
                if ( $inserted ) {
                    $content = implode( '', $paragraphs );
                }
            }
        } elseif ( strpos( $injection_point, 'before_h2_' ) === 0 ) {
            $h2_num = intval( str_replace( 'before_h2_', '', $injection_point ) );
            $matches = array();
            if ( preg_match_all( '/<h2(.*?)>(.*?)<\/h2>/s', $content, $matches, PREG_OFFSET_CAPTURE ) ) {
                if ( isset( $matches[0][$h2_num - 1] ) ) {
                    $position = $matches[0][$h2_num - 1][1];
                    $content = substr_replace( $content, $placeholder, $position, 0 );
                }
            }
        } elseif ( 'after_content' === $injection_point ) {
            $content .= $placeholder;
        }
    }

    // Substituir todos os placeholders pelo código real do anúncio no final
    if ( ! empty( $ads_html ) ) {
        $content = str_replace( array_keys( $ads_html ), array_values( $ads_html ), $content );
    }

    return $content;
}
add_filter( 'the_content', 'fwx_inject_ad_block', 30 ); // Roda depois do TOC (Prioridade 20)

/**
 * 3. Injetor no Topo Absoluto (Antes de Título/Thumb)
 */
function fwx_inject_top_ads() {
    if ( ! is_single() || ! in_the_loop() || ! is_main_query() ) {
        return;
    }

    if ( is_singular( 'fwx_produto' ) && fwx_get_option( 'disable_ads_loja_single', 0 ) ) {
        return;
    }

    for ( $i = 1; $i <= 3; $i++ ) {
        $injection_point = fwx_get_option( 'ad_injection_point_' . $i, 'none' );

        if ( 'before_everything' === $injection_point ) {
            $ad_content = fwx_get_option( 'ad_block_content_' . $i, '' );
            if ( empty( $ad_content ) ) continue;

            $ad_content = do_shortcode( $ad_content );
            $ad_content = fwx_sanitize_ad_code( $ad_content );
            echo '<div class="fwx-auto-ad-container fwx-ad-block-' . $i . ' fwx-top-ad" style="overflow:hidden;max-width:100%;box-sizing:border-box;">' . $ad_content . '</div>';
        }
    }
}
add_action( 'fwx_before_post_top', 'fwx_inject_top_ads' );

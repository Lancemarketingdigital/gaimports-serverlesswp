<?php
/**
 * Módulo de Captura de Leads e Base de Dados Customizada
 *
 * @package Fast_WebX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. Instalação e Checagem da Tabela (Isolada)
 */
function fwx_install_leads_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'fastwebx_leads';

    $db_version = get_option( 'fwx_leads_db_version' );
    $current_version = '1.2';

    if ( $db_version !== $current_version ) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            email varchar(100) NOT NULL,
            whatsapp varchar(30) DEFAULT '' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            ip_address varchar(55) DEFAULT '' NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY email (email)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        update_option( 'fwx_leads_db_version', $current_version );
    }
}
add_action( 'admin_init', 'fwx_install_leads_table' );

/**
 * 2. Filtro de Qualidade de E-mail (Anti Temp-Mail + Anti Gibberish + MX Check)
 * Zero dependências externas. 100% PHP nativo.
 */
function fwx_validate_email_quality( $email ) {
    if ( empty( $email ) ) {
        return array( true, '' );
    }
    $domain = strtolower( substr( strrchr( $email, '@' ), 1 ) );

    // --- 1. Lista negra de domínios de e-mail descartável (temp-mail) ---
    $blocked_domains = array(
        // Serviços clássicos de temp-mail
        'mailinator.com', 'guerrillamail.com', 'guerrillamail.org', 'guerrillamail.net',
        'guerrillamail.de', 'guerrillamail.biz', 'guerrillamail.info',
        'tempmail.com', 'tempmail.net', 'temp-mail.org', 'temp-mail.io', 'temp-mail.ru',
        'throwam.com', 'throwam.net', 'dispostable.com', 'yopmail.com', 'yopmail.fr',
        'yopmail.net', 'cool.fr.nf', 'jetable.fr.nf', 'nospam.ze.tc', 'nomail.xl.cx',
        'mega.zik.dj', 'speed.1s.fr', 'courriel.fr.nf', 'moncourrier.fr.nf',
        'monemail.fr.nf', 'monmail.fr.nf', 'sharklasers.com', 'guerrillamailblock.com',
        'grr.la', 'guerrillamail.info', 'spam4.me', 'trashmail.com', 'trashmail.me',
        'trashmail.net', 'trashmail.at', 'trashmail.io', 'trashmail.xyz',
        'getairmail.com', 'fakeinbox.com', 'mailnull.com', 'spamgourmet.com',
        'spamgourmet.net', 'spamgourmet.org', 'spammotel.com', 'spaml.de',
        'spaml.com', 'maildrop.cc', 'mailnesia.com', 'mailtothis.com', 'discard.email',
        'despam.it', 'spamfree24.org', 'spamfree24.de', 'spamfree24.eu',
        'spamfree24.info', 'spamfree24.net', 'spamfree.eu', 'spam.la',
        'spamspot.com', 'spamthisplease.com', 'fakemail.fr', 'fakedemail.com',
        'tempr.email', 'discard.email', 'cfl.fr', 'giveh2o.info',
        '10minutemail.com', '10minutemail.net', '10minutemail.org',
        '10minemail.com', '20minutemail.com', '20minutemail.it',
        'minutemailbox.com', 'anonbox.net', 'mytempemail.com', 'tmailinator.com',
        'mt2015.com', 'mt2016.com', 'mt2017.com', 'filzmail.com',
        'throwam.com', 'throwam.net', 'gawab.com', 'incognitomail.com',
        'incognitemail.com', 'letthemeatspam.com', 'mailnew.com', 'mailscrap.com',
        'mailsiphon.com', 'mailslapping.com', 'mailzilla.org', 'makemetheking.com',
        'meltmail.com', 'messagebeamer.de', 'mezimages.net', 'mierdamail.com',
        'mintemail.com', 'misterpinball.de', 'moncourrier.fr.nf',
        'monemail.fr.nf', 'monmail.fr.nf', 'msa.minsmail.com',
        'mt2009.com', 'mu2.net', 'mx0.wwwnew.eu', 'myfastmail.com',
        'mypacks.net', 'mypartyclip.de', 'myphantomemail.com', 'myspace-id.com',
        'myspamless.com', 'thisisnotmyrealemail.com', 'sendspamhere.com',
        'binkmail.com', 'bobmail.info', 'dayrep.com', 'deadaddress.com',
        'e4ward.com', 'emailias.com', 'emailinfive.com', 'emailismy.com',
        'emailtemporanea.com', 'emailtemporanea.net', 'emailto.de',
        'emailwarden.com', 'emailx.at.hm', 'fastacura.com', 'filzmail.com',
        'fux0ringduh.com', 'gishpuppy.com', 'grandmasmail.com', 'grandmasfood.com',
        'greensloth.com', 'haltospam.com', 'hatespam.org', 'heroin.dk',
        'hidemail.de', 'hitmail.com', 'hochsitze.com', 'hockeymail.de',
        'hu2.ru', 'humaility.com', 'icx.ro', 'ige.es', 'inoutmail.de',
        'inoutmail.eu', 'inoutmail.info', 'inoutmail.net',
        'iroid.com', 'itchyforum.com', 'jetable.com', 'jetable.net',
        'jetable.org', 'junk.to', 'kasmail.com', 'koszmail.pl',
        'kurzepost.de', 'spamfree24.org', 'lroid.com', 'lol.ovpn.to',
    );

    if ( in_array( $domain, $blocked_domains, true ) ) {
        return array( false, fwx_t( 'lead_error_temp_mail' ) );
    }

    // --- 2. Anti-Gibberish: Detecta e-mails com partes locais aleatórias ---
    $local_part = strtolower( substr( $email, 0, strpos( $email, '@' ) ) );

    // Bloqueia se tiver 5+ consoantes consecutivas (padrão de e-mail aleatório)
    if ( preg_match( '/[bcdfghjklmnpqrstvwxyz]{6,}/', $local_part ) ) {
        return array( false, fwx_t( 'lead_error_invalid_format' ) );
    }

    // Bloqueia se tiver padrão de hash/token (8+ hex chars consecutivos)
    if ( preg_match( '/[a-f0-9]{8,}/', $local_part ) && strlen( $local_part ) <= 16 ) {
        return array( false, fwx_t( 'lead_error_invalid_format' ) );
    }

    // --- 3. Verificação de MX Record (PHP nativo, sem requisição HTTP) ---
    if ( ! checkdnsrr( $domain, 'MX' ) && ! checkdnsrr( $domain, 'A' ) ) {
        return array( false, fwx_t( 'lead_error_no_mx' ) );
    }

    return array( true, '' );
}

/**
 * 3. Processador Ajax de Recebimento de Leads
 */
function fwx_process_lead_submission() {
    // Proteção Anti-spam via Honeypot (compatível com Cache) em vez de Nonce
    if ( ! empty( $_POST['fwx_hp_email'] ) ) {
        wp_send_json_error( fwx_t( 'lead_error_spam' ) );
    }

    $email    = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
    $whatsapp = isset( $_POST['whatsapp'] ) ? sanitize_text_field( $_POST['whatsapp'] ) : '';
    $lgpd     = isset( $_POST['lgpd'] ) ? intval( $_POST['lgpd'] ) : 0;

    $email_optional = fwx_get_option('lead_email_optional', 0);
    $wa_active      = fwx_get_option('enable_whatsapp_capture', 0);

    // Se o modo "Somente WhatsApp" estiver ativo, validamos apenas o WhatsApp
    if ( $email_optional && $wa_active ) {
        if ( empty( $_POST['whatsapp'] ) ) {
            wp_send_json_error( fwx_t( 'lead_error_whatsapp_req' ) );
        }
    } else {
        // Comportamento padrão: e-mail obrigatório
        if ( ! is_email( $email ) ) {
            wp_send_json_error( fwx_t( 'lead_error_email_req' ) );
        }
    }

    // Validação WhatsApp se enviado
    $clean_wa = '';
    if ( ! empty( $whatsapp ) ) {
        // Remove tudo que não for número para salvar apenas dígitos (limpa a máscara)
        $clean_wa = preg_replace( '/\D/', '', $whatsapp );
        if ( strlen( $clean_wa ) < 10 ) {
            wp_send_json_error( fwx_t( 'lead_error_whatsapp_invalid' ) );
        }
    }

    // Filtro de Qualidade: Anti Temp-Mail + Anti Gibberish + MX Check
    list( $quality_ok, $quality_msg ) = fwx_validate_email_quality( $email );
    if ( ! $quality_ok ) {
        wp_send_json_error( $quality_msg );
    }

    if ( ! $lgpd ) {
        wp_send_json_error( fwx_t( 'lead_error_lgpd_req' ) );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'fastwebx_leads';

    // Pega o IP real do usuário
    $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';

    $inserted = $wpdb->insert(
        $table_name,
        array(
            'email'      => $email,
            'whatsapp'   => $clean_wa,
            'ip_address' => $ip,
            'created_at' => current_time('mysql')
        ),
        array( '%s', '%s', '%s', '%s' )
    );

    if ( $inserted ) {
        // Disparar Webhook se a função existir
        if ( function_exists( 'fwx_trigger_webhook' ) ) {
            fwx_trigger_webhook( array(
                'event'    => 'lead_capture',
                'email'    => $email,
                'whatsapp' => $whatsapp,
                'ip'       => $ip,
                'date'     => current_time('mysql'),
                'source'   => home_url()
            ) );
        }

        // URL de redirecionamento (Página de Obrigado)
        $redirect_url = fwx_get_option( 'lead_redirect_url', '' );
        $response_data = array(
            'message'      => fwx_t( 'lead_success_msg' ),
            'redirect_url' => ! empty( $redirect_url ) ? esc_url_raw( $redirect_url ) : '',
        );
        
        wp_send_json_success( $response_data );
    } else {
        // Pode ser um erro de UNIQUE key (email já cadastrado)
        wp_send_json_error( fwx_t( 'lead_error_duplicate' ) );
    }
}
add_action( 'wp_ajax_nopriv_fwx_submit_lead', 'fwx_process_lead_submission' );
add_action( 'wp_ajax_fwx_submit_lead', 'fwx_process_lead_submission' );

/**
 * 3. Shortcode Front-End [fwx_lead_form]
 */
function fwx_render_lead_form_shortcode() {
    ob_start();
    $ajax_url = admin_url( 'admin-ajax.php' );
    $unique_id = uniqid();
    ?>
    <div class="fwx-lead-wrapper fwx-lead-form-box">
        <?php
        $title = fwx_get_option( 'lead_form_title', fwx_t( 'lead_form_title_default' ) );
        $desc = fwx_get_option( 'lead_form_desc', fwx_t( 'lead_form_desc_default' ) );
        if ( ! empty( $title ) ) echo '<h2 class="fwx-lead-title">' . esc_html( $title ) . '</h2>';
        if ( ! empty( $desc ) ) echo '<p class="fwx-lead-desc">' . esc_html( $desc ) . '</p>';
        ?>
        <form class="fwx-lead-form" onsubmit="fwxSubmitLead(event, this)" 
              data-msg-sending="<?php echo esc_attr( fwx_t( 'lead_sending' ) ); ?>" 
              data-msg-redirecting="<?php echo esc_attr( fwx_t( 'lead_redirecting' ) ); ?>" 
              data-msg-err-server="<?php echo esc_attr( fwx_t( 'lead_error_server' ) ); ?>" 
              data-msg-err-try="<?php echo esc_attr( fwx_t( 'lead_error_server_try' ) ); ?>"
              data-msg-err-generic="<?php echo esc_attr( fwx_t( 'lead_error_generic' ) ); ?>">
            <input type="hidden" name="ajax_url" value="<?php echo esc_url( $ajax_url ); ?>">
            <input type="text" name="fwx_hp_email" value="" style="display:none!important" tabindex="-1" autocomplete="off">
            
            <div class="fwx-input-group <?php echo fwx_get_option('enable_whatsapp_capture', 0) ? 'fwx-has-whatsapp' : ''; ?>">
                <?php 
                $wa_active = fwx_get_option('enable_whatsapp_capture', 0);
                $email_hidden = ( $wa_active && fwx_get_option('lead_email_optional', 0) );
                
                if ( ! $email_hidden ) : ?>
                    <input type="email" name="email" placeholder="<?php echo esc_attr( fwx_t( 'lead_placeholder_email' ) ); ?>" required autocomplete="email" />
                <?php endif; ?>
                
                <?php if ( fwx_get_option('enable_whatsapp_capture', 0) ) : ?>
                    <input type="tel" name="whatsapp" placeholder="(00) 00000-0000" oninput="fwxMaskWA(this)" required />
                <?php endif; ?>

                <button type="submit" class="fwx-lead-btn-submit"><?php echo esc_html( fwx_t( 'lead_btn_submit' ) ); ?></button>
            </div>
            
            <div class="fwx-checkbox-group">
                <label>
                    <input type="checkbox" name="lgpd" required />
                    <span><?php echo wp_kses_post( fwx_get_option('lead_form_lgpd_msg', fwx_t( 'lead_form_lgpd_default' ) ) ); ?></span>
                </label>
            </div>

            <div class="fwx-form-msg"></div>
        </form>
    </div>

    <?php if ( ! wp_script_is( 'fwx-lead-script-injected', 'done' ) ) : ?>
    <script>
        function fwxMaskWA(el) {
            let v = el.value.replace(/\D/g, "");
            if (v.length > 11) v = v.substring(0, 11);
            if (v.length > 2) v = "(" + v.substring(0, 2) + ") " + v.substring(2);
            if (v.length > 9) v = v.substring(0, 10) + "-" + v.substring(10);
            else if (v.length > 7) v = v.substring(0, 9) + "-" + v.substring(9);
            el.value = v;
        }

        function fwxSubmitLead(e, form) {
            e.preventDefault();
            const btn = form.querySelector('.fwx-lead-btn-submit');
            const msg = form.querySelector('.fwx-form-msg');
            const emailInp = form.querySelector('input[name="email"]');
            const email = emailInp ? emailInp.value : '';
            const waInput = form.querySelector('input[name="whatsapp"]');
            const whatsapp = waInput ? waInput.value : '';
            const lgpd = form.querySelector('input[name="lgpd"]').checked ? 1 : 0;
            const ajaxUrl = form.querySelector('input[name="ajax_url"]').value;
            const hp = form.querySelector('input[name="fwx_hp_email"]');

            if (hp && hp.value !== '') {
                // Preenchimento invisível (Bot)
                return false;
            }

            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.innerHTML = form.getAttribute('data-msg-sending') || 'Enviando...';
            msg.innerHTML = '';
            msg.className = 'fwx-form-msg';

            const data = new URLSearchParams();
            data.append('action', 'fwx_submit_lead');
            data.append('email', email);
            data.append('whatsapp', whatsapp);
            data.append('lgpd', lgpd);

            fetch(ajaxUrl, {
                method: 'POST',
                body: data
            })
            .then(res => res.json())
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = originalText;

                // Proteção contra respostas não-objeto (ex: erro 500 disfarçado, -1 de cache, etc)
                if (typeof res !== 'object' || res === null) {
                    msg.innerHTML = form.getAttribute('data-msg-err-server') || 'Erro no servidor. Por favor, recarregue a página e tente novamente.';
                    msg.classList.add('fwx-error');
                    return;
                }

                if(res.success) {
                    const data = res.data || {};
                    const message = data.message || 'Cadastro realizado com sucesso!';
                    const redirectUrl = data.redirect_url || '';

                    if ( redirectUrl ) {
                        // Mostra overlay de redirecionamento com efeito blur
                        form.reset();
                        let overlay = document.getElementById('fwx-redirect-overlay');
                        if ( ! overlay ) {
                            overlay = document.createElement('div');
                            overlay.id = 'fwx-redirect-overlay';
                            const redirText = form.getAttribute('data-msg-redirecting') || 'Redirecionando, aguarde...';
                            overlay.innerHTML = '<div class="fwx-redirect-box"><div class="fwx-redirect-spinner"></div><p class="fwx-redirect-text">' + redirText + '</p></div>';
                            document.body.appendChild(overlay);
                        }
                        overlay.classList.add('fwx-redirect-active');
                        setTimeout(function() {
                            window.location.href = redirectUrl;
                        }, 1800);
                    } else {
                        // Exibe mensagem de sucesso normalmente
                        msg.innerHTML = message;
                        msg.classList.add('fwx-success');
                        form.reset();
                    }
                } else {
                    const genError = form.getAttribute('data-msg-err-generic') || 'Ocorreu um erro.';
                    msg.innerHTML = (typeof res.data === 'string') ? res.data : genError;
                    msg.classList.add('fwx-error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                msg.innerHTML = form.getAttribute('data-msg-err-try') || 'Erro no servidor, tente novamente.';
                msg.classList.add('fwx-error');
            });
        }
    </script>
    <?php 
    wp_enqueue_script( 'fwx-lead-script-injected' ); // Fake handle just to stay track
    global $wp_scripts;
    $wp_scripts->done[] = 'fwx-lead-script-injected';
    endif;
    ?>
    <?php
    return ob_get_clean();
}
add_shortcode( 'fwx_lead_form', 'fwx_render_lead_form_shortcode' );

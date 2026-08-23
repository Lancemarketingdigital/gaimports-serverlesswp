<?php
/**
 * Fast WebX - Módulo de Interatividade e Webhooks
 *
 * @package Fast_WebX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disparador Global de Webhook
 * @param array $data_payload Dados a serem enviados.
 */
function fwx_trigger_webhook( $data_payload ) {
    $webhook_url = fwx_get_option( 'webhook_url', '' );
    
    if ( empty( $webhook_url ) || ! wp_http_validate_url( $webhook_url ) ) {
        return false;
    }

    $args = array(
        'body'        => wp_json_encode( $data_payload ),
        'headers'     => array(
            'Content-Type' => 'application/json',
        ),
        'timeout'     => 10,
        'redirection' => 5,
        'blocking'    => false, // Assíncrono, não bloqueia o carregamento
        'data_format' => 'body',
    );

    $response = wp_remote_post( $webhook_url, $args );
    
    if ( is_wp_error( $response ) ) {
        return false;
    }

    return true;
}

/**
 * 1. Processador Ajax de Contato
 */
function fwx_process_contact_form() {
    check_ajax_referer( 'fwx_submit_contact', 'nonce' );

    // Honeypot genérico anti-spam
    if ( ! empty( $_POST['fwx_hp'] ) ) {
        wp_send_json_error( fwx_t( 'contact_err_spam' ) );
    }

    if ( ! fwx_get_option( 'enable_contact_form', 0 ) ) {
        wp_send_json_error( fwx_t( 'contact_err_disabled' ) );
    }

    $name    = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
    $email   = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
    $subject = isset( $_POST['subject'] ) ? sanitize_text_field( $_POST['subject'] ) : 'Novo Contato do Site';
    $message = isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '';

    if ( empty( $name ) || ! is_email( $email ) || empty( $message ) ) {
        wp_send_json_error( fwx_t( 'contact_err_required' ) );
    }

    // Configurando e-mail
    $to        = get_option( 'admin_email' );
    $site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $site_name . ' <' . $to . '>',
        'Reply-To: ' . $name . ' <' . $email . '>'
    );

    $body  = "<h2>Novo Contato via Fast WebX</h2>";
    $body .= "<p><strong>Nome:</strong> {$name}</p>";
    $body .= "<p><strong>E-mail:</strong> {$email}</p>";
    $body .= "<p><strong>Assunto:</strong> {$subject}</p>";
    $body .= "<p><strong>Mensagem:</strong><br/>" . nl2br( $message ) . "</p>";

    $sent = wp_mail( $to, $subject, $body, $headers );

    if ( $sent ) {
        // Disparar Webhook
        fwx_trigger_webhook( array(
            'event'   => 'contact_form',
            'name'    => $name,
            'email'   => $email,
            'subject' => $subject,
            'message' => $message,
            'date'    => current_time('mysql'),
            'source'  => home_url()
        ) );

        wp_send_json_success( fwx_t( 'contact_success_msg' ) );
    } else {
        wp_send_json_error( fwx_t( 'contact_err_mail' ) );
    }
}
add_action( 'wp_ajax_nopriv_fwx_submit_contact', 'fwx_process_contact_form' );
add_action( 'wp_ajax_fwx_submit_contact', 'fwx_process_contact_form' );


/**
 * 2. Shortcode Front-End [fwx_contact_form]
 */
function fwx_render_contact_form_shortcode() {
    if ( ! fwx_get_option( 'enable_contact_form', 0 ) ) {
        return '';
    }

    ob_start();
    $ajax_url = admin_url( 'admin-ajax.php' );
    $nonce = wp_create_nonce( 'fwx_submit_contact' );
    ?>
    <div class="fwx-contact-wrapper">
        <form class="fwx-contact-form" id="fwx-contact-form-primary" onsubmit="fwxSubmitContact(event)"
              data-sending="<?php echo esc_attr( fwx_t( 'contact_btn_sending' ) ); ?>"
              data-submit="<?php echo esc_attr( fwx_t( 'contact_btn_submit' ) ); ?>"
              data-conn-error="<?php echo esc_attr( fwx_t( 'contact_msg_conn_error' ) ); ?>">
            <input type="hidden" id="fwx_contact_nonce" value="<?php echo esc_attr( $nonce ); ?>">
            <input type="hidden" id="fwx_contact_ajax_url" value="<?php echo esc_url( $ajax_url ); ?>">
            <!-- Honeypot field -->
            <input type="text" id="fwx_contact_hp" style="display:none !important;" tabindex="-1" autocomplete="off">
            
            <div class="fwx-form-row">
                <div class="fwx-input-group">
                    <label for="fwx_contact_name"><?php echo esc_html( fwx_t( 'contact_label_name' ) ); ?> <span class="fwx-required">*</span></label>
                    <input type="text" id="fwx_contact_name" required placeholder="<?php echo esc_attr( fwx_t( 'contact_placeholder_name' ) ); ?>" autocomplete="name" />
                </div>
                <div class="fwx-input-group">
                    <label for="fwx_contact_email"><?php echo esc_html( fwx_t( 'contact_label_email' ) ); ?> <span class="fwx-required">*</span></label>
                    <input type="email" id="fwx_contact_email" required placeholder="<?php echo esc_attr( fwx_t( 'contact_placeholder_email' ) ); ?>" autocomplete="email" />
                </div>
            </div>

            <div class="fwx-input-group">
                <label for="fwx_contact_subject"><?php echo esc_html( fwx_t( 'contact_label_subject' ) ); ?></label>
                <input type="text" id="fwx_contact_subject" placeholder="<?php echo esc_attr( fwx_t( 'contact_placeholder_subject' ) ); ?>" autocomplete="off" />
            </div>

            <div class="fwx-input-group">
                <label for="fwx_contact_message"><?php echo esc_html( fwx_t( 'contact_label_message' ) ); ?> <span class="fwx-required">*</span></label>
                <textarea id="fwx_contact_message" rows="5" required placeholder="<?php echo esc_attr( fwx_t( 'contact_placeholder_message' ) ); ?>"></textarea>
            </div>
            
            <div class="fwx-action-group">
                <button type="submit" id="fwx_contact_btn" class="fwx-btn-submit"><?php echo esc_html( fwx_t( 'contact_btn_submit' ) ); ?></button>
            </div>

            <div id="fwx_contact_msg" class="fwx-form-msg"></div>
        </form>
    </div>

    <script>
        function fwxSubmitContact(e) {
            e.preventDefault();
            const form = document.getElementById('fwx-contact-form-primary');
            const btn = document.getElementById('fwx_contact_btn');
            const msg = document.getElementById('fwx_contact_msg');
            const nonce = document.getElementById('fwx_contact_nonce').value;
            const ajaxUrl = document.getElementById('fwx_contact_ajax_url').value;
            const hp = document.getElementById('fwx_contact_hp').value;

            const txtSending = form.getAttribute('data-sending') || 'Enviando...';
            const txtSubmit = form.getAttribute('data-submit') || 'Enviar Mensagem';
            const txtConnError = form.getAttribute('data-conn-error') || 'Erro de conexão. Tente mais tarde.';

            btn.disabled = true;
            btn.innerHTML = txtSending;
            msg.innerHTML = '';
            msg.className = 'fwx-form-msg';

            const data = new URLSearchParams();
            data.append('action', 'fwx_submit_contact');
            data.append('nonce', nonce);
            data.append('fwx_hp', hp);
            data.append('name', document.getElementById('fwx_contact_name').value);
            data.append('email', document.getElementById('fwx_contact_email').value);
            data.append('subject', document.getElementById('fwx_contact_subject').value);
            data.append('message', document.getElementById('fwx_contact_message').value);

            fetch(ajaxUrl, {
                method: 'POST',
                body: data
            })
            .then(res => res.json())
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = txtSubmit;
                if(res.success) {
                    msg.innerHTML = res.data;
                    msg.classList.add('fwx-success');
                    form.reset();
                } else {
                    msg.innerHTML = res.data;
                    msg.classList.add('fwx-error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = txtSubmit;
                msg.innerHTML = txtConnError;
                msg.classList.add('fwx-error');
            });
        }
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode( 'fwx_contact_form', 'fwx_render_contact_form_shortcode' );

/**
 * 3. Paginação AJAX (Ver Mais Posts)
 */
function fwx_load_more_posts() {
    check_ajax_referer( 'fwx_loadmore_nonce', 'nonce' );
    
    $layout = isset( $_POST['layout'] ) ? sanitize_text_field( $_POST['layout'] ) : 'layout-a';
    $page   = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
    
    // Obter variáveis simples passadas pelo frontend
    $query_vars = array(
        'paged'       => $page,
        'post_status' => 'publish'
    );
    
    if ( ! empty( $_POST['category'] ) ) {
        $query_vars['cat'] = intval( $_POST['category'] );
    }
    if ( ! empty( $_POST['tag'] ) ) {
        $query_vars['tag_id'] = intval( $_POST['tag'] );
    }
    if ( ! empty( $_POST['author'] ) ) {
        $query_vars['author'] = intval( $_POST['author'] );
    }
    if ( ! empty( $_POST['search'] ) ) {
        $query_vars['s'] = sanitize_text_field( $_POST['search'] );
    }
    
    $query = new WP_Query( $query_vars );
    
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            
            // O destaque fwx-post-featured só afeta o PRIMEIRO post do loop principal, então em AJAX sempre padrão
            $item_class = 'fwx-post-item';
            if ( 'layout-f' === $layout ) {
                $item_class .= ' fwx-news-list-item';
                $thumb_size = 'fwx-news-list';
            } elseif ( 'layout-e' === $layout ) {
                $thumb_size = 'fwx-home-featured';
            } else {
                $thumb_size = 'fwx-home-grid';
            }
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class( $item_class ); ?>>

                <div class="fwx-post-thumb">
                    <a href="<?php the_permalink(); ?>">
                        <?php fwx_the_post_thumbnail( null, $thumb_size, array( 'loading' => 'lazy' ) ); ?>
                    </a>
                </div>

                <div class="fwx-post-content">
                    <header class="entry-header">
                        <?php 
                        $categories = get_the_category();
                        if ( ! empty( $categories ) ) {
                            echo '<span class="fwx-cat-label fwx-cat-id-' . esc_attr( $categories[0]->term_id ) . '">' . esc_html( $categories[0]->name ) . '</span>';
                        }
                        
                        the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); 
                        fwx_render_post_meta();
                        ?>
                    </header>
                    <div class="entry-summary">
                        <?php the_excerpt(); ?>
                    </div>
                </div>
            </article>
            <?php
        }
    }
    wp_reset_postdata();
    wp_die();
}
add_action( 'wp_ajax_nopriv_fwx_load_more_posts', 'fwx_load_more_posts' );
add_action( 'wp_ajax_fwx_load_more_posts', 'fwx_load_more_posts' );

/**
 * 4. Paginação AJAX (Ver Mais Produtos)
 */
function fwx_load_more_produtos() {
    check_ajax_referer( 'fwx_loja_loadmore_nonce', 'nonce' );
    
    $page = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
    
    $query_vars = array(
        'post_type'      => 'fwx_produto',
        'paged'          => $page,
        'post_status'    => 'publish',
        'posts_per_page' => get_option( 'posts_per_page' )
    );
    
    if ( ! empty( $_POST['category'] ) ) {
        $query_vars['tax_query'] = array(
            array(
                'taxonomy' => 'fwx_categoria_produto',
                'field'    => 'term_id',
                'terms'    => intval( $_POST['category'] ),
            ),
        );
    }
    
    $query = new WP_Query( $query_vars );
    
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            fwx_render_produto_card( get_the_ID(), true );
        }
    }
    wp_reset_postdata();
    wp_die();
}
add_action( 'wp_ajax_nopriv_fwx_load_more_produtos', 'fwx_load_more_produtos' );
add_action( 'wp_ajax_fwx_load_more_produtos', 'fwx_load_more_produtos' );

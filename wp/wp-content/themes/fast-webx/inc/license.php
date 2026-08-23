<?php
/**
 * Fast WebX - Sistema de Licenciamento (Soft Lock)
 * 
 * Gerencia a validação de licença via API REST e controla
 * o acesso ao painel de configurações do tema.
 * 
 * @package Fast_WebX
 * @since   3.1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =========================================================================
// CONSTANTE DO SERVIDOR DE LICENÇAS
// Para produção, alterar para: https://membros.academiawpx.com/wp-json/fwx/v1/validate
// =========================================================================
if ( ! defined( 'FWX_LICENSE_SERVER' ) ) {
    $current_host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
    if ( strpos( $current_host, 'projectswpx.local' ) !== false || strpos( $current_host, 'fastwebx.local' ) !== false ) {
        define( 'FWX_LICENSE_SERVER', 'http://projectswpx.local/wp-json/fwx/v1/validate' );
    } else {
        define( 'FWX_LICENSE_SERVER', 'https://membros.academiawpx.com/wp-json/fwx/v1/validate' );
    }
}

// =========================================================================
// FUNÇÃO AUXILIAR — Verifica se o tema está ativado
// =========================================================================

/**
 * Retorna true se a licença do tema estiver ativa.
 *
 * @return bool
 */
function fwx_is_active() {
    return get_option( 'fwx_license_status' ) === 'active';
}

// =========================================================================
// OVERLAY DE BLOQUEIO — HTML reutilizável
// =========================================================================

/**
 * Renderiza o overlay de bloqueio dentro das abas do painel.
 * Chamado pelo admin-panel.php quando fwx_is_active() retorna false.
 *
 * @return void
 */
function fwx_render_lock_overlay() {
    ?>
    <div class="fwx-lock-overlay">
        <div class="fwx-lock-icon">🔒</div>
        <h3>Este recurso requer ativação</h3>
        <p>Insira sua licença na aba <strong>Licença</strong> para liberar o painel completo.</p>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=fwx-license' ) ); ?>" class="button button-primary button-hero">Ativar Licença</a>
    </div>
    <?php
}

// =========================================================================
// SUB-MENU ADMIN — Página de Ativação
// =========================================================================

/**
 * Registra o sub-menu "Licença" dentro do menu "Fast WebX".
 *
 * @return void
 */
function fwx_license_admin_menu() {
    add_submenu_page(
        'fast-webx',
        'Licença — Fast WebX',
        'Licença',
        'manage_options',
        'fwx-license',
        'fwx_license_render_page'
    );
}
add_action( 'admin_menu', 'fwx_license_admin_menu' );

// =========================================================================
// PROCESSAMENTO DO FORMULÁRIO — admin_init
// =========================================================================

/**
 * Processa a ativação e desativação de licença via POST.
 *
 * @return void
 */
function fwx_license_handle_actions() {
    // Só processa na página correta (usa $_REQUEST para capturar tanto GET quanto POST)
    $page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';
    if ( $page !== 'fwx-license' ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // --- ATIVAÇÃO ---
    if ( isset( $_POST['fwx_license_activate'] ) ) {
        if ( ! wp_verify_nonce( $_POST['fwx_license_nonce'] ?? '', 'fwx_license_action' ) ) {
            add_settings_error( 'fwx_license', 'nonce_fail', 'Erro de segurança. Tente novamente.', 'error' );
            return;
        }

        $license_key = sanitize_text_field( wp_unslash( $_POST['fwx_license_key'] ?? '' ) );
        $domain      = sanitize_text_field( wp_unslash( $_POST['fwx_license_domain'] ?? '' ) );

        if ( empty( $license_key ) || empty( $domain ) ) {
            add_settings_error( 'fwx_license', 'empty_fields', 'Preencha todos os campos.', 'error' );
            return;
        }

        // --- CHAVE BYPASS LOCAL (FWX-BYPASS-) ---
        //---

        // Requisição para o servidor de licenças
        $response = wp_remote_post( FWX_LICENSE_SERVER, array(
            'timeout'   => 15,
            'sslverify' => false,
            'body'      => array(
                'domain'       => $domain,
                'license_key'  => $license_key,
                'product_slug' => 'fast-webx',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            $err = $response->get_error_message();
            if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
                error_log( '[Fast WebX License] WP_Error: ' . $err );
            }
            add_settings_error( 'fwx_license', 'connection_error', 'Erro de conexão: ' . $err, 'error' );
            return;
        }

        $raw_body = wp_remote_retrieve_body( $response );
        $body     = json_decode( $raw_body, true );
        $code_resp = wp_remote_retrieve_response_code( $response );

        if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            error_log( '[Fast WebX License] HTTP ' . $code_resp . ' — Corpo: ' . substr( $raw_body, 0, 300 ) );
        }

        if ( $code_resp === 200 && ! empty( $body['success'] ) && $body['success'] === true ) {
            update_option( 'fwx_license_status', 'active' );
            update_option( 'fwx_license_key', $license_key );
            update_option( 'fwx_license_domain', $domain );
            set_transient( 'fwx_license_check_valid', 'valid', 48 * HOUR_IN_SECONDS );
            add_settings_error( 'fwx_license', 'activated', '✅ Licença ativada com sucesso! O painel completo está liberado.', 'success' );
        } else {
            delete_option( 'fwx_license_status' );
            delete_option( 'fwx_license_key' );
            delete_option( 'fwx_license_domain' );
            delete_transient( 'fwx_license_check_valid' );
            $msg = ! empty( $body['message'] ) ? $body['message'] : 'Licença inválida ou não encontrada para este domínio.';
            add_settings_error( 'fwx_license', 'invalid_license', '❌ ' . esc_html( $msg ), 'error' );
        }
    }

    // --- DESATIVAÇÃO ---
    if ( isset( $_POST['fwx_license_deactivate'] ) ) {
        if ( ! wp_verify_nonce( $_POST['fwx_license_nonce'] ?? '', 'fwx_license_action' ) ) {
            add_settings_error( 'fwx_license', 'nonce_fail', 'Erro de segurança. Tente novamente.', 'error' );
            return;
        }

        delete_option( 'fwx_license_status' );
        delete_option( 'fwx_license_key' );
        delete_option( 'fwx_license_domain' );
        add_settings_error( 'fwx_license', 'deactivated', 'Licença desativada. O painel foi bloqueado.', 'info' );
    }
}
add_action( 'admin_init', 'fwx_license_handle_actions' );

// =========================================================================
// VERIFICAÇÃO SILENCIOSA EM BACKGROUND (Background Ping)
// =========================================================================

/**
 * Rotina de verificação periódica de licença (a cada 48h / 60 segundos para testes).
 *
 * Roda silenciosamente no admin_init quando a licença está ativa,
 * garantindo que revogações e reembolsos sejam detectados automaticamente.
 * Em caso de falha de rede, aguarda apenas 2h antes de tentar novamente,
 * sem penalizar o cliente por instabilidade no servidor de licenças.
 *
 * @return void
 */
function fwx_license_background_ping() {
    // Só executa se a licença estiver marcada como ativa no banco
    if ( get_option( 'fwx_license_status' ) !== 'active' ) {
        return;
    }

    // Se o transient existir, a licença foi validada recentemente — nada a fazer
    if ( get_transient( 'fwx_license_check_valid' ) ) {
        return;
    }

    // Recupera os dados salvos no banco
    $license_key = get_option( 'fwx_license_key', '' );
    $domain      = get_option( 'fwx_license_domain', '' );

    // Segurança: se não houver chave salva, revoga localmente
    if ( empty( $license_key ) || empty( $domain ) ) {
        update_option( 'fwx_license_status', 'inactive' );
        return;
    }

    // Chave bypass: não faz ping no servidor, apenas renova o transient
    // ...

    // Faz a requisição silenciosa ao servidor de licenças
    $response = wp_remote_post( FWX_LICENSE_SERVER, array(
        'timeout'   => 5, // Timeout curto para não travar o painel
        'sslverify' => false,
        'body'      => array(
            'domain'       => $domain,
            'license_key'  => $license_key,
            'product_slug' => 'fast-webx',
        ),
    ) );

    // --- CASO 1: Erro de rede ou timeout ---
    // Servidor instável. Não punimos o cliente. Tentamos novamente em 2h.
    if ( is_wp_error( $response ) ) {
        set_transient( 'fwx_license_check_valid', 'retry', 2 * HOUR_IN_SECONDS );
        return;
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    // --- CASO 2: Bloqueios Temporários e Erros de Servidor ---
    // Protege o cliente contra F5 rápido ou Firewalls (Hostinger/Cloudflare).
    // Se não for um JSON válido ou for um código de bloqueio (403, 429, 500+), tentamos depois.
    if ( $body === null || $code === 403 || $code === 429 || $code >= 500 ) {
        set_transient( 'fwx_license_check_valid', 'retry', 2 * HOUR_IN_SECONDS );
        return;
    }

    // --- CASO 3: API confirmou licença válida ---
    // Cria o transient de 48h. Próxima verificação só depois desse período.    
    if ( $code === 200 && ! empty( $body['success'] ) && $body['success'] === true ) {
        set_transient( 'fwx_license_check_valid', 'valid', 48 * HOUR_IN_SECONDS);
        return;
    }

    // --- CASO 4: API retornou erro explícito (chave revogada ou expirada) ---
    // Apenas uma resposta negativa explícita do servidor desativa a licença.
    update_option( 'fwx_license_status', 'inactive' );
    delete_option( 'fwx_license_key' );
    delete_option( 'fwx_license_domain' );
    delete_transient( 'fwx_license_check_valid' );

    // Registra o admin_notice para alertar o administrador do site
    add_action( 'admin_notices', function() {
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <strong>Fast WebX:</strong>
                Sua licença foi revogada ou expirou. Por favor, <a href="<?php echo esc_url( admin_url( 'admin.php?page=fwx-license' ) ); ?>">ative novamente</a> para restaurar o acesso ao painel.
            </p>
        </div>
        <?php
    } );
}
add_action( 'admin_init', 'fwx_license_background_ping' );


// =========================================================================
// CSS DO MÓDULO DE LICENÇA — Inline Admin
// =========================================================================

/**
 * Injeta CSS do formulário de licença e do overlay de bloqueio.
 *
 * @param string $hook Hook da página atual.
 * @return void
 */
function fwx_license_admin_styles( $hook ) {
    // Injeta em TODAS as páginas do Fast WebX (para o overlay funcionar)
    if ( strpos( $hook, 'fast-webx' ) === false && strpos( $hook, 'fwx-license' ) === false ) {
        return;
    }

    $css = '
        /* === Overlay de Bloqueio === */
        .fwx-lock-overlay {
            text-align: center;
            padding: 80px 40px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            border: 2px dashed #dee2e6;
            margin: 20px 0;
        }
        .fwx-lock-icon {
            font-size: 64px;
            margin-bottom: 20px;
            line-height: 1;
        }
        .fwx-lock-overlay h3 {
            font-size: 22px;
            font-weight: 700;
            color: #1d2327;
            margin-bottom: 10px;
        }
        .fwx-lock-overlay p {
            font-size: 15px;
            color: #50575e;
            margin-bottom: 25px;
            max-width: 450px;
            margin-left: auto;
            margin-right: auto;
        }
        .fwx-lock-overlay .button-hero {
            font-size: 15px !important;
            padding: 12px 30px !important;
            height: auto !important;
            line-height: 1.4 !important;
        }

        /* === Página de Ativação === */
        .fwx-license-wrap {
            max-width: 600px;
            margin: 40px auto;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .fwx-license-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 40px;
            text-align: center;
        }
        .fwx-license-card h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #1d2327;
        }
        .fwx-license-card .fwx-license-subtitle {
            color: #50575e;
            font-size: 14px;
            margin-bottom: 30px;
        }
        .fwx-license-form-group {
            text-align: left;
            margin-bottom: 20px;
        }
        .fwx-license-form-group label {
            display: block;
            font-weight: 600;
            font-size: 13px;
            color: #1d2327;
            margin-bottom: 6px;
        }
        .fwx-license-form-group input[type="text"] {
            width: 100%;
            padding: 10px 14px;
            font-size: 14px;
            border: 1px solid #8c8f94;
            border-radius: 6px;
            transition: border-color 0.2s;
        }
        .fwx-license-form-group input[type="text"]:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
            outline: none;
        }
        .fwx-license-form-group input[readonly] {
            background: #f0f0f1;
            color: #50575e;
            cursor: not-allowed;
        }
        .fwx-license-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 25px;
        }
        .fwx-license-actions .button {
            padding: 8px 24px !important;
            font-size: 14px !important;
            height: auto !important;
            line-height: 1.6 !important;
        }
        .fwx-license-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 25px;
        }
        .fwx-license-status.active {
            background: #d1fae5;
            color: #065f46;
        }
        .fwx-license-status.inactive {
            background: #fee2e2;
            color: #991b1b;
        }
        .fwx-license-info {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f1;
            font-size: 12px;
            color: #50575e;
        }
        .fwx-license-info code {
            font-size: 11px;
            padding: 2px 6px;
            background: #f0f0f1;
            border-radius: 3px;
        }
    ';

    wp_add_inline_style( 'wp-admin', $css );
}
add_action( 'admin_enqueue_scripts', 'fwx_license_admin_styles' );

// =========================================================================
// RENDERIZAÇÃO DA PÁGINA DE ATIVAÇÃO
// =========================================================================

/**
 * Renderiza a interface da página de ativação de licença.
 *
 * @return void
 */
function fwx_license_render_page() {
    $is_active   = fwx_is_active();
    $saved_key   = get_option( 'fwx_license_key', '' );
    $domain      = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : 'localhost';

    // Mascara a chave: exibe apenas os primeiros e últimos 4 caracteres
    $masked_key  = '';
    if ( ! empty( $saved_key ) && strlen( $saved_key ) > 8 ) {
        $masked_key = substr( $saved_key, 0, 4 ) . str_repeat( '•', strlen( $saved_key ) - 8 ) . substr( $saved_key, -4 );
    } elseif ( ! empty( $saved_key ) ) {
        $masked_key = str_repeat( '•', strlen( $saved_key ) );
    }
    ?>
    <div class="wrap">
        <div class="fwx-license-wrap">
            <?php settings_errors( 'fwx_license' ); ?>

            <div class="fwx-license-card">
                <h2>🔑 Ativação Fast WebX</h2>
                <p class="fwx-license-subtitle">Insira sua chave de licença para desbloquear o painel completo do tema.</p>

                <?php if ( $is_active ) : ?>
                    <div class="fwx-license-status active">
                        <span>●</span> Licença Ativa
                    </div>
                <?php else : ?>
                    <div class="fwx-license-status inactive">
                        <span>●</span> Licença Inativa
                    </div>
                <?php endif; ?>

                <form method="post">
                    <?php wp_nonce_field( 'fwx_license_action', 'fwx_license_nonce' ); ?>

                    <div class="fwx-license-form-group">
                        <label for="fwx_license_key">Chave de Licença</label>
                        <?php if ( $is_active ) : ?>
                            <?php /* Campo visual mascarado — a chave real nunca é enviada ao HTML */ ?>
                            <input
                                type="text"
                                id="fwx_license_key"
                                value="<?php echo esc_attr( $masked_key ); ?>"
                                placeholder="Cole sua chave aqui..."
                                autocomplete="off"
                                readonly
                                style="font-family: monospace; letter-spacing: 2px;"
                            />
                            <?php /* Campo oculto com a chave real para o formulário de desativação */ ?>
                            <input type="hidden" name="fwx_license_key" value="<?php echo esc_attr( $saved_key ); ?>" />
                        <?php else : ?>
                            <input
                                type="text"
                                id="fwx_license_key"
                                name="fwx_license_key"
                                value=""
                                placeholder="Cole sua chave aqui..."
                                autocomplete="off"
                            />
                        <?php endif; ?>
                    </div>

                    <div class="fwx-license-form-group">
                        <label for="fwx_license_domain">Domínio Atual</label>
                        <input
                            type="text"
                            id="fwx_license_domain"
                            name="fwx_license_domain"
                            value="<?php echo esc_attr( $domain ); ?>"
                            readonly
                        />
                    </div>

                    <div class="fwx-license-actions">
                        <?php if ( ! $is_active ) : ?>
                            <button type="submit" name="fwx_license_activate" value="1" class="button button-primary">
                                Ativar Licença
                            </button>
                        <?php else : ?>
                            <button type="submit" name="fwx_license_deactivate" value="1" class="button button-secondary" onclick="return confirm('Tem certeza que deseja desativar a licença? O painel será bloqueado.');">
                                Desativar Licença
                            </button>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="fwx-license-info">
                    <!-- Servidor de validação: <?php echo esc_html( FWX_LICENSE_SERVER ); ?> -->
                    <p>Sua licença é vinculada ao domínio acima. Para trocar de domínio, desative primeiro e ative no novo site.</p>
                </div>
            </div>
        </div>
    </div>
    <?php
}

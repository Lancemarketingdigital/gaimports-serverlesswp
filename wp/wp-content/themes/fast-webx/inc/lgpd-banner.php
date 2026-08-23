<?php
/**
 * Banner de Consentimento LGPD
 * Integrado nativamente com Zero Cookies do Server (apenas front-end storage).
 *
 * @package Fast_WebX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function fwx_render_lgpd_banner() {
    // Rendiriza no rodapé de todas as páginas públicas
    if ( is_admin() ) return;
    $lgpd_text = fwx_get_option( 'lgpd_text', fwx_t( 'lgpd_text_default' ) );
    ?>
    <div id="fwx-lgpd-banner" class="fwx-lgpd-banner" style="display: none;">
        <div class="fwx-lgpd-content fwx-container">
            <p><?php echo esc_html( $lgpd_text ); ?></p>
            <div class="fwx-lgpd-actions">
                <?php 
                $policy_url = fwx_get_option( 'lgpd_policy_url', '' );
                if ( ! empty( $policy_url ) ) : ?>
                    <a href="<?php echo esc_url( $policy_url ); ?>" class="fwx-lgpd-policy-btn" target="_blank" rel="noopener noreferrer"><?php echo esc_html( fwx_t( 'lgpd_policy_btn' ) ); ?></a>
                <?php endif; ?>
                <button id="fwx-lgpd-accept" class="fwx-lgpd-btn"><?php echo esc_html( fwx_t( 'lgpd_accept_btn' ) ); ?></button>
            </div>
        </div>
    </div>

    <!-- Script Inline extremamente leve para não impactar o TBT e rodar apenas se o Storage não tiver o consentimento -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const banner = document.getElementById('fwx-lgpd-banner');
        if (!banner) return;

        // Se já aceitou, nem exibe
        if (localStorage.getItem('fwx_lgpd_accepted') === 'yes') {
            return;
        }

        // Mostra o banner
        banner.style.display = 'block';

        const acceptBtn = document.getElementById('fwx-lgpd-accept');
        acceptBtn.addEventListener('click', function() {
            localStorage.setItem('fwx_lgpd_accepted', 'yes');
            banner.style.display = 'none';
        });
    });
    </script>
    <?php
}
add_action( 'wp_footer', 'fwx_render_lgpd_banner', 100 );

/**
 * Fast WebX - Admin JS
 * Controla os comportamentos visuais dos componentes nativos na tela de Opções
 */
jQuery(document).ready(function($) {
    // Inicializa o Color Picker Nativo do WP
    if( $('.fwx-color-picker').length ) {
        $('.fwx-color-picker').wpColorPicker();
    }
});

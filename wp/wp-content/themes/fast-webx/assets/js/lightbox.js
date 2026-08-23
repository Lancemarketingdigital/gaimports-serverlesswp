/**
 * Fast WebX - Lightbox Nativo (Zero jQuery)
 * Abre imagens do conteúdo em overlay fullscreen ao clicar.
 *
 * @package Fast_WebX
 * @since   3.1.3
 */
document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', function (e) {
        const img = e.target.closest('.entry-content img, .fwx-produto-single-gallery img');
        if (!img) return;

        // Ignora imagens dentro das miniaturas da galeria
        if (img.closest('.fwx-produto-single-gallery-nav')) return;

        // Ignora imagens pequenas (ícones, avatars, emojis)
        if (img.naturalWidth < 200 || img.naturalHeight < 200) return;

        // Ignora imagens que já possuem o Lightbox nativo do WordPress (WP 6.4+)
        if (img.closest('.wp-lightbox-container') || img.hasAttribute('data-wp-interactive')) return;

        // Ignora imagens dentro de links externos (não ampliar banners clicáveis)
        const parentLink = img.closest('a');
        if (parentLink && !parentLink.getAttribute('href').match(/\.(jpe?g|png|gif|webp|svg)(\?.*)?$/i)) return;

        // Previne navegação se estiver dentro de <a>
        if (parentLink) e.preventDefault();

        // Determina a URL da imagem em tamanho completo
        let fullSrc = img.src;
        if (parentLink && parentLink.getAttribute('href').match(/\.(jpe?g|png|gif|webp|svg)(\?.*)?$/i)) {
            fullSrc = parentLink.getAttribute('href');
        }

        openLightbox(fullSrc, img.alt || '');
    });

    function openLightbox(src, alt) {
        const overlay = document.createElement('div');
        overlay.className = 'fwx-lightbox-overlay';
        overlay.innerHTML =
            '<button class="fwx-lightbox-close" aria-label="Fechar">&times;</button>' +
            '<img src="' + src + '" alt="' + alt + '">';

        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';

        // Fechar ao clicar no overlay (fora da imagem)
        overlay.addEventListener('click', function (ev) {
            if (ev.target === overlay || ev.target.classList.contains('fwx-lightbox-close')) {
                closeLightbox(overlay);
            }
        });

        // Alterna o zoom na imagem do lightbox
        const lightboxImg = overlay.querySelector('img');
        if (lightboxImg) {
            lightboxImg.addEventListener('click', function (ev) {
                ev.stopPropagation();
                this.classList.toggle('fwx-zoomed');
            });
        }

        // Fechar com ESC
        function onKeyDown(ev) {
            if (ev.key === 'Escape') {
                closeLightbox(overlay);
                document.removeEventListener('keydown', onKeyDown);
            }
        }
        document.addEventListener('keydown', onKeyDown);
    }

    function closeLightbox(overlay) {
        overlay.classList.add('fwx-lightbox-closing');
        overlay.addEventListener('animationend', function () {
            overlay.remove();
            document.body.style.overflow = '';
        });
    }
});

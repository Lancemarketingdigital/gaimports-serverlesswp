/**
 * Reading Progress Bar - Vanilla JS
 * Levíssimo (< 1KB), Zero jQuery, Performance Máxima
 */
document.addEventListener('DOMContentLoaded', function() {
    const progressBar = document.querySelector('.fwx-reading-progress');
    const content = document.querySelector('.fwx-single-content');
    
    if (progressBar && content) {
        window.addEventListener('scroll', function() {
            const contentRect = content.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            
            // Calcula o quanto já foi lido em relação ao tamanho total do conteúdo
            let percentage = 0;
            if (contentRect.top < windowHeight && contentRect.bottom > 0) {
                const totalScroll = contentRect.height - windowHeight;
                const currentScroll = windowHeight - contentRect.top;
                percentage = (currentScroll / totalScroll) * 100;
            } else if (contentRect.bottom <= 0) {
                percentage = 100; // Acabou o artigo
            }
            
            // Garante que fique entre 0 e 100
            percentage = Math.max(0, Math.min(100, percentage));
            progressBar.style.width = percentage + '%';
        }, { passive: true }); // passive: true melhora muito a performance do scroll no PageSpeed
    }
});

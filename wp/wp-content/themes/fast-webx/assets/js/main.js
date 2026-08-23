/**
 * Fast WebX - Scripts Principais (Vanilla JS)
 * Focado em performance e interatividade zero-bloat.
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Botão de Copiar Código
    const preBlocks = document.querySelectorAll('pre');
    
    preBlocks.forEach((pre) => {
        // Evita duplicar o botão se o script rodar mais de uma vez
        if (pre.querySelector('.fwx-copy-btn')) return;

        const copyBtn = document.createElement('button');
        copyBtn.className = 'fwx-copy-btn';
        copyBtn.type = 'button';
        copyBtn.innerText = 'Copiar';
        
        pre.appendChild(copyBtn);
        
        copyBtn.addEventListener('click', function() {
            // Seleciona o elemento de código (prioriza a tag <code> dentro do <pre>)
            const codeElement = pre.querySelector('code') || pre;
            
            // Captura o texto limpando possíveis espaços extras e ignorando o próprio botão
            let codeText = '';
            if (pre.querySelector('code')) {
                codeText = pre.querySelector('code').innerText;
            } else {
                // Se não houver <code>, clona o <pre> para remover o botão antes de pegar o texto
                const clone = pre.cloneNode(true);
                const btnInClone = clone.querySelector('.fwx-copy-btn');
                if (btnInClone) btnInClone.remove();
                codeText = clone.innerText;
            }

            // Função para dar feedback visual de sucesso
            const showSuccess = () => {
                copyBtn.innerText = 'Copiado!';
                copyBtn.classList.add('copied');
                setTimeout(() => {
                    copyBtn.innerText = 'Copiar';
                    copyBtn.classList.remove('copied');
                }, 2000);
            };

            // Tenta usar a API moderna de Clipboard
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(codeText.trim()).then(showSuccess).catch(err => {
                    console.error('Falha ao copiar: ', err);
                });
            } else {
                // Fallback: Método antigo para navegadores sem HTTPS ou suporte limitado
                const textArea = document.createElement("textarea");
                textArea.value = codeText.trim();
                textArea.style.position = "fixed";
                textArea.style.left = "-9999px";
                textArea.style.top = "0";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    showSuccess();
                } catch (err) {
                    console.warn('Erro no fallback de cópia:', err);
                }
                document.body.removeChild(textArea);
            }
        });
    });

    // 2. Botão Voltar ao Topo (Preparação para Fase B, Item 3.1)
    const backToTop = document.getElementById('fwx-back-to-top');
    if (backToTop) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });
        
        backToTop.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // 3. Exit Intent Popup
    const exitPopup = document.getElementById('fwx-exit-popup');
    if (exitPopup) {
        const frequency = exitPopup.getAttribute('data-frequency') || 'always';

        const shouldShow = () => {
            if (frequency === 'always') {
                return true;
            }
            if (frequency === 'session') {
                return !sessionStorage.getItem('fwx_exit_popup_session_showed');
            }
            if (frequency === 'permanent') {
                return !localStorage.getItem('fwx_exit_popup_showed');
            }
            
            // Frequências baseadas em tempo (1m, 1, 7, 30)
            const lastTime = localStorage.getItem('fwx_exit_popup_last_time');
            if (!lastTime) {
                return true;
            }

            const diff = Date.now() - parseInt(lastTime, 10);
            let limit = 0;

            if (frequency === '1m') {
                limit = 60 * 1000; // 1 minuto
            } else if (frequency === '1') {
                limit = 24 * 60 * 60 * 1000; // 1 dia
            } else if (frequency === '7') {
                limit = 7 * 24 * 60 * 60 * 1000; // 1 semana
            } else if (frequency === '30') {
                limit = 30 * 24 * 60 * 60 * 1000; // 1 mês
            }

            return diff > limit;
        };
        
        const showPopup = () => {
            if (shouldShow()) {
                exitPopup.classList.add('active');
                
                // Grava o estado conforme a recorrência
                if (frequency === 'session') {
                    sessionStorage.setItem('fwx_exit_popup_session_showed', 'true');
                } else if (frequency === 'permanent') {
                    localStorage.setItem('fwx_exit_popup_showed', 'true');
                } else if (['1m', '1', '7', '30'].includes(frequency)) {
                    localStorage.setItem('fwx_exit_popup_last_time', Date.now().toString());
                }
            }
        };

        // Detecta mouse saindo pelo topo (Intenção de fechar aba ou mudar URL)
        document.addEventListener('mouseleave', (e) => {
            if (e.clientY < 0) {
                showPopup();
            }
        });

        // Fechar Popup
        const closeBtn = exitPopup.querySelector('.fwx-exit-popup-close');
        const overlay = exitPopup.querySelector('.fwx-exit-popup-overlay');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                exitPopup.classList.remove('active');
            });
        }
        
        if (overlay) {
            overlay.addEventListener('click', () => {
                exitPopup.classList.remove('active');
            });
        }
    }

});

/**
 * Image Optimizer - Processamento Ajax no Backend
 */
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('fwx-btn-start-crop');

    if (!btn) return;

    btn.addEventListener('click', function () {
        const ids = window.fwxMediaIds || [];
        const total = ids.length;

        if (total === 0) return;

        btn.disabled = true;
        btn.innerText = 'Processando...';

        const stopBtn = document.getElementById('fwx-btn-stop-crop');
        const progressContainer = document.getElementById('fwx-progress-container');
        const progressBar = document.getElementById('fwx-progress-bar');
        const statusMsg = document.getElementById('fwx-status-msg');
        const logBox = document.getElementById('fwx-realtime-logs');

        // Novas opções
        const skipOptimized = document.getElementById('fwx-skip-optimized').checked;
        const generateAlt = document.getElementById('fwx-batch-alt-gen')?.checked || false;
        const generateLoja = document.getElementById('fwx-generate-loja-sizes')?.checked || false;
        const cleanOrphans = document.getElementById('fwx-clean-orphans').checked;
        const convertWebP = document.getElementById('fwx-convert-webp').checked;
        const throttleEnabled = document.getElementById('fwx-throttle-process').checked;

        progressContainer.style.display = 'block';
        if (stopBtn) stopBtn.style.display = 'inline-block';

        logBox.innerHTML = '> Iniciando processo de regeneração (v' + fwx_ajax.version + ')...';
        if (generateAlt) {
            logBox.innerHTML += '<br>> [SEO] Automação e preenchimento de tags ALT ativa.';
        }
        if (cleanOrphans) {
            logBox.innerHTML += '<br>> [ALERTA] Modo de Limpeza de Órfãs ativado. Miniaturas antigas serão removidas.';
        }
        if (convertWebP) {
            logBox.innerHTML += '<br>> [OTIMIZAÇÃO] Conversão para WebP (90%) ativa.';
        }

        let current = 0;
        let isStopped = false;

        if (stopBtn) {
            stopBtn.addEventListener('click', function () {
                isStopped = true;
                stopBtn.disabled = true;
                stopBtn.innerText = 'Parando...';
                logBox.innerHTML += '<br>> [AVISO] Interrupção solicitada pelo usuário. Aguardando fim do item atual...';
                logBox.scrollTop = logBox.scrollHeight;
            });
        }

        function processNext() {
            if (isStopped) {
                progressBar.style.background = '#d63638';
                statusMsg.innerText = 'Processo interrompido pelo usuário.';
                btn.innerText = 'Reiniciar Regeneração';
                btn.disabled = false;
                if (stopBtn) stopBtn.style.display = 'none';
                logBox.innerHTML += '<br>> [PARADO] Otimização interrompida.';
                logBox.scrollTop = logBox.scrollHeight;
                return;
            }

            if (current >= total) {
                progressBar.style.width = '100%';
                progressBar.style.background = '#46b450'; // Verde Sucesso
                statusMsg.innerText = 'Finalizado! Todas as ' + total + ' imagens foram verificadas e adaptadas.';
                btn.innerText = 'Regeneração Concluída';
                if (stopBtn) stopBtn.style.display = 'none';

                // Salva o log persistente no banco
                const finalData = new URLSearchParams();
                finalData.append('action', 'fwx_finalize_media_logs');
                finalData.append('nonce', fwx_ajax.nonce);
                finalData.append('count', total);
                fetch(fwx_ajax.ajax_url, { method: 'POST', body: finalData });

                logBox.innerHTML += '<br>> [CONCLUÍDO] Banco de mídias 100% sincronizado.';
                logBox.scrollTop = logBox.scrollHeight;
                return;
            }

            const attachmentId = ids[current];
            const percentage = Math.round((current / total) * 100);

            progressBar.style.width = percentage + '%';
            statusMsg.innerText = 'Processando imagem ' + (current + 1) + ' de ' + total + ' (' + percentage + '%)';

            const data = new URLSearchParams();
            data.append('action', 'fwx_regenerate_image');
            data.append('nonce', fwx_ajax.nonce);
            data.append('id', attachmentId);
            data.append('skip_optimized', skipOptimized);
            data.append('generate_alt', generateAlt);
            data.append('generate_loja', generateLoja);
            data.append('clean_orphans', cleanOrphans);
            data.append('convert_webp', convertWebP);

            fetch(fwx_ajax.ajax_url, {
                method: 'POST',
                body: data
            })
                .then(response => response.json())
                .then(res => {
                    if (res.success) {
                        if (res.data.skipped) {
                            logBox.innerHTML += '<br>> [PULADA] ' + res.data.filename + ' (Já otimizada)';
                        } else {
                            logBox.innerHTML += '<br>> [OK] ' + res.data.filename + ' | Versões: ' + (res.data.versions || 'padrão');
                        }
                    } else {
                        logBox.innerHTML += '<br>> [ERRO] ID ' + attachmentId + ': ' + (res.data || 'Erro desconhecido');
                    }
                    logBox.scrollTop = logBox.scrollHeight;
                    current++;

                    // Lógica de Throttle
                    if (throttleEnabled) {
                        setTimeout(processNext, 1000);
                    } else {
                        processNext();
                    }
                })
                .catch(error => {
                    console.error('Erro na imagem ID ' + attachmentId, error);
                    logBox.innerHTML += '<br>> [FALHA] Erro de rede no ID ' + attachmentId;
                    logBox.scrollTop = logBox.scrollHeight;
                    current++;
                    processNext();
                });
        }

        processNext();
    });
});

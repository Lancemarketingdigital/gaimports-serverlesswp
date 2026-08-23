document.addEventListener('DOMContentLoaded', function() {
    const ttsContainer = document.getElementById('fwx-tts-player');
    if (!ttsContainer) return;

    const playBtn = document.getElementById('fwx-tts-play');
    const stopBtn = document.getElementById('fwx-tts-stop');
    const progressBar = document.getElementById('fwx-tts-progress-fill');
    
    const contentDiv = document.querySelector('.entry-content');
    if (!contentDiv) return;

    let isPlaying = false;
    let isPaused = false;
    let utterance = null;
    let totalChars = 0;
    
    // Fallback
    if (!('speechSynthesis' in window)) {
        ttsContainer.style.display = 'none';
        return;
    }

    // Prepare text
    const clone = contentDiv.cloneNode(true);
    const elementsToRemove = clone.querySelectorAll('.fwx-tts-wrapper, .fwx-toc-container, .page-links, .fwx-post-tags');
    elementsToRemove.forEach(el => el.remove());
    
    const textToRead = (clone.innerText || clone.textContent).trim();
    totalChars = textToRead.length;

    if (totalChars === 0) {
        ttsContainer.style.display = 'none';
        return;
    }

    function updatePlayButtonState() {
        if (isPlaying && !isPaused) {
            playBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>';
        } else {
            playBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>';
        }
    }

    function resetPlayer() {
        window.speechSynthesis.cancel();
        isPlaying = false;
        isPaused = false;
        progressBar.style.width = '0%';
        updatePlayButtonState();
    }

    playBtn.addEventListener('click', function(e) {
        e.preventDefault();

        if (isPlaying) {
            if (isPaused) {
                window.speechSynthesis.resume();
                isPaused = false;
            } else {
                window.speechSynthesis.pause();
                isPaused = true;
            }
            updatePlayButtonState();
            return;
        }

        // Start new reading
        window.speechSynthesis.cancel(); // Clear pending
        
        utterance = new SpeechSynthesisUtterance(textToRead);
        utterance.lang = 'pt-BR';
        
        // Timeline approximation based on characters spoken
        utterance.onboundary = function(e) {
            if (e.name === 'word') {
                const percent = Math.min(100, (e.charIndex / totalChars) * 100);
                progressBar.style.width = percent + '%';
            }
        };

        utterance.onend = function() {
            resetPlayer();
            progressBar.style.width = '100%';
            setTimeout(() => { progressBar.style.width = '0%'; }, 1000);
        };

        utterance.onerror = function() {
            resetPlayer();
        };

        window.speechSynthesis.speak(utterance);
        isPlaying = true;
        isPaused = false;
        updatePlayButtonState();
    });

    stopBtn.addEventListener('click', function(e) {
        e.preventDefault();
        resetPlayer();
    });

    window.addEventListener('beforeunload', function() {
        if (isPlaying) window.speechSynthesis.cancel();
    });
});

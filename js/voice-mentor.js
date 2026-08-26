document.addEventListener('DOMContentLoaded', () => {
  const startVoiceBtn = document.getElementById('startVoiceBtn');
  const stopVoiceBtn = document.getElementById('stopVoiceBtn');
  const chatMessages = document.getElementById('chatMessages');

  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

  if (!SpeechRecognition) {
    if (startVoiceBtn) {
      startVoiceBtn.innerText = '🎤 Voice Unsupported';
      startVoiceBtn.disabled = true;
    }
    return;
  }

  const recognition = new SpeechRecognition();
  recognition.continuous = false;
  recognition.interimResults = false;
  recognition.lang = 'en-US';

  recognition.onstart = () => {
    showToast('Listening... Speak your question now.', 'info');
    if (startVoiceBtn) startVoiceBtn.innerText = '🔴 Listening...';
  };

  recognition.onresult = (event) => {
    const transcript = event.results[0][0].transcript;
    
    const userMsg = document.createElement('div');
    userMsg.className = 'message user';
    userMsg.innerText = transcript;
    chatMessages.appendChild(userMsg);

    fetch('api/voice-chat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ transcript: transcript })
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        const aiMsg = document.createElement('div');
        aiMsg.className = 'message ai';
        aiMsg.innerHTML = data.replyHtml;
        chatMessages.appendChild(aiMsg);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        if ('speechSynthesis' in window) {
          const utterance = new SpeechSynthesisUtterance(data.spokenText);
          utterance.rate = 1.0;
          window.speechSynthesis.speak(utterance);
          if (stopVoiceBtn) stopVoiceBtn.style.display = 'inline-flex';
        }
      }
    });
  };

  recognition.onend = () => {
    if (startVoiceBtn) startVoiceBtn.innerText = '🎤 Start Voice Mentor';
  };

  if (startVoiceBtn) {
    startVoiceBtn.addEventListener('click', () => recognition.start());
  }

  if (stopVoiceBtn) {
    stopVoiceBtn.addEventListener('click', () => {
      window.speechSynthesis.cancel();
      stopVoiceBtn.style.display = 'none';
    });
  }
});
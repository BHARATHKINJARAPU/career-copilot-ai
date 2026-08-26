document.addEventListener('DOMContentLoaded', () => {
  const chatMessages = document.getElementById('chatMessages');
  const chatInput = document.getElementById('chatInput');
  const sendBtn = document.getElementById('sendChatBtn');
  const typingIndicator = document.getElementById('typingIndicator');
  const promptChips = document.querySelectorAll('.prompt-chip');

  function appendMessage(htmlContent, sender) {
    const msg = document.createElement('div');
    msg.className = `message ${sender}`;
    msg.innerHTML = htmlContent;
    chatMessages.appendChild(msg);
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  function handleSend(userText) {
    const text = userText || (chatInput ? chatInput.value.trim() : '');
    if (!text) return;

    appendMessage(text, 'user');
    if (chatInput && !userText) chatInput.value = '';

    if (typingIndicator) {
      typingIndicator.style.display = 'block';
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // POST request to PHP REST API Endpoint
    fetch('api/ai-chat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ prompt: text })
    })
    .then(res => res.json())
    .then(data => {
      if (typingIndicator) typingIndicator.style.display = 'none';
      if (data.status === 'success') {
        appendMessage(data.reply, 'ai');
      } else {
        appendMessage("Sorry, I encountered an issue retrieving context. Please try again.", 'ai');
      }
    })
    .catch(() => {
      if (typingIndicator) typingIndicator.style.display = 'none';
      appendMessage("Network error communicating with PHP AI endpoint.", 'ai');
    });
  }

  if (sendBtn && chatInput) {
    sendBtn.addEventListener('click', () => handleSend());
    chatInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') handleSend();
    });
  }

  promptChips.forEach(chip => {
    chip.addEventListener('click', () => {
      handleSend(chip.getAttribute('data-prompt') || chip.innerText.replace(/"/g, ''));
    });
  });
});
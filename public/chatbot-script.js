document.addEventListener('DOMContentLoaded', () => {
    const chatIcon = document.createElement('img');
    chatIcon.className = 'oacb-chat-icon';
    chatIcon.src = oacbConfig.assetsUrl + 'chatbot-icon.png';
    document.body.appendChild(chatIcon);

    const chatWindow = document.getElementById('oacb-chat-window');
    const messagesContainer = document.getElementById('oacb-messages');
    const messageInput = document.getElementById('oacb-message-input');
    const sendButton = document.getElementById('oacb-send-button');

    let isOpen = false;

    // Toggle chat window
    chatIcon.addEventListener('click', () => {
        isOpen = !isOpen;
        chatWindow.style.display = isOpen ? 'block' : 'none';
    });

    // Handle message sending
    function sendMessage() {
        const message = messageInput.value.trim();
        if (!message) return;

        // Add user message
        appendMessage(message, 'user');
        messageInput.value = '';

        // Send to backend
        fetch(oacbConfig.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': oacbConfig.nonce
            },
            body: JSON.stringify({ message })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                appendMessage(data.response, 'bot');
            } else {
                showError('Failed to get response');
            }
        })
        .catch(() => showError('Connection error'));
    }

    // Append message to chat
    function appendMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `oacb-message oacb-${sender}-message`;
        
        const content = document.createElement('div');
        content.className = 'oacb-message-content';
        content.textContent = text;
        
        messageDiv.appendChild(content);
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Error handling
    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'oacb-error';
        errorDiv.textContent = message;
        messagesContainer.appendChild(errorDiv);
    }

    // Event listeners
    sendButton.addEventListener('click', sendMessage);
    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
});
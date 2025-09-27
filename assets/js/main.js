document.addEventListener('DOMContentLoaded', () => {
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message-input');
    const chatMessages = document.getElementById('chat-messages');

    chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const messageText = messageInput.value.trim();

        if (messageText) {
            appendMessage(messageText, 'user');
            messageInput.value = '';
            
            showBotTyping();

            // Send message to backend
            fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: messageText })
            })
            .then(response => response.json())
            .then(data => {
                removeBotTyping();
                appendMessage(data.reply, 'bot');
            })
            .catch(error => {
                removeBotTyping();
                console.error('Error:', error);
                appendMessage('Извините, произошла ошибка. Попробуйте еще раз.', 'bot');
            });
        }
    });

    function appendMessage(text, sender) {
        const messageWrapper = document.createElement('div');
        messageWrapper.classList.add('message', `${sender}-message`);

        const messageContent = document.createElement('div');
        messageContent.classList.add('message-content');

        const paragraph = document.createElement('p');
        paragraph.innerHTML = text; // Use innerHTML to render potential HTML tags from response

        messageContent.appendChild(paragraph);
        messageWrapper.appendChild(messageContent);
        chatMessages.appendChild(messageWrapper);

        scrollToBottom();
    }
    
    function showBotTyping() {
        const typingIndicator = document.createElement('div');
        typingIndicator.id = 'typing-indicator';
        typingIndicator.classList.add('message', 'bot-message');
        
        const content = document.createElement('div');
        content.classList.add('message-content');
        
        const p = document.createElement('p');
        p.textContent = '...';
        
        content.appendChild(p);
        typingIndicator.appendChild(content);
        chatMessages.appendChild(typingIndicator);
        scrollToBottom();
    }

    function removeBotTyping() {
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Initial scroll to bottom if content is already there
    scrollToBottom();
});
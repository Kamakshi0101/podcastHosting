// Chatbot functionality
const chatbot = {
    messages: [],
    isOpen: false,

    // Common responses for podcast-related queries
    responses: {
        hosting: "We offer professional podcast hosting with unlimited storage, analytics, and distribution to major platforms. Our hosting includes CDN delivery, backup storage, and RSS feed management.",
        recording: "Our platform provides high-quality recording tools with noise reduction and multi-track support. We recommend using a good USB microphone like Blue Yeti or Audio-Technica ATR2100x-USB for best results.",
        editing: "Use our intuitive editing suite to trim, enhance, and perfect your podcast episodes. Features include noise reduction, audio leveling, intro/outro insertion, and multi-track editing.",
        distribution: "Distribute your podcast to Spotify, Apple Podcasts, Google Podcasts, and more with one click. We handle all the technical requirements and feed validation automatically.",
        analytics: "Track your podcast's performance with detailed analytics including listener demographics, engagement metrics, download trends, and geographic distribution of your audience.",
        monetization: "You can monetize your podcast through sponsorships, listener support/donations, premium content, merchandise, or affiliate marketing. We provide tools to help manage these revenue streams.",
        promotion: "Promote your podcast effectively through social media integration, SEO optimization, cross-promotion opportunities, and our built-in marketing tools.",
        equipment: "For professional sound quality, we recommend: 1) A good USB microphone (Blue Yeti/Audio-Technica) 2) Pop filter 3) Headphones 4) Quiet recording space. Need specific recommendations?",
        default: "How can I help you with your podcasting journey today? You can ask about hosting, recording, editing, distribution, analytics, monetization, promotion, or equipment."
    },

    // Initialize chatbot
    init() {
        this.createChatInterface();
        this.bindEvents();
    },

    // Create chat interface
    createChatInterface() {
        const chatHTML = `
            <div class="chat-bot-container" style="display: none;">
                <div class="chat-header">
                    <h3>PodcastPro Assistant</h3>
                    <button class="close-chat">×</button>
                </div>
                <div class="chat-messages"></div>
                <div class="chat-input-container">
                    <input type="text" class="chat-input" placeholder="Type your message...">
                    <button class="send-message">Send</button>
                </div>
            </div>
            <button class="chat-bot-toggle">
                <i class="fas fa-comments"></i>
            </button>
        `;

        const chatContainer = document.createElement('div');
        chatContainer.classList.add('chat-bot-wrapper');
        chatContainer.innerHTML = chatHTML;
        document.body.appendChild(chatContainer);
    },

    // Bind event listeners
    bindEvents() {
        const toggleBtn = document.querySelector('.chat-bot-toggle');
        const closeBtn = document.querySelector('.close-chat');
        const sendBtn = document.querySelector('.send-message');
        const chatInput = document.querySelector('.chat-input');

        toggleBtn.addEventListener('click', () => this.toggleChat());
        closeBtn.addEventListener('click', () => this.toggleChat());
        sendBtn.addEventListener('click', () => this.sendMessage());
        
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.sendMessage();
        });
    },

    // Toggle chat window
    toggleChat() {
        const chatContainer = document.querySelector('.chat-bot-container');
        const toggleBtn = document.querySelector('.chat-bot-toggle');
        this.isOpen = !this.isOpen;
        
        chatContainer.style.display = this.isOpen ? 'flex' : 'none';
        toggleBtn.classList.toggle('active');

        if (this.isOpen && this.messages.length === 0) {
            this.addMessage('bot', 'Hi! How can I help you with your podcasting needs today?');
        }
    },

    // Send message
    sendMessage() {
        const chatInput = document.querySelector('.chat-input');
        const message = chatInput.value.trim();

        if (message) {
            this.addMessage('user', message);
            chatInput.value = '';
            this.processMessage(message);
        }
    },

    // Add message to chat
    addMessage(sender, text) {
        const chatMessages = document.querySelector('.chat-messages');
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('chat-message', sender);
        messageDiv.innerHTML = `<p>${text}</p>`;
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        this.messages.push({ sender, text });
    },

    // Process incoming message and generate response
    processMessage(message) {
        const lowerMessage = message.toLowerCase();
        let response = "I can't help with that.";

        if (lowerMessage.includes('hi')) {
            response = "Hello! Would you like to know more about Podcast Hosting or Podcast Listening?";
        } else if (lowerMessage.includes('host') || lowerMessage.includes('hosting')) {
            response = this.responses.hosting;
        } else if (lowerMessage.includes('record') || lowerMessage.includes('recording')) {
            response = this.responses.recording;
        } else if (lowerMessage.includes('edit') || lowerMessage.includes('editing')) {
            response = this.responses.editing;
        } else if (lowerMessage.includes('distribute') || lowerMessage.includes('distribution')) {
            response = this.responses.distribution;
        } else if (lowerMessage.includes('analytics') || lowerMessage.includes('stats')) {
            response = this.responses.analytics;
        } else if (lowerMessage.includes('money') || lowerMessage.includes('monetize') || lowerMessage.includes('revenue')) {
            response = this.responses.monetization;
        } else if (lowerMessage.includes('promote') || lowerMessage.includes('marketing') || lowerMessage.includes('grow')) {
            response = this.responses.promotion;
        } else if (lowerMessage.includes('equipment') || lowerMessage.includes('microphone') || lowerMessage.includes('mic') || lowerMessage.includes('gear')) {
            response = this.responses.equipment;
        } else if (lowerMessage.includes('listen') || lowerMessage.includes('listening')) {
            response = "You can listen to podcasts on various platforms like Spotify, Apple Podcasts, and Google Podcasts. Check out our website for exclusive content and features.";
        }

        setTimeout(() => this.addMessage('bot', response), 500);
    }
};

// Initialize chatbot when DOM is loaded
document.addEventListener('DOMContentLoaded', () => chatbot.init());
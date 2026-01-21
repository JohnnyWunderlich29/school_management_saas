<div x-data="miniChat()" x-init="init()" id="mini-chat-root"
    class="fixed right-6 z-[99999] bottom-[70px] md:bottom-[20px]"
    style="position: fixed !important; right: 1.5rem !important; display: flex !important; flex-direction: column !important; align-items: flex-end !important; pointer-events: auto !important; visibility: visible !important;">

    <!-- Widget Container -->
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="bg-white w-80 md:w-96 h-[32rem] rounded-lg shadow-2xl flex flex-col overflow-hidden border border-gray-200 mb-4"
        style="display: none;">

        <!-- Header -->
        <div class="bg-indigo-700 p-3 text-white flex justify-between items-center shadow-sm shrink-0">
            <div class="flex items-center">
                <button x-show="currentView === 'chat'" @click="showList()"
                    class="mr-2 hover:bg-indigo-600 rounded-full p-1 transition">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <h3 class="font-semibold text-sm"
                    x-text="currentView === 'list' ? 'Mensagens' : activeConversationTitle"></h3>
            </div>
            <div class="flex items-center space-x-2">
                <button @click="minimize()" class="hover:bg-indigo-600 rounded p-1 transition" title="Minimizar">
                    <i class="fas fa-minus text-xs"></i>
                </button>
                <button @click="closeWidget()" class="hover:bg-indigo-600 rounded p-1 transition" title="Fechar">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        </div>

        <!-- Body: Conversation List -->
        <div x-show="currentView === 'list'" class="flex-1 overflow-y-auto bg-gray-50">
            <template x-if="conversations.length === 0 && !loading">
                <div class="flex flex-col items-center justify-center h-full text-gray-500 p-4 text-center">
                    <i class="far fa-comments text-3xl mb-2 text-gray-300"></i>
                    <p class="text-sm">Nenhuma conversa recente.</p>
                    <a href="{{ route('conversas.index') }}" class="text-xs text-indigo-600 hover:underline mt-2">Ver
                        todas</a>
                </div>
            </template>

            <template x-for="conv in conversations" :key="conv.id">
                <div @click="openConversation(conv)"
                    class="p-3 border-b border-gray-100 hover:bg-gray-100 cursor-pointer transition relative group">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 mr-3 relative">
                            <div
                                class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm">
                                <span x-text="conv.iniciais"></span>
                            </div>
                            <template x-if="conv.mensagens_nao_lidas > 0">
                                <span
                                    class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-bold shadow-sm"
                                    x-text="conv.mensagens_nao_lidas"></span>
                            </template>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-baseline mb-0.5">
                                <h4 class="text-sm font-medium text-gray-900 truncate pr-2" x-text="conv.titulo"></h4>
                                <span class="text-xs text-gray-400 flex-shrink-0" x-text="conv.updated_at"></span>
                            </div>
                            <p class="text-xs text-gray-500 truncate"
                                :class="{ 'font-semibold text-gray-800': conv.mensagens_nao_lidas > 0 }"
                                x-text="conv.ultima_mensagem || 'Sem mensagens'"></p>
                        </div>
                    </div>
                </div>
            </template>

            <div x-show="loading" class="flex justify-center p-4">
                <i class="fas fa-spinner fa-spin text-indigo-500"></i>
            </div>
        </div>

        <!-- Body: Chat Messages -->
        <div x-show="currentView === 'chat'" class="flex-1 flex flex-col bg-gray-50 min-h-0">
            <div id="mini-chat-messages" class="flex-1 overflow-y-auto p-3 space-y-3 min-h-0">
                <template x-for="msg in messages" :key="msg.id">
                    <div class="flex flex-col" :class="msg.is_own ? 'items-end' : 'items-start'">
                        <div class="max-w-[85%] rounded-lg px-[15px] py-[10px] shadow-sm text-sm relative"
                            :class="msg.is_own ? 'bg-indigo-600 text-white rounded-br-none' :
                                'bg-white text-gray-800 rounded-bl-none border border-gray-100'">

                            <template x-if="!msg.is_own">
                                <span class="text-[10px] font-bold opacity-75 block mb-0.5"
                                    x-text="msg.remetente.name"></span>
                            </template>

                            <p class="break-words whitespace-pre-wrap" x-text="msg.conteudo"></p>

                            <!-- File Attachment Indicator -->
                            <template x-if="msg.arquivo_url">
                                <div class="mt-2 text-xs flex items-center p-1 rounded bg-black/10">
                                    <i class="fas fa-paperclip mr-1"></i>
                                    <span class="truncate">Anexo</span>
                                </div>
                            </template>

                            <div class="text-[10px] text-right mt-1 opacity-70 flex justify-end items-center space-x-1">
                                <span x-text="msg.created_at"></span>
                                <template x-if="msg.is_own">
                                    <span>
                                        <i class="fas fa-check-double"
                                            :class="msg.status === 'Lido' ? 'text-blue-300' : 'text-gray-300'"></i>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="loadingMessages" class="flex justify-center p-2">
                    <i class="fas fa-spinner fa-spin text-gray-400 text-xs"></i>
                </div>
            </div>

            <!-- Input Area -->
            <div class="p-2 bg-white border-t border-gray-200 shrink-0">
                <div class="relative flex items-center">
                    <input type="text" x-model="newMessage" @keydown.enter="sendMessage()"
                        placeholder="Digite uma mensagem..." maxlength="300"
                        class="w-full pl-3 pr-10 py-2 text-sm bg-gray-100 border-none rounded-full focus:ring-1 focus:ring-indigo-500">
                    <button @click="sendMessage()"
                        class="absolute right-1 text-indigo-600 hover:text-indigo-800 p-1.5 rounded-full transition"
                        :disabled="!newMessage.trim()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
                <div class="text-[10px] text-gray-400 text-center mt-1">
                    <a :href="'/conversas/' + activeConversationId" class="hover:underline">Abrir chat completo</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Button -->
    <button @click="toggle()" id="mini-chat-btn"
        class="bg-indigo-600 hover:bg-indigo-700 text-white w-14 h-14 rounded-full shadow-lg flex items-center justify-center transition transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 relative z-50"
        style="pointer-events: auto !important; opacity: 1 !important; visibility: visible !important;">
        <i class="fas fa-comment-dots text-2xl" x-show="!isOpen"></i>
        <i class="fas fa-chevron-down text-xl" x-show="isOpen" style="display: none;"></i>

        <template x-if="unreadCount > 0 && !isOpen">
            <span
                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center shadow border-2 border-white"
                x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
        </template>
    </button>
</div>

<script>
    function miniChat() {
        return {
            isOpen: false,
            currentView: 'list', // 'list' or 'chat'
            conversations: [],
            messages: [],
            activeConversationId: null,
            activeConversationTitle: '',
            unreadCount: 0,
            loading: false,
            loadingMessages: false,
            newMessage: '',
            pollInterval: null,
            userId: {{ Auth::id() }},

            init() {
                console.log('MiniChat: Initializing...');
                const root = document.getElementById('mini-chat-root');
                const btn = document.getElementById('mini-chat-btn');
                console.log('MiniChat: Root element:', root);
                console.log('MiniChat: Button element:', btn);
                if (root) {
                    const rect = root.getBoundingClientRect();
                    console.log('MiniChat: Root Rect:', rect);
                }

                // Check local storage for preference
                const wasOpen = localStorage.getItem('miniChatOpen') === 'true';
                if (wasOpen) {
                    this.isOpen = true;
                    this.loadConversations();
                }

                // Initial load of unread count
                this.updateUnreadCount();

                // Start polling
                this.startPolling();

                // Listen for custom events if needed
                window.addEventListener('chat-update', () => this.updateUnreadCount());
                console.log('MiniChat: Initialized');
            },

            toggle() {
                this.isOpen = !this.isOpen;
                localStorage.setItem('miniChatOpen', this.isOpen);

                if (this.isOpen) {
                    this.loadConversations();
                    // Scroll down if in chat view
                    if (this.currentView === 'chat') {
                        this.$nextTick(() => this.scrollToBottom());
                    }
                }
            },

            minimize() {
                this.isOpen = false;
                localStorage.setItem('miniChatOpen', 'false');
            },

            closeWidget() {
                this.isOpen = false;
                localStorage.setItem('miniChatOpen', 'false');
                this.currentView = 'list';
                this.activeConversationId = null;
            },

            showList() {
                this.currentView = 'list';
                this.activeConversationId = null;
                this.loadConversations(); // Refresh list to update unread counts
            },

            async updateUnreadCount() {
                try {
                    const response = await fetch('{{ route('conversas.total-nao-lidas') }}');
                    const data = await response.json();
                    this.unreadCount = data.total;
                } catch (error) {
                    console.error('Erro ao buscar total de não lidas', error);
                }
            },

            async loadConversations() {
                this.loading = true;
                try {
                    const response = await fetch('{{ route('conversas.lista') }}');
                    const data = await response.json();
                    this.conversations = data.conversas;
                } catch (error) {
                    console.error('Erro ao carregar conversas', error);
                } finally {
                    this.loading = false;
                }
            },

            async openConversation(conv) {
                this.activeConversationId = conv.id;
                this.activeConversationTitle = conv.titulo;
                this.currentView = 'chat';
                this.messages = [];
                this.loadingMessages = true;

                // Mark locally as read (will be updated on server refresh)
                const convIndex = this.conversations.findIndex(c => c.id === conv.id);
                if (convIndex !== -1) {
                    this.unreadCount = Math.max(0, this.unreadCount - this.conversations[convIndex]
                        .mensagens_nao_lidas);
                    this.conversations[convIndex].mensagens_nao_lidas = 0;
                }

                try {
                    // Use carregar-mensagens for better performance and error handling
                    const response = await fetch(`/conversas/${conv.id}/carregar-mensagens`);
                    if (!response.ok) throw new Error('Falha ao carregar mensagens');

                    const data = await response.json();

                    if (data.mensagens) {
                        this.messages = data.mensagens;
                        this.scrollToBottom();

                        // Mark as read on server
                        fetch(`/conversas/${conv.id}/marcar-lida`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        });
                    }
                } catch (error) {
                    console.error('Erro ao carregar mensagens', error);
                } finally {
                    this.loadingMessages = false;
                }
            },

            async sendMessage() {
                if (!this.newMessage.trim() || !this.activeConversationId) return;

                const content = this.newMessage;
                this.newMessage = ''; // Optimistic clear

                try {
                    const formData = new FormData();
                    formData.append('conteudo', content);

                    const response = await fetch(`/conversas/${this.activeConversationId}/mensagens`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content'),
                            'Accept': 'application/json' // Important for JSON response
                        },
                        body: formData
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (data.mensagem) {
                            this.messages.push(data.mensagem);
                            this.scrollToBottom();
                        }
                    } else {
                        // Error handling
                        console.error('Erro ao enviar mensagem');
                        this.newMessage = content; // Restore
                    }
                } catch (error) {
                    console.error('Erro ao enviar mensagem', error);
                    this.newMessage = content; // Restore
                }
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    const container = document.getElementById('mini-chat-messages');
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                });
            },

            startPolling() {
                // Poll every 30 seconds for unread count
                setInterval(() => {
                    this.updateUnreadCount();
                    if (this.isOpen && this.currentView === 'list') {
                        this.loadConversations();
                    } else if (this.isOpen && this.currentView === 'chat' && this.activeConversationId) {
                        this.pollMessages();
                    }
                }, 30000);
            },

            async pollMessages() {
                // Simple poll for new messages in active chat
                // In a real app we'd use 'after' param with last message ID
                if (!this.activeConversationId) return;

                try {
                    const lastId = this.messages.length > 0 ? this.messages[this.messages.length - 1].id : 0;

                    const response = await fetch(
                        `/conversas/${this.activeConversationId}/carregar-mensagens?latest=true&last_message_id=${lastId}`
                    );
                    if (!response.ok) throw new Error('Falha ao poll mensagens');

                    const data = await response.json();

                    if (data.mensagens && data.mensagens.length > 0) {
                        this.messages = [...this.messages, ...data.mensagens];
                        this.scrollToBottom();

                        // Mark read again
                        fetch(`/conversas/${this.activeConversationId}/marcar-lida`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content')
                            }
                        });
                    }
                } catch (e) {
                    console.error(e);
                }
            }
        }
    }
</script>

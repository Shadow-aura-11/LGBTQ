<?php
include __DIR__ . '/header.php';

// Access Control
if (!$currentUser) {
    header('Location: /login');
    exit;
}

if (($currentUser['tier'] ?? 'free') !== 'premium') {
    echo "<div class='glass-panel p-12 rounded-3xl text-center border border-white/60 my-12 max-w-xl mx-auto shadow-xl'>";
    echo "<span class='text-5xl'>👑</span>";
    echo "<h3 class='text-3xl font-extrabold text-gray-900 mt-5 serif-font'>Unlock Live Messaging</h3>";
    echo "<p class='text-gray-600 text-sm mt-2 mb-6 leading-relaxed'>Direct real-time conversations, online statuses, and typing receipts are exclusive to PrideUnion Premium members.</p>";
    echo "<a href='/subscription' class='btn-primary px-8 py-3.5 rounded-full font-bold shadow-md hover:shadow-lg transition'>Upgrade to Premium</a>";
    echo "</div>";
    include __DIR__ . '/footer.php';
    exit;
}

$recipientId = isset($_GET['recipient_id']) ? (int)$_GET['recipient_id'] : 0;
$recipientProfile = null;

if ($recipientId) {
    $res = makeApiRequest('GET', "/api/v1/profiles/internal/{$recipientId}", [], $token);
    if ($res['status'] === 200 && isset($res['data']['profile'])) {
        $recipientProfile = $res['data']['profile'];
    }
}
?>

<div class="glass-panel rounded-3xl border border-white/60 shadow-xl overflow-hidden h-[80vh] flex flex-col md:flex-row">
    <!-- Chat Contacts Sidebar -->
    <div class="w-full md:w-80 border-r border-gray-200/50 bg-white/40 p-4 flex flex-col gap-4">
        <h3 class="font-extrabold text-gray-800 text-lg border-b border-gray-100 pb-3 flex items-center gap-2">
            <span>💬</span> Conversations
        </h3>
        
        <div class="flex-grow overflow-y-auto space-y-2">
            <?php if ($recipientProfile): 
                $rPhotos = json_decode($recipientProfile['photos'] ?? '[]', true) ?: [];
                $rPhoto = !empty($rPhotos) ? $rPhotos[0] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80';
            ?>
                <!-- Active Conversation List Item -->
                <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-gradient-to-r from-pink-500/10 to-indigo-500/5 border border-pink-200/50 shadow-sm cursor-pointer hover:bg-pink-50 transition">
                    <div class="relative w-12 h-12 rounded-full overflow-hidden border border-pink-300">
                        <img src="<?= htmlspecialchars($rPhoto) ?>" class="w-full h-full object-cover">
                        <span id="contact-status-dot" class="absolute bottom-0 right-0 bg-green-500 border-2 border-white w-3.5 h-3.5 rounded-full shadow-sm animate-pulse"></span>
                    </div>
                    <div class="flex-grow">
                        <div class="flex justify-between items-start">
                            <h4 class="font-bold text-sm text-gray-800 line-clamp-1"><?= htmlspecialchars($recipientProfile['name']) ?></h4>
                        </div>
                        <span id="contact-status-txt" class="text-[10px] text-green-600 font-bold">online</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-12 px-4 space-y-2">
                    <span class="text-3xl">👥</span>
                    <p class="text-xs text-gray-500 font-medium">Select a profile matching your orientation criteria to trigger direct chats.</p>
                    <a href="/discovery" class="inline-block text-xs font-bold text-pink-600 hover:underline">Find Matches &rarr;</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Active Conversation window -->
    <div class="flex-grow flex flex-col justify-between bg-white/10 h-full relative">
        <?php if ($recipientProfile): ?>
            <!-- Active Conversation Header -->
            <div class="glass-panel px-6 py-4 border-b border-gray-200/50 flex justify-between items-center shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="relative w-10 h-10 rounded-full overflow-hidden">
                        <img src="<?= htmlspecialchars($rPhoto) ?>" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($recipientProfile['name']) ?></h4>
                        <p class="text-xs text-gray-500 flex items-center gap-2">
                            <span>Pronouns: <?= htmlspecialchars($recipientProfile['pronouns'] ?: 'they/them') ?></span>
                            <span class="text-gray-300">•</span>
                            <span id="typing-indicator" class="hidden text-pink-600 font-extrabold animate-pulse">typing...</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Scrollable Conversation history logs -->
            <div id="messages-log" class="flex-grow p-6 overflow-y-auto space-y-4 bg-gradient-to-b from-white/10 to-pink-50/10">
                <!-- Appended via JavaScript -->
            </div>

            <!-- Chat Inputs bar -->
            <div class="p-4 border-t border-gray-200/50 bg-white/50 backdrop-blur">
                <form id="chat-send-form" class="flex gap-3">
                    <input type="text" id="chat-msg-input" required placeholder="Type an inclusive message..." autocomplete="off"
                           class="flex-grow px-4 py-3 rounded-2xl border border-gray-300 focus:ring-2 focus:ring-pink-400 focus:border-transparent outline-none transition bg-white/70 shadow-sm text-sm">
                    <button type="submit" class="btn-primary px-6 py-3 rounded-2xl font-bold transition shadow-md hover:shadow-lg text-sm">
                        Send
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-6 space-y-3">
                <span class="text-6xl">💬</span>
                <h4 class="font-bold text-gray-700 text-lg serif-font">Start a Conversation</h4>
                <p class="text-gray-500 text-xs max-w-xs leading-relaxed">Direct messaging lets premium users talk, exchange contact details, and arrange real-life dates immediately.</p>
                <a href="/discovery" class="btn-primary px-5 py-2.5 rounded-full text-xs font-bold shadow-md">Find Compatible Matches</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    <?php if ($recipientProfile): ?>
        const recipientId = <?= $recipientId ?>;
        const currentUserId = <?= $currentUser['id'] ?>;
        
        async function loadHistory() {
            try {
                const res = await fetch(`/api/v1/chats/history/${recipientId}`, {
                    headers: { 'Authorization': 'Bearer ' + '<?= $token ?>' }
                });
                const data = await res.json();
                
                if (data.success) {
                    const log = document.getElementById('messages-log');
                    log.innerHTML = '';

                    data.history.forEach(msg => {
                        appendMessage(msg.sender_id, msg.message, msg.created_at, msg.is_read);
                    });
                    scrollToBottom();
                }
            } catch (err) {
                console.error('Error loading chat logs.', err);
            }
        }

        function appendMessage(senderId, text, time, isRead = false) {
            const log = document.getElementById('messages-log');
            const isMe = (parseInt(senderId) === currentUserId);
            
            const outerDiv = document.createElement('div');
            outerDiv.className = `flex ${isMe ? 'justify-end' : 'justify-start'}`;

            const innerDiv = document.createElement('div');
            innerDiv.className = `max-w-xs md:max-w-md p-3.5 rounded-2xl text-sm shadow-sm ${
                isMe ? 'bg-pink-500 text-white rounded-br-none' : 'glass-panel bg-white/95 text-gray-800 rounded-bl-none border border-gray-200/50'
            }`;
            
            const timestamp = new Date(time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            
            innerDiv.innerHTML = `
                <p class="leading-relaxed">${escapeHtml(text)}</p>
                <div class="flex justify-end gap-1 items-center mt-1.5 text-[9px] ${isMe ? 'text-pink-200' : 'text-gray-400'}">
                    <span>${timestamp}</span>
                    ${isMe ? `<span>${isRead ? '✓✓' : '✓'}</span>` : ''}
                </div>
            `;
            
            outerDiv.appendChild(innerDiv);
            log.appendChild(outerDiv);
        }

        function scrollToBottom() {
            const log = document.getElementById('messages-log');
            log.scrollTop = log.scrollHeight;
        }

        function escapeHtml(text) {
            return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
        }

        ws.onmessage = function(event) {
            const data = JSON.parse(event.data);
            
            if (data.type === 'message' && parseInt(data.message.sender_id) === recipientId) {
                appendMessage(data.message.sender_id, data.message.message, data.message.created_at);
                scrollToBottom();
                
                // Confirm read
                fetch(`/api/v1/chats/read/${data.message.id}`, {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + '<?= $token ?>' }
                });
            } else if (data.type === 'typing' && parseInt(data.sender_id) === recipientId) {
                const indicator = document.getElementById('typing-indicator');
                if (data.typing) {
                    indicator.classList.remove('hidden');
                } else {
                    indicator.classList.add('hidden');
                }
            } else if (data.type === 'status' && parseInt(data.user_id) === recipientId) {
                updateOnlineStatus(data.status);
            } else if (data.type === 'notification') {
                showToast(data.notification.title, data.notification.message);
            }
        };

        function updateOnlineStatus(status) {
            const dot = document.getElementById('contact-status-dot');
            const txt = document.getElementById('contact-status-txt');
            if (status === 'online') {
                dot.className = "absolute bottom-0 right-0 bg-green-500 border-2 border-white w-3.5 h-3.5 rounded-full shadow-sm animate-pulse";
                txt.innerText = "online";
                txt.className = "text-[10px] text-green-600 font-bold";
            } else {
                dot.className = "absolute bottom-0 right-0 bg-gray-400 border-2 border-white w-3.5 h-3.5 rounded-full";
                txt.innerText = "offline";
                txt.className = "text-[10px] text-gray-400";
            }
        }

        let typingTimeout;
        const msgInput = document.getElementById('chat-msg-input');
        msgInput.addEventListener('input', () => {
            ws.send(JSON.stringify({ type: 'typing', recipient_id: recipientId, typing: true }));
            
            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => {
                ws.send(JSON.stringify({ type: 'typing', recipient_id: recipientId, typing: false }));
            }, 2000);
        });

        document.getElementById('chat-send-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const text = msgInput.value.trim();
            if (!text) return;

            msgInput.value = '';
            ws.send(JSON.stringify({ type: 'typing', recipient_id: recipientId, typing: false }));

            try {
                const res = await fetch('/api/v1/chats/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + '<?= $token ?>'
                    },
                    body: JSON.stringify({ recipient_id: recipientId, message: text })
                });
                const data = await res.json();

                if (data.success) {
                    appendMessage(data.message.sender_id, data.message.message, data.message.created_at);
                    scrollToBottom();
                }
            } catch (err) {
                console.error(err);
            }
        });

        window.addEventListener('load', () => {
            loadHistory();
            setTimeout(() => {
                updateOnlineStatus('online'); // Mock active online state
            }, 1000);
        });
    <?php endif; ?>
</script>

<?php include __DIR__ . '/footer.php'; ?>

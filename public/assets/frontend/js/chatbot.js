(() => {
    'use strict';

    const root = document.getElementById('wlChatbot');
    if (!root) return;

    const toggle = document.getElementById('wlChatbotToggle');
    const close = document.getElementById('wlChatbotClose');
    const reset = document.getElementById('wlChatbotReset');
    const panel = document.getElementById('wlChatbotPanel');
    const form = document.getElementById('wlChatbotForm');
    const input = document.getElementById('wlChatbotInput');
    const messages = document.getElementById('wlChatbotMessages');
    const typing = document.getElementById('wlChatbotTyping');
    const quickReplies = document.getElementById('wlChatbotQuickReplies');
    const leadLayer = document.getElementById('wlChatbotLeadLayer');
    const leadForm = document.getElementById('wlChatbotLeadForm');
    const leadClose = document.getElementById('wlChatbotLeadClose');
    const leadError = document.getElementById('wlChatbotLeadError');
    const storageKey = 'travel_platform_chatbot_session';

    let sessionToken = localStorage.getItem(storageKey) || '';
    let historyLoaded = false;
    let isSending = false;

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const nowLabel = () => new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date());

    const scrollBottom = () => {
        requestAnimationFrame(() => {
            messages.scrollTop = messages.scrollHeight;
        });
    };

    const openPanel = async () => {
        root.classList.add('is-open');
        panel.setAttribute('aria-hidden', 'false');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.classList.add('wl-chatbot-open');

        if (!historyLoaded && sessionToken) {
            await loadHistory();
        }

        setTimeout(() => input.focus(), 160);
        scrollBottom();
    };

    const closePanel = () => {
        root.classList.remove('is-open');
        panel.setAttribute('aria-hidden', 'true');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('wl-chatbot-open');
        closeLeadForm();
    };

    const addMessage = (sender, text, options = {}) => {
        const wrapper = document.createElement('div');
        wrapper.className = `wl-chatbot-message ${sender === 'visitor' ? 'is-user' : 'is-bot'}`;

        const avatar = sender === 'visitor'
            ? ''
            : '<div class="wl-chatbot-avatar"><i class="bi bi-stars"></i></div>';

        wrapper.innerHTML = `${avatar}<div class="wl-chatbot-bubble"><p>${escapeHtml(text)}</p><time>${escapeHtml(options.time || nowLabel())}</time></div>`;
        messages.appendChild(wrapper);

        if (Array.isArray(options.packages) && options.packages.length) {
            renderPackages(options.packages);
        }

        if (options.handoffUrl && options.handoffLabel) {
            const link = document.createElement('a');
            link.className = 'wl-chatbot-handoff';
            link.href = options.handoffUrl;
            link.target = '_blank';
            link.rel = 'noopener';
            link.innerHTML = `<i class="bi bi-box-arrow-up-right"></i>${escapeHtml(options.handoffLabel)}`;
            messages.appendChild(link);
        }

        scrollBottom();
    };

    const renderPackages = (packages) => {
        const list = document.createElement('div');
        list.className = 'wl-chatbot-package-list';

        packages.forEach((pkg) => {
            const card = document.createElement('article');
            card.className = 'wl-chatbot-package';
            const seat = pkg.remaining_seat === null || pkg.remaining_seat === undefined
                ? 'Belum tersedia'
                : `${Number(pkg.remaining_seat)} seat`;

            card.innerHTML = `
                <div class="wl-chatbot-package-top">
                    <div class="wl-chatbot-package-tags">
                        ${pkg.program_label ? `<span class="program">Program ${escapeHtml(pkg.program_label)}</span>` : ''}
                        ${pkg.badge ? `<span>${escapeHtml(pkg.badge)}</span>` : ''}
                    </div>
                    <h4>${escapeHtml(pkg.name)}</h4>
                </div>
                <div class="wl-chatbot-package-meta">
                    <div><small>Harga</small><strong>${escapeHtml(pkg.price_label)}</strong></div>
                    <div><small>Durasi</small><strong>${escapeHtml(pkg.duration_label)}</strong></div>
                    <div><small>Keberangkatan</small><strong>${escapeHtml(pkg.departure_label)}</strong></div>
                    <div><small>Ketersediaan</small><strong>${escapeHtml(seat)}</strong></div>
                </div>
                <div class="wl-chatbot-package-actions">
                    <a href="${escapeHtml(pkg.detail_url)}"><i class="bi bi-eye"></i>Lihat Detail</a>
                    <button type="button" class="js-chatbot-interest"><i class="bi bi-person-check"></i>Saya Tertarik</button>
                </div>`;

            const interestButton = card.querySelector('.js-chatbot-interest');
            interestButton.addEventListener('click', () => openLeadForm(pkg));
            list.appendChild(card);
        });

        messages.appendChild(list);
        scrollBottom();
    };

    const updateQuickReplies = (items) => {
        if (!Array.isArray(items) || !items.length) return;
        quickReplies.innerHTML = '';

        items.forEach((item) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = item.label;
            button.dataset.chatMessage = item.message;
            quickReplies.appendChild(button);
        });
    };

    const showTyping = (visible) => {
        typing.hidden = !visible;
        if (visible) scrollBottom();
    };

    const sendMessage = async (text) => {
        text = String(text || '').trim();
        if (!text || isSending) return;

        isSending = true;
        addMessage('visitor', text);
        input.value = '';
        input.style.height = 'auto';
        showTyping(true);
        form.querySelector('button[type="submit"]').disabled = true;

        try {
            const response = await fetch(root.dataset.messageUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    message: text,
                    session_token: sessionToken,
                    source_page: root.dataset.sourcePage,
                }),
            });

            const json = await response.json();
            showTyping(false);

            if (!response.ok || !json.success) {
                addMessage('bot', json.message || 'Chatbot sedang mengalami kendala. Silakan coba kembali.');
                return;
            }

            const data = json.data || {};
            if (data.session_token) {
                sessionToken = data.session_token;
                localStorage.setItem(storageKey, sessionToken);
            }

            addMessage('bot', data.reply || 'Mohon maaf, jawaban belum tersedia.', {
                packages: data.packages || [],
                handoffUrl: data.handoff_url || null,
                handoffLabel: data.handoff_label || null,
            });
            updateQuickReplies(data.quick_replies || []);
        } catch (error) {
            showTyping(false);
            addMessage('bot', 'Koneksi chatbot sedang terganggu. Silakan periksa koneksi dan coba lagi.');
        } finally {
            isSending = false;
            form.querySelector('button[type="submit"]').disabled = false;
            input.focus();
        }
    };

    const loadHistory = async () => {
        historyLoaded = true;
        try {
            const response = await fetch(`${root.dataset.historyUrl}/${encodeURIComponent(sessionToken)}`, {
                headers: { 'Accept': 'application/json' },
            });
            const json = await response.json();
            if (!json.success || !Array.isArray(json.data) || !json.data.length) return;

            messages.innerHTML = '';
            json.data.forEach((message) => {
                const metadata = message.metadata || {};
                const time = message.created_at
                    ? new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit' }).format(new Date(message.created_at.replace(' ', 'T')))
                    : nowLabel();
                addMessage(message.sender, message.message, {
                    time,
                    packages: metadata.packages || [],
                });
            });
        } catch (error) {
            // Riwayat tidak wajib untuk memulai percakapan baru.
        }
    };

    const openLeadForm = (pkg) => {
        document.getElementById('wlChatbotLeadPackageName').textContent = pkg.name || 'Paket Travel';
        document.getElementById('wlChatbotLeadPackageId').value = pkg.id || '';
        document.getElementById('wlChatbotLeadDepartureId').value = pkg.departure_id || '';
        document.getElementById('wlChatbotLeadParticipants').value = pkg.participants || 1;
        leadError.hidden = true;
        leadError.textContent = '';
        leadLayer.hidden = false;
        setTimeout(() => document.getElementById('wlChatbotLeadName').focus(), 80);
    };

    const closeLeadForm = () => {
        leadLayer.hidden = true;
    };

    const submitLead = async (event) => {
        event.preventDefault();
        leadError.hidden = true;
        const submitButton = leadForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;

        const data = Object.fromEntries(new FormData(leadForm).entries());
        data.session_token = sessionToken;
        data.total_participants = Number(data.total_participants || 1);

        try {
            const response = await fetch(root.dataset.leadUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data),
            });
            const json = await response.json();

            if (!response.ok || !json.success) {
                const errors = json.errors ? Object.values(json.errors).join(' ') : '';
                leadError.textContent = `${json.message || 'Data belum dapat disimpan.'} ${errors}`.trim();
                leadError.hidden = false;
                return;
            }

            closeLeadForm();
            leadForm.reset();
            addMessage('bot', json.data.message || 'Data minat berhasil disimpan.');

            if (json.data.whatsapp_url) {
                const link = document.createElement('a');
                link.className = 'wl-chatbot-handoff';
                link.href = json.data.whatsapp_url;
                link.target = '_blank';
                link.rel = 'noopener';
                link.innerHTML = '<i class="bi bi-whatsapp"></i>Lanjutkan ke WhatsApp';
                messages.appendChild(link);
                scrollBottom();
            }
        } catch (error) {
            leadError.textContent = 'Koneksi sedang terganggu. Silakan coba kembali.';
            leadError.hidden = false;
        } finally {
            submitButton.disabled = false;
        }
    };

    const resetChat = () => {
        sessionToken = '';
        historyLoaded = true;
        localStorage.removeItem(storageKey);
        const welcome = messages.querySelector('[data-welcome="true"]');
        messages.innerHTML = welcome ? welcome.outerHTML : '';
        updateQuickReplies([
            { label: 'Paket Terdekat', message: 'Paket terdekat kapan?' },
            { label: 'Cek 10 Seat', message: 'Ada paket untuk 10 orang?' },
            { label: 'Harga Paket', message: 'Paket paling murah apa?' },
            { label: 'Alamat Kantor', message: 'Alamat kantor di mana?' },
        ]);
        closeLeadForm();
        scrollBottom();
    };

    toggle.addEventListener('click', openPanel);
    close.addEventListener('click', closePanel);
    reset.addEventListener('click', resetChat);
    leadClose.addEventListener('click', closeLeadForm);
    leadLayer.addEventListener('click', (event) => {
        if (event.target === leadLayer) closeLeadForm();
    });
    leadForm.addEventListener('submit', submitLead);

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        sendMessage(input.value);
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendMessage(input.value);
        }
    });

    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 100)}px`;
    });

    quickReplies.addEventListener('click', (event) => {
        const button = event.target.closest('[data-chat-message]');
        if (button) sendMessage(button.dataset.chatMessage);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && root.classList.contains('is-open')) {
            closePanel();
        }
    });
})();

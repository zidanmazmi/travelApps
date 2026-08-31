<?php
$siteName = site_setting('site_name', 'Travel Umroh & Haji');
$chatbotEnabled = site_setting('chatbot_enabled', '1') === '1';
$chatbotName = site_setting('chatbot_name', 'Asisten Travel');
$welcomeMessage = site_setting(
    'chatbot_welcome_message',
    'Assalamualaikum. Saya Asisten ' . $siteName . '. Saya dapat membantu mencari paket, jadwal terdekat, ketersediaan seat, harga, alamat kantor, dan informasi resmi lainnya.'
);
?>

<?php if ($chatbotEnabled) : ?>
<div
    class="wl-chatbot"
    id="wlChatbot"
    data-message-url="<?= base_url('/chatbot/message'); ?>"
    data-lead-url="<?= base_url('/chatbot/create-lead'); ?>"
    data-history-url="<?= base_url('/chatbot/history'); ?>"
    data-source-page="<?= esc(current_url()); ?>">

    <button
        type="button"
        class="wl-chatbot-toggle"
        id="wlChatbotToggle"
        aria-label="Buka chatbot <?= esc($siteName); ?>"
        aria-controls="wlChatbotPanel"
        aria-expanded="false">
        <span class="wl-chatbot-toggle-icon">
            <i class="bi bi-chat-dots-fill"></i>
        </span>
        <span class="wl-chatbot-toggle-copy">
            <small>Butuh bantuan?</small>
            <strong>Tanya <?= esc($chatbotName); ?></strong>
        </span>
        <span class="wl-chatbot-online-dot" aria-hidden="true"></span>
    </button>

    <section class="wl-chatbot-panel" id="wlChatbotPanel" aria-hidden="true" aria-label="Chatbot <?= esc($siteName); ?>">
        <header class="wl-chatbot-header">
            <div class="wl-chatbot-agent">
                <span class="wl-chatbot-agent-logo">
                    <img
                        src="<?= site_asset('logo'); ?>"
                        alt="Logo <?= esc($siteName); ?>">
                </span>
                <div>
                    <strong><?= esc($chatbotName); ?></strong>
                    <span><i></i> Online · Informasi resmi website</span>
                </div>
            </div>

            <div class="wl-chatbot-header-actions">
                <button type="button" id="wlChatbotReset" aria-label="Mulai percakapan baru" title="Percakapan baru">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
                <button type="button" id="wlChatbotClose" aria-label="Tutup chatbot">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </header>

        <div class="wl-chatbot-scope">
            <i class="bi bi-shield-check"></i>
            Jawaban diambil dari paket, jadwal, FAQ, dan informasi resmi <?= esc($siteName); ?>.
        </div>

        <div class="wl-chatbot-messages" id="wlChatbotMessages" aria-live="polite">
            <div class="wl-chatbot-message is-bot" data-welcome="true">
                <div class="wl-chatbot-avatar"><i class="bi bi-stars"></i></div>
                <div class="wl-chatbot-bubble">
                    <p><?= esc($welcomeMessage); ?></p>
                    <time>Sekarang</time>
                </div>
            </div>
        </div>

        <div class="wl-chatbot-typing" id="wlChatbotTyping" hidden>
            <span></span><span></span><span></span>
        </div>

        <div class="wl-chatbot-quick" id="wlChatbotQuickReplies">
            <button type="button" data-chat-message="Paket terdekat kapan?">Paket Terdekat</button>
            <button type="button" data-chat-message="Ada paket untuk 10 orang?">Cek 10 Seat</button>
            <button type="button" data-chat-message="Paket paling murah apa?">Harga Paket</button>
            <button type="button" data-chat-message="Alamat kantor di mana?">Alamat Kantor</button>
        </div>

        <form class="wl-chatbot-input" id="wlChatbotForm">
            <label class="visually-hidden" for="wlChatbotInput">Tulis pertanyaan</label>
            <textarea
                id="wlChatbotInput"
                rows="1"
                maxlength="500"
                placeholder="Contoh: Ada paket untuk 8 orang?"
                required></textarea>
            <button type="submit" aria-label="Kirim pesan">
                <i class="bi bi-send-fill"></i>
            </button>
        </form>

        <div class="wl-chatbot-lead-layer" id="wlChatbotLeadLayer" hidden>
            <div class="wl-chatbot-lead-card">
                <div class="wl-chatbot-lead-head">
                    <div>
                        <span>Form Minat Paket</span>
                        <strong id="wlChatbotLeadPackageName">Paket Perjalanan</strong>
                    </div>
                    <button type="button" id="wlChatbotLeadClose" aria-label="Tutup form">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <form id="wlChatbotLeadForm">
                    <input type="hidden" name="package_id" id="wlChatbotLeadPackageId">
                    <input type="hidden" name="departure_id" id="wlChatbotLeadDepartureId">

                    <div class="wl-chatbot-field">
                        <label for="wlChatbotLeadName">Nama Lengkap</label>
                        <input type="text" id="wlChatbotLeadName" name="full_name" minlength="3" maxlength="150" required>
                    </div>

                    <div class="wl-chatbot-field">
                        <label for="wlChatbotLeadPhone">Nomor WhatsApp</label>
                        <input type="tel" id="wlChatbotLeadPhone" name="phone" minlength="9" maxlength="30" placeholder="08xxxxxxxxxx" required>
                    </div>

                    <div class="wl-chatbot-field-row">
                        <div class="wl-chatbot-field">
                            <label for="wlChatbotLeadCity">Domisili</label>
                            <input type="text" id="wlChatbotLeadCity" name="city" maxlength="100" placeholder="Kota Anda">
                        </div>
                        <div class="wl-chatbot-field">
                            <label for="wlChatbotLeadParticipants">Jumlah Jamaah</label>
                            <input type="number" id="wlChatbotLeadParticipants" name="total_participants" min="1" max="50" value="1" required>
                        </div>
                    </div>

                    <div class="wl-chatbot-field">
                        <label for="wlChatbotLeadNotes">Catatan</label>
                        <textarea id="wlChatbotLeadNotes" name="notes" rows="2" maxlength="500" placeholder="Kebutuhan atau pertanyaan khusus"></textarea>
                    </div>

                    <div class="wl-chatbot-lead-error" id="wlChatbotLeadError" hidden></div>

                    <button type="submit" class="wl-chatbot-lead-submit">
                        <i class="bi bi-check2-circle"></i>
                        Simpan Minat
                    </button>

                    <small>Data akan masuk ke Reporting Leads agar admin dapat menindaklanjuti Anda.</small>
                </form>
            </div>
        </div>
    </section>
</div>
<?php endif; ?>

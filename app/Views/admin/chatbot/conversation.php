<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>
<div class="admin-page-title d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
    <div>
        <span>Conversation Detail</span>
        <h1><?= esc($chatSession['visitor_name'] ?: 'Pengunjung Website'); ?></h1>
        <p>Riwayat percakapan chatbot dan konteks pertanyaan pengunjung.</p>
    </div>
    <a href="<?= base_url('/admin/chatbot'); ?>" class="btn btn-admin-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<?php
$smartContext = !empty($chatSession['conversation_context_json'])
    ? json_decode($chatSession['conversation_context_json'], true)
    : [];
$smartContext = is_array($smartContext) ? $smartContext : [];
$contextLabels = [
    'price'        => 'Harga',
    'schedule'     => 'Jadwal',
    'duration'     => 'Durasi',
    'availability' => 'Ketersediaan Seat',
    'hotel'        => 'Hotel',
    'airline'      => 'Maskapai',
    'facilities'   => 'Fasilitas',
    'equipment'    => 'Perlengkapan',
    'document_deadline' => 'Batas Dokumen',
    'passport_requirements' => 'Ketentuan Paspor',
    'payment_method' => 'Metode Pembayaran',
    'deposit'      => 'Down Payment',
    'program'      => 'Program',
    'room_type_explanation' => 'Penjelasan Tipe Kamar',
];
?>
<div class="row g-4">
    <div class="col-xl-8">
        <div class="admin-card">
            <div class="admin-card-header"><h2>Riwayat Pesan</h2></div>
            <div class="admin-card-body" style="background:#f7f2e9;">
                <div class="d-grid gap-3">
                    <?php if (empty($messages)) : ?>
                        <div class="admin-empty"><i class="bi bi-chat-square-dots"></i> Belum ada pesan.</div>
                    <?php else : ?>
                        <?php foreach ($messages as $message) : ?>
                            <?php $isVisitor = ($message['sender'] ?? '') === 'visitor'; ?>
                            <div class="d-flex <?= $isVisitor ? 'justify-content-end' : 'justify-content-start'; ?>">
                                <div class="p-3 rounded-4 <?= $isVisitor ? 'text-white' : 'bg-white border'; ?>" style="max-width:78%; <?= $isVisitor ? 'background:#17130e;' : ''; ?>">
                                    <div class="small lh-lg" style="white-space:pre-line;"><?= esc($message['message']); ?></div>
                                    <div class="mt-2 d-flex justify-content-between gap-3 opacity-75" style="font-size:10px;">
                                        <span><?= $isVisitor ? 'Pengunjung' : 'Chatbot'; ?></span>
                                        <span><?= !empty($message['created_at']) ? date('d M Y H:i', strtotime($message['created_at'])) : '-'; ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="admin-card">
            <div class="admin-card-header"><h2>Informasi Sesi</h2></div>
            <div class="admin-card-body">
                <div class="d-grid gap-3">
                    <div><small class="text-muted d-block">Nama</small><strong><?= esc($chatSession['visitor_name'] ?: 'Belum diisi'); ?></strong></div>
                    <div><small class="text-muted d-block">WhatsApp</small><strong><?= esc($chatSession['visitor_phone'] ?: 'Belum diisi'); ?></strong></div>
                    <div><small class="text-muted d-block">Intent Terakhir</small><strong><?= esc($chatSession['last_intent'] ?: '-'); ?></strong></div>
                    <div><small class="text-muted d-block">Halaman Asal</small><span class="small text-break"><?= esc($chatSession['source_page'] ?: '-'); ?></span></div>
                    <div><small class="text-muted d-block">Aktivitas Terakhir</small><strong><?= !empty($chatSession['last_message_at']) ? date('d M Y H:i', strtotime($chatSession['last_message_at'])) : '-'; ?></strong></div>
                </div>
            </div>
        </div>

        <div class="admin-card mt-4">
            <div class="admin-card-header">
                <div>
                    <span class="lead-card-kicker">Smart Context</span>
                    <h2>Konteks Aktif</h2>
                </div>
            </div>
            <div class="admin-card-body">
                <?php if (empty(array_filter([
                    $smartContext['topic'] ?? null,
                    $smartContext['program'] ?? null,
                    $smartContext['package_name'] ?? null,
                    $smartContext['month_label'] ?? null,
                    $smartContext['airline'] ?? null,
                    $smartContext['room_type'] ?? null,
                    $smartContext['participants'] ?? null,
                ]))) : ?>
                    <div class="admin-empty"><i class="bi bi-diagram-3"></i> Belum ada konteks percakapan aktif.</div>
                <?php else : ?>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <?php if (!empty($smartContext['topic'])) : ?>
                            <span class="package-program-pill">Topik: <?= esc(ucwords(str_replace('_', ' ', $smartContext['topic']))); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($smartContext['focus'])) : ?>
                            <span class="package-program-pill">Fokus: <?= esc($contextLabels[$smartContext['focus']] ?? ucwords($smartContext['focus'])); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($smartContext['program'])) : ?>
                            <span class="package-program-pill">Program <?= esc(ucwords($smartContext['program'])); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($smartContext['package_name'])) : ?>
                            <span class="package-program-pill">Paket <?= esc($smartContext['package_name']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($smartContext['month_label'])) : ?>
                            <span class="package-program-pill"><?= esc($smartContext['month_label']); ?> <?= esc($smartContext['year'] ?? ''); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($smartContext['airline'])) : ?>
                            <span class="package-program-pill"><?= esc($smartContext['airline']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($smartContext['room_type'])) : ?>
                            <span class="package-program-pill">Kamar <?= esc(ucwords($smartContext['room_type'])); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($smartContext['participants'])) : ?>
                            <span class="package-program-pill"><?= esc($smartContext['participants']); ?> Jamaah</span>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted">Konteks otomatis kedaluwarsa setelah 45 menit tanpa percakapan.</small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

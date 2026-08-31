<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>
<div class="admin-page-title d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
    <div>
        <span>Detail Prospek</span>
        <h1><?= esc($lead['lead_no'] ?? 'Lead'); ?></h1>
        <p>Kelola status, kualitas prospek, admin PIC, dan jadwal follow-up calon jamaah.</p>
    </div>
    <a href="<?= base_url('/admin/leads'); ?>" class="btn btn-admin-outline"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<?php if (session()->getFlashdata('success')) : ?><div class="alert alert-success rounded-4"><?= esc(session()->getFlashdata('success')); ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?><div class="alert alert-danger rounded-4"><?= esc(session()->getFlashdata('error')); ?></div><?php endif; ?>

<?php
$status      = $lead['status'] ?? 'new';
$temperature = $lead['lead_temperature'] ?? 'warm';
$waPhone     = preg_replace('/[^0-9]/', '', (string) ($lead['phone'] ?? ''));
if (str_starts_with($waPhone, '0')) {
    $waPhone = '62' . substr($waPhone, 1);
}
?>

<div class="lead-detail-hero mb-4">
    <div class="lead-detail-person">
        <div class="lead-avatar"><?= esc(strtoupper(substr((string) ($lead['full_name'] ?? 'L'), 0, 1))); ?></div>
        <div>
            <span>Calon Jamaah</span>
            <h2><?= esc($lead['full_name'] ?? '-'); ?></h2>
            <div class="d-flex flex-wrap gap-2 mt-2">
                <span class="lead-temperature lead-temperature-<?= esc($temperature); ?>"><i class="bi bi-fire"></i> <?= esc($temperatureLabels[$temperature] ?? ucfirst($temperature)); ?></span>
                <span class="lead-status lead-status-<?= esc($status); ?>"><?= esc($statusLabels[$status] ?? $status); ?></span>
            </div>
        </div>
    </div>

    <div class="lead-detail-actions">
        <a href="https://wa.me/<?= esc($waPhone); ?>?text=<?= rawurlencode('Assalamualaikum ' . ($lead['full_name'] ?? '') . ', kami dari ' . site_setting('site_name', 'Travel Umroh & Haji') . ' ingin menindaklanjuti minat Anda pada paket ' . ($lead['package_name'] ?? '') . '.'); ?>" target="_blank" class="btn btn-admin-primary"><i class="bi bi-whatsapp"></i> Hubungi WhatsApp</a>
        <?php if (!empty($lead['package_slug'])) : ?><a href="<?= base_url('/paket/' . $lead['package_slug']); ?>" target="_blank" class="btn btn-admin-outline"><i class="bi bi-box-arrow-up-right"></i> Lihat Paket</a><?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="admin-card mb-4">
            <div class="admin-card-header"><div><span class="lead-card-kicker">Informasi Lead</span><h2>Data Calon Jamaah</h2></div></div>
            <div class="admin-card-body">
                <div class="lead-info-grid">
                    <div><span>Nama Lengkap</span><strong><?= esc($lead['full_name'] ?? '-'); ?></strong></div>
                    <div><span>WhatsApp</span><strong><?= esc($lead['phone'] ?? '-'); ?></strong></div>
                    <div><span>Email</span><strong><?= esc($lead['email'] ?? '-'); ?></strong></div>
                    <div><span>Domisili</span><strong><?= esc($lead['city'] ?? '-'); ?></strong></div>
                    <div><span>Jumlah Jamaah</span><strong><?= esc($lead['total_participants'] ?? 1); ?> orang</strong></div>
                    <div><span>Sumber</span><strong><?= esc($lead['source'] ?? 'website'); ?></strong></div>
                    <div><span>Paket</span><strong><?= esc($lead['package_name'] ?? '-'); ?></strong></div>
                    <div><span>Harga Paket</span><strong><?= (float) ($lead['package_price'] ?? 0) > 0 ? 'Rp ' . number_format((float) $lead['package_price'], 0, ',', '.') : '-'; ?></strong></div>
                    <div><span>Keberangkatan</span><strong><?= !empty($lead['departure_date']) ? date('d M Y', strtotime($lead['departure_date'])) : 'Belum memilih'; ?></strong></div>
                    <div><span>Tanggal Masuk</span><strong><?= !empty($lead['created_at']) ? date('d M Y H:i', strtotime($lead['created_at'])) . ' WIB' : '-'; ?></strong></div>
                </div>

                <?php if (!empty($lead['notes'])) : ?>
                    <div class="lead-note-box mt-4"><span>Catatan dari calon jamaah</span><p><?= nl2br(esc($lead['notes'])); ?></p></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header"><div><span class="lead-card-kicker">Audit Follow-up</span><h2>Riwayat Aktivitas</h2></div></div>
            <div class="admin-card-body">
                <?php if (empty($activities)) : ?>
                    <div class="admin-empty"><i class="bi bi-clock-history"></i> Belum ada aktivitas.</div>
                <?php else : ?>
                    <div class="lead-timeline">
                        <?php foreach ($activities as $activity) : ?>
                            <div class="lead-timeline-item">
                                <div class="lead-timeline-dot"></div>
                                <div class="lead-timeline-content">
                                    <div class="d-flex flex-wrap justify-content-between gap-2">
                                        <strong><?= esc(ucwords(str_replace('_', ' ', $activity['activity_type'] ?? 'aktivitas'))); ?></strong>
                                        <small><?= !empty($activity['created_at']) ? date('d M Y H:i', strtotime($activity['created_at'])) . ' WIB' : '-'; ?></small>
                                    </div>
                                    <?php if (!empty($activity['description'])) : ?><p><?= nl2br(esc($activity['description'])); ?></p><?php endif; ?>
                                    <span><?= esc($activity['admin_name'] ?? 'Sistem Website'); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="admin-card lead-pipeline-card position-sticky" style="top: 112px;">
            <div class="admin-card-header"><div><span class="lead-card-kicker">Kelola Pipeline</span><h2>Update Follow-up</h2></div></div>
            <div class="admin-card-body">
                <form action="<?= base_url('/admin/leads/update/' . $lead['id']); ?>" method="post">
                    <?= csrf_field(); ?>

                    <div class="mb-3">
                        <label class="form-label">Status Lead</label>
                        <select name="status" class="form-select" required>
                            <?php foreach ($statusLabels as $key => $label) : ?><option value="<?= esc($key); ?>" <?= $status === $key ? 'selected' : ''; ?>><?= esc($label); ?></option><?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tingkat Keseriusan</label>
                        <div class="lead-temperature-picker">
                            <?php foreach ($temperatureLabels as $key => $label) : ?>
                                <label class="lead-temp-option lead-temp-option-<?= esc($key); ?>">
                                    <input type="radio" name="lead_temperature" value="<?= esc($key); ?>" <?= $temperature === $key ? 'checked' : ''; ?>>
                                    <span><i class="bi bi-fire"></i><?= esc($label); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Admin PIC</label>
                        <select name="assigned_admin_id" class="form-select">
                            <option value="">Belum ditugaskan</option>
                            <?php foreach ($admins as $admin) : ?><option value="<?= esc($admin['id']); ?>" <?= (string) ($lead['assigned_admin_id'] ?? '') === (string) $admin['id'] ? 'selected' : ''; ?>><?= esc($admin['name']); ?></option><?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Follow-up Berikutnya <span class="lead-form-timezone">WIB · GMT+7</span></label>
                        <input type="datetime-local" name="next_follow_up_at" class="form-control" value="<?= !empty($lead['next_follow_up_at']) ? date('Y-m-d\TH:i', strtotime($lead['next_follow_up_at'])) : ''; ?>">
                        <small class="lead-form-help"><i class="bi bi-clock"></i> Jadwal disimpan dalam zona waktu Asia/Jakarta.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Follow-up</label>
                        <textarea name="follow_up_note" class="form-control" rows="4" placeholder="Contoh: calon jamaah masih berdiskusi dengan keluarga."></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Alasan Tidak Lanjut</label>
                        <input type="text" name="lost_reason" class="form-control" value="<?= esc($lead['lost_reason'] ?? ''); ?>" placeholder="Diisi jika tidak berminat/dibatalkan">
                    </div>

                    <button type="submit" class="btn btn-admin-primary w-100"><i class="bi bi-check2-circle"></i> Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

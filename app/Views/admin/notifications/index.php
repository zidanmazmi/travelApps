<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>

<div class="admin-page-title">
    <span>Sistem</span>
    <h1>Notification Center</h1>
    <p>Pilih beberapa notifikasi untuk ditandai dibaca atau dihapus sekaligus.</p>
</div>

<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')); ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')); ?></div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header flex-wrap gap-2">
        <div>
            <h2>Daftar Notifikasi</h2>
            <small class="text-muted">Maksimal 100 notifikasi terbaru ditampilkan.</small>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <form action="<?= base_url('/admin/notifications/read-all'); ?>" method="post">
                <?= csrf_field(); ?>
                <button type="submit" class="btn btn-admin-outline btn-sm">
                    <i class="bi bi-check2-all"></i> Tandai Semua Dibaca
                </button>
            </form>

            <?php if (session()->get('admin_role') === 'super_admin' && !empty($notifications)) : ?>
                <form
                    action="<?= base_url('/admin/notifications/delete-all'); ?>"
                    method="post"
                    data-confirm="Hapus semua notifikasi secara permanen?">
                    <?= csrf_field(); ?>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash3"></i> Hapus Semua
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($notifications)) : ?>
        <div class="admin-empty py-5">
            <i class="bi bi-bell"></i>
            Belum ada notifikasi.
        </div>
    <?php else : ?>
        <form
            action="<?= base_url('/admin/notifications/bulk-action'); ?>"
            method="post"
            data-bulk-scope
            data-bulk-form>
            <?= csrf_field(); ?>

            <div class="bulk-selection-row px-3 pt-3">
                <label class="bulk-check-label">
                    <input type="checkbox" class="form-check-input" data-check-all>
                    <span>Pilih semua notifikasi</span>
                </label>
            </div>

            <div class="bulk-action-bar mx-3" data-bulk-toolbar hidden>
                <div>
                    <strong><span data-bulk-count>0</span> dipilih</strong>
                    <small>Tentukan tindakan untuk notifikasi yang dipilih.</small>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button
                        type="submit"
                        name="bulk_action"
                        value="read"
                        class="btn btn-admin-outline btn-sm"
                        data-confirm="Tandai {count} notifikasi sebagai sudah dibaca?">
                        <i class="bi bi-check2-all"></i> Tandai Dibaca
                    </button>
                    <button
                        type="submit"
                        name="bulk_action"
                        value="delete"
                        class="btn btn-danger btn-sm"
                        data-confirm="Hapus {count} notifikasi yang dipilih secara permanen?">
                        <i class="bi bi-trash3"></i> Hapus Terpilih
                    </button>
                </div>
            </div>

            <div class="notification-list-page">
                <?php foreach ($notifications as $notification) : ?>
                    <?php
                    $type = $notification['type'] ?? 'system';
                    $icons = [
                        'registration' => 'bi-person-plus',
                        'payment'      => 'bi-credit-card',
                        'document'     => 'bi-file-earmark-text',
                        'package'      => 'bi-box-seam',
                        'gallery'      => 'bi-images',
                        'testimonial'  => 'bi-chat-quote',
                        'faq'          => 'bi-question-circle',
                        'lead'         => 'bi-person-lines-fill',
                        'system'       => 'bi-bell',
                    ];
                    $icon = $icons[$type] ?? 'bi-bell';
                    $isRead = (int) ($notification['is_read'] ?? 0) === 1;
                    ?>

                    <div class="notification-page-item <?= $isRead ? 'is-read' : 'is-unread'; ?>">
                        <div class="bulk-item-check">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                name="selected_ids[]"
                                value="<?= (int) $notification['id']; ?>"
                                data-bulk-item
                                aria-label="Pilih notifikasi <?= esc($notification['title'] ?? ''); ?>">
                        </div>

                        <div class="notification-page-icon">
                            <i class="bi <?= esc($icon); ?>"></i>
                        </div>

                        <div class="notification-page-content">
                            <div class="notification-page-title-row">
                                <h3><?= esc($notification['title'] ?? '-'); ?></h3>
                                <span class="status-pill <?= $isRead ? 'status-muted' : 'status-warning'; ?>">
                                    <?= $isRead ? 'Dibaca' : 'Baru'; ?>
                                </span>
                            </div>

                            <p><?= esc($notification['message'] ?? '-'); ?></p>
                            <small>
                                <?= !empty($notification['created_at'])
                                    ? date('d M Y H:i', strtotime($notification['created_at']))
                                    : '-'; ?>
                            </small>
                        </div>

                        <div class="notification-page-action">
                            <a
                                href="<?= base_url('/admin/notifications/read/' . $notification['id']); ?>"
                                class="btn btn-admin-primary btn-sm">
                                Buka
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </form>
    <?php endif; ?>
</div>

<?= $this->endSection(); ?>

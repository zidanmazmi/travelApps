<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>

<div class="admin-page-title d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
    <div>
        <span>Paket &amp; Jadwal</span>
        <h1>Jadwal Keberangkatan</h1>
        <p>Paket: <strong><?= esc($package['name']); ?></strong>. Pilih beberapa jadwal untuk dihapus sekaligus.</p>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('/admin/packages'); ?>" class="btn btn-admin-outline">
            <i class="bi bi-arrow-left"></i> Kembali ke Paket
        </a>
        <a href="<?= base_url('/admin/packages/departures/create/' . $package['id']); ?>" class="btn btn-admin-primary">
            <i class="bi bi-plus-lg"></i> Tambah Jadwal
        </a>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success rounded-4"><?= esc(session()->getFlashdata('success')); ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger rounded-4"><?= esc(session()->getFlashdata('error')); ?></div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <span class="lead-card-kicker">Keberangkatan</span>
            <h2>Daftar Jadwal</h2>
        </div>
        <span class="status-pill status-muted"><?= count($departures ?? []); ?> jadwal</span>
    </div>

    <form
        action="<?= base_url('/admin/packages/departures/bulk-delete'); ?>"
        method="post"
        data-bulk-scope
        data-bulk-form>
        <?= csrf_field(); ?>
        <input type="hidden" name="package_id" value="<?= (int) $package['id']; ?>">

        <div class="bulk-action-bar" data-bulk-toolbar hidden>
            <div class="bulk-action-copy">
                <i class="bi bi-check2-square"></i>
                <span><strong data-bulk-count>0</strong> jadwal dipilih</span>
            </div>
            <button
                type="submit"
                class="btn btn-danger btn-sm"
                data-confirm="Hapus {count} jadwal yang dipilih? Jadwal yang sudah memiliki lead atau histori pendaftaran akan dilewati.">
                <i class="bi bi-trash3"></i> Hapus Terpilih
            </button>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="admin-select-cell">
                            <input type="checkbox" class="admin-bulk-check" data-check-all aria-label="Pilih semua jadwal">
                        </th>
                        <th>Tanggal Berangkat</th>
                        <th>Tanggal Pulang</th>
                        <th>Kuota</th>
                        <th>Terisi</th>
                        <th>Sisa</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($departures)) : ?>
                        <tr>
                            <td colspan="8">
                                <div class="admin-empty">
                                    <i class="bi bi-calendar-x"></i>
                                    Belum ada jadwal keberangkatan.
                                </div>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($departures as $departure) : ?>
                            <?php
                            $quota = (int) ($departure['quota'] ?? 0);
                            $booked = (int) ($departure['booked'] ?? 0);
                            $remaining = max($quota - $booked, 0);
                            $status = $departure['status'] ?? 'closed';
                            ?>

                            <tr>
                                <td class="admin-select-cell">
                                    <input
                                        type="checkbox"
                                        name="selected_ids[]"
                                        value="<?= (int) $departure['id']; ?>"
                                        class="admin-bulk-check"
                                        data-bulk-item
                                        aria-label="Pilih jadwal <?= esc($departure['departure_date'] ?? ''); ?>">
                                </td>
                                <td>
                                    <strong><?= !empty($departure['departure_date']) ? date('d M Y', strtotime($departure['departure_date'])) : '-'; ?></strong>
                                </td>
                                <td><?= !empty($departure['return_date']) ? date('d M Y', strtotime($departure['return_date'])) : '-'; ?></td>
                                <td><?= $quota; ?></td>
                                <td><?= $booked; ?></td>
                                <td><strong><?= $remaining; ?></strong></td>
                                <td>
                                    <?php if ($status === 'available') : ?>
                                        <span class="status-pill status-success">Available</span>
                                    <?php elseif ($status === 'full') : ?>
                                        <span class="status-pill status-warning">Full</span>
                                    <?php else : ?>
                                        <span class="status-pill status-danger">Closed</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a
                                        href="<?= base_url('/admin/packages/departures/edit/' . $departure['id']); ?>"
                                        class="btn btn-admin-primary btn-sm">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>

<div class="admin-info-note mt-3">
    <i class="bi bi-shield-check"></i>
    <span>Jadwal yang sudah terhubung dengan calon jamaah atau histori pendaftaran tidak akan dihapus. Ubah statusnya menjadi <strong>closed</strong> agar tidak tampil sebagai jadwal tersedia.</span>
</div>

<?= $this->endSection(); ?>

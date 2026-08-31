<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>
<div class="admin-page-title d-flex flex-column flex-xl-row align-items-xl-end justify-content-between gap-3">
    <div>
        <span>Sales Overview</span>
        <h1>Dashboard <?= esc(site_setting('site_name', 'Travel Umroh & Haji')); ?></h1>
        <p>Fokus pada lead website, tindak lanjut WhatsApp, paket yang diminati, dan peluang konversi jamaah.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('/admin/leads'); ?>" class="btn btn-admin-primary"><i class="bi bi-person-lines-fill"></i> Kelola Leads</a>
        <a href="<?= base_url('/admin/packages/create'); ?>" class="btn btn-admin-outline"><i class="bi bi-plus-circle"></i> Tambah Paket</a>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<?php if (session()->getFlashdata('success')) : ?><div class="alert alert-success rounded-4"><?= esc(session()->getFlashdata('success')); ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?><div class="alert alert-danger rounded-4"><?= esc(session()->getFlashdata('error')); ?></div><?php endif; ?>

<section class="lead-dashboard-banner mb-4">
    <div class="lead-dashboard-copy">
        <span>Operasional hari ini</span>
        <h2><?= esc($leadStats['follow_up_today'] ?? 0); ?> follow-up terjadwal</h2>
        <p>
            Ada <?= esc($leadStats['new'] ?? 0); ?> lead baru,
            <?= esc($leadStats['hot'] ?? 0); ?> hot lead, dan
            <?= esc($leadStats['overdue'] ?? 0); ?> tindak lanjut yang melewati jadwal.
        </p>
        <div class="d-flex flex-wrap gap-2 mt-4">
            <a href="<?= base_url('/admin/leads?status=new'); ?>" class="btn btn-light rounded-pill fw-bold"><i class="bi bi-stars"></i> Lead Baru</a>
            <a href="<?= base_url('/admin/leads?temperature=hot'); ?>" class="btn btn-outline-light rounded-pill fw-bold"><i class="bi bi-fire"></i> Hot Lead</a>
        </div>
    </div>
    <div class="lead-dashboard-orbit"><i class="bi bi-whatsapp"></i></div>
</section>

<div class="lead-stat-grid mb-4">
    <div class="lead-stat-card lead-stat-primary"><div class="lead-stat-icon"><i class="bi bi-people"></i></div><div><span>Total Lead</span><strong><?= esc($leadStats['total'] ?? 0); ?></strong><small>Prospek aktif</small></div></div>
    <div class="lead-stat-card"><div class="lead-stat-icon"><i class="bi bi-stars"></i></div><div><span>Lead Baru</span><strong><?= esc($leadStats['new'] ?? 0); ?></strong><small>Perlu dihubungi</small></div></div>
    <div class="lead-stat-card"><div class="lead-stat-icon"><i class="bi bi-arrow-repeat"></i></div><div><span>Dalam Follow-up</span><strong><?= esc($leadStats['follow_up'] ?? 0); ?></strong><small>Pipeline aktif</small></div></div>
    <div class="lead-stat-card lead-stat-hot"><div class="lead-stat-icon"><i class="bi bi-fire"></i></div><div><span>Hot Lead</span><strong><?= esc($leadStats['hot'] ?? 0); ?></strong><small>Prioritas sales</small></div></div>
    <div class="lead-stat-card"><div class="lead-stat-icon"><i class="bi bi-calendar2-check"></i></div><div><span>Bulan Ini</span><strong><?= esc($leadStats['this_month'] ?? 0); ?></strong><small>Lead masuk</small></div></div>
    <div class="lead-stat-card lead-stat-success"><div class="lead-stat-icon"><i class="bi bi-graph-up-arrow"></i></div><div><span>Conversion Rate</span><strong><?= esc($leadStats['conversion_rate'] ?? 0); ?>%</strong><small><?= esc($leadStats['converted'] ?? 0); ?> berhasil daftar</small></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="admin-card h-100 lead-chart-card">
            <div class="admin-card-header">
                <div><span class="lead-card-kicker">Pertumbuhan Prospek</span><h2>Lead 6 Bulan Terakhir</h2></div>
                <span class="lead-live-badge"><span></span> Data realtime</span>
            </div>
            <div class="admin-card-body"><canvas id="dashboardLeadChart" height="115"></canvas></div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="admin-card h-100 lead-chart-card">
            <div class="admin-card-header"><div><span class="lead-card-kicker">Minat Jamaah</span><h2>Paket Terpopuler</h2></div></div>
            <div class="admin-card-body"><canvas id="dashboardPackageChart" height="210"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <div><span class="lead-card-kicker">Prospek Terbaru</span><h2>Lead Masuk dari Website</h2></div>
                <a href="<?= base_url('/admin/leads'); ?>" class="btn btn-admin-outline btn-sm">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead><tr><th>Lead</th><th>Calon Jamaah</th><th>Paket</th><th>Potensi</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php
                    $statusLabels = [
                        'new' => 'Baru', 'contacted' => 'Sudah Dihubungi', 'follow_up' => 'Follow-up',
                        'qualified' => 'Prospek Serius', 'waiting_decision' => 'Menunggu Keputusan',
                        'converted' => 'Berhasil Daftar', 'not_interested' => 'Tidak Berminat',
                        'unreachable' => 'Tidak Bisa Dihubungi', 'cancelled' => 'Dibatalkan'
                    ];
                    ?>
                    <?php if (empty($latestLeads)) : ?>
                        <tr><td colspan="6"><div class="admin-empty"><i class="bi bi-person-lines-fill"></i> Belum ada lead dari website.</div></td></tr>
                    <?php else : ?>
                        <?php foreach ($latestLeads as $lead) : ?>
                            <tr>
                                <td><strong><?= esc($lead['lead_no'] ?? '-'); ?></strong><br><small><?= !empty($lead['created_at']) ? date('d M Y H:i', strtotime($lead['created_at'])) : '-'; ?></small></td>
                                <td><strong><?= esc($lead['full_name'] ?? '-'); ?></strong><br><small><?= esc($lead['phone'] ?? '-'); ?> · <?= esc($lead['city'] ?? '-'); ?></small></td>
                                <td><?= esc($lead['package_name'] ?? '-'); ?></td>
                                <td><span class="lead-temperature lead-temperature-<?= esc($lead['lead_temperature'] ?? 'warm'); ?>"><i class="bi bi-fire"></i> <?= esc(strtoupper($lead['lead_temperature'] ?? 'warm')); ?></span></td>
                                <td><span class="lead-status lead-status-<?= esc($lead['status'] ?? 'new'); ?>"><?= esc($statusLabels[$lead['status'] ?? 'new'] ?? $lead['status']); ?></span></td>
                                <td><a href="<?= base_url('/admin/leads/detail/' . $lead['id']); ?>" class="btn btn-admin-primary btn-sm">Follow-up</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="admin-card h-100">
            <div class="admin-card-header"><div><span class="lead-card-kicker">Agenda Sales</span><h2>Follow-up Berikutnya</h2></div></div>
            <div class="admin-card-body">
                <?php if (empty($upcomingFollowUps)) : ?>
                    <div class="admin-empty"><i class="bi bi-calendar2-check"></i> Belum ada jadwal follow-up.</div>
                <?php else : ?>
                    <div class="dashboard-followup-list">
                        <?php foreach ($upcomingFollowUps as $lead) : ?>
                            <?php $isOverdue = !empty($lead['next_follow_up_at']) && strtotime($lead['next_follow_up_at']) < time(); ?>
                            <a href="<?= base_url('/admin/leads/detail/' . $lead['id']); ?>" class="dashboard-followup-item <?= $isOverdue ? 'is-overdue' : ''; ?>">
                                <div class="dashboard-followup-date">
                                    <strong><?= date('d', strtotime($lead['next_follow_up_at'])); ?></strong>
                                    <span><?= date('M', strtotime($lead['next_follow_up_at'])); ?></span>
                                </div>
                                <div>
                                    <strong><?= esc($lead['full_name'] ?? '-'); ?></strong>
                                    <small><?= date('H:i', strtotime($lead['next_follow_up_at'])); ?> · <?= esc($lead['package_name'] ?? '-'); ?></small>
                                    <span><?= $isOverdue ? 'Terlambat' : esc($lead['assigned_admin_name'] ?? 'Belum ada PIC'); ?></span>
                                </div>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6 col-xl-3"><a href="<?= base_url('/admin/packages'); ?>" class="dashboard-quick-card"><i class="bi bi-box-seam"></i><div><span>Paket Aktif</span><strong><?= esc($packageStats['active'] ?? 0); ?></strong><small>Kelola katalog paket</small></div></a></div>
    <div class="col-md-6 col-xl-3"><a href="<?= base_url('/admin/packages'); ?>" class="dashboard-quick-card"><i class="bi bi-calendar-event"></i><div><span>Jadwal Tersedia</span><strong><?= esc($packageStats['departures'] ?? 0); ?></strong><small>Keberangkatan mendatang</small></div></a></div>
    <div class="col-md-6 col-xl-3"><a href="<?= base_url('/admin/leads?assigned_admin_id='); ?>" class="dashboard-quick-card"><i class="bi bi-person-exclamation"></i><div><span>Belum Ada PIC</span><strong><?= esc($leadStats['unassigned'] ?? 0); ?></strong><small>Segera tugaskan admin</small></div></a></div>
    <div class="col-md-6 col-xl-3"><a href="<?= base_url('/admin/packages/trash'); ?>" class="dashboard-quick-card"><i class="bi bi-trash3"></i><div><span>Sampah Paket</span><strong><?= esc($packageStats['trash'] ?? 0); ?></strong><small>Kelola data terhapus</small></div></a></div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('script'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
const dashboardMonthlyLabels = <?= json_encode(array_column($monthlyRows, 'month_label')); ?>;
const dashboardMonthlyValues = <?= json_encode(array_map('intval', array_column($monthlyRows, 'total'))); ?>;
const dashboardPackageLabels = <?= json_encode(array_column($packageRows, 'package_name')); ?>;
const dashboardPackageValues = <?= json_encode(array_map('intval', array_column($packageRows, 'total'))); ?>;

const leadCanvas = document.getElementById('dashboardLeadChart');
if (leadCanvas) {
    const ctx = leadCanvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(201,154,74,.72)');
    gradient.addColorStop(1, 'rgba(201,154,74,.03)');
    new Chart(leadCanvas, {
        type: 'line',
        data: { labels: dashboardMonthlyLabels, datasets: [{ data: dashboardMonthlyValues, fill: true, borderColor: '#b77a34', backgroundColor: gradient, borderWidth: 3, tension: .42, pointRadius: 4, pointBackgroundColor: '#090909' }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(143,97,41,.08)' } }, x: { grid: { display: false } } } }
    });
}

const packageCanvas = document.getElementById('dashboardPackageChart');
if (packageCanvas) {
    new Chart(packageCanvas, {
        type: 'doughnut',
        data: { labels: dashboardPackageLabels, datasets: [{ data: dashboardPackageValues, backgroundColor: ['#090909', '#b77a34', '#efd087', '#8f6129', '#d9c7a6', '#5b4632'], borderWidth: 0 }] },
        options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } } } }
    });
}
</script>
<?= $this->endSection(); ?>

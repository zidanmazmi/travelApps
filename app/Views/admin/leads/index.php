<?= $this->extend('templates/admin/layouts/main'); ?>

<?php
$currentPage = $pager ? max(1, (int) $pager->getCurrentPage('leads')) : 1;
$perPage     = (int) ($filters['per_page'] ?? 20);
$rangeStart  = $totalLeads > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
$rangeEnd    = min($currentPage * $perPage, $totalLeads);
$hasFilters  = !empty($activeFilters);
?>

<?= $this->section('content_header'); ?>
<div class="admin-page-title lead-report-page-title">
    <div class="lead-report-heading">
        <span>Sales Pipeline</span>
        <h1>Reporting Leads</h1>
        <p>Pantau calon jamaah, prioritas follow-up, minat paket, dan hasil konversi dalam satu tampilan.</p>
        <div class="lead-report-heading-meta">
            <span><i class="bi bi-broadcast-pin"></i> Data prospek aktif</span>
            <span class="lead-timezone-badge"><i class="bi bi-clock"></i> Asia/Jakarta · GMT+7 (WIB)</span>
        </div>
    </div>

    <div class="admin-page-actions lead-report-actions">
        <a href="<?= base_url('/admin/leads/trash'); ?>" class="btn lead-trash-button">
            <i class="bi bi-trash3"></i>
            <span>Sampah</span>
        </a>

        <a href="<?= esc($exportUrl); ?>" class="btn lead-export-button">
            <i class="bi bi-file-earmark-spreadsheet"></i>
            <span>Export CSV<?= $hasFilters ? ' Terfilter' : ''; ?></span>
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

<div class="lead-report-summary-bar mb-4">
    <div class="lead-report-summary-main">
        <span class="lead-report-summary-icon"><i class="bi bi-funnel"></i></span>
        <div>
            <strong><?= number_format($totalLeads, 0, ',', '.'); ?> lead ditampilkan</strong>
            <small>
                <?= $hasFilters
                    ? 'Hasil telah disesuaikan dengan filter aktif.'
                    : 'Dari ' . number_format($globalTotalLeads, 0, ',', '.') . ' lead aktif pada database.'; ?>
            </small>
        </div>
    </div>

    <div class="lead-report-attention-list">
        <a href="<?= base_url('/admin/leads?status=new'); ?>" class="lead-attention-item">
            <i class="bi bi-stars"></i>
            <span><strong><?= esc($newLeads); ?></strong> lead baru</span>
        </a>
        <a href="<?= base_url('/admin/leads?attention=overdue'); ?>" class="lead-attention-item <?= $overdueFollowUps > 0 ? 'is-danger' : ''; ?>">
            <i class="bi bi-alarm"></i>
            <span><strong><?= esc($overdueFollowUps); ?></strong> follow-up terlambat</span>
        </a>
        <a href="<?= base_url('/admin/leads?attention=unassigned'); ?>" class="lead-attention-item <?= $unassignedLeads > 0 ? 'is-warning' : ''; ?>">
            <i class="bi bi-person-exclamation"></i>
            <span><strong><?= esc($unassignedLeads); ?></strong> belum ada PIC</span>
        </a>
    </div>
</div>

<div class="lead-stat-grid mb-4">
    <a href="<?= base_url('/admin/leads'); ?>" class="lead-stat-card lead-stat-primary" aria-label="Tampilkan seluruh lead">
        <div class="lead-stat-icon"><i class="bi bi-people"></i></div>
        <div>
            <span>Total Lead</span>
            <strong><?= number_format($totalLeads, 0, ',', '.'); ?></strong>
            <small><?= $hasFilters ? 'Sesuai filter aktif' : 'Seluruh prospek aktif'; ?></small>
        </div>
        <i class="bi bi-arrow-up-right lead-stat-link-icon" aria-hidden="true"></i>
    </a>

    <a href="<?= base_url('/admin/leads?status=new'); ?>" class="lead-stat-card" aria-label="Tampilkan lead baru">
        <div class="lead-stat-icon"><i class="bi bi-stars"></i></div>
        <div>
            <span>Lead Baru</span>
            <strong><?= number_format($newLeads, 0, ',', '.'); ?></strong>
            <small>Perlu segera dihubungi</small>
        </div>
        <i class="bi bi-arrow-up-right lead-stat-link-icon" aria-hidden="true"></i>
    </a>

    <a href="<?= base_url('/admin/leads?attention=pipeline'); ?>" class="lead-stat-card" aria-label="Tampilkan lead dalam follow-up">
        <div class="lead-stat-icon"><i class="bi bi-arrow-repeat"></i></div>
        <div>
            <span>Dalam Follow-up</span>
            <strong><?= number_format($followUpLeads, 0, ',', '.'); ?></strong>
            <small>Pipeline sedang berjalan</small>
        </div>
        <i class="bi bi-arrow-up-right lead-stat-link-icon" aria-hidden="true"></i>
    </a>

    <a href="<?= base_url('/admin/leads?temperature=hot'); ?>" class="lead-stat-card lead-stat-hot" aria-label="Tampilkan hot lead">
        <div class="lead-stat-icon"><i class="bi bi-fire"></i></div>
        <div>
            <span>Hot Lead</span>
            <strong><?= number_format($hotLeads, 0, ',', '.'); ?></strong>
            <small>Prospek prioritas tinggi</small>
        </div>
        <i class="bi bi-arrow-up-right lead-stat-link-icon" aria-hidden="true"></i>
    </a>

    <a href="<?= base_url('/admin/leads?attention=today'); ?>" class="lead-stat-card" aria-label="Tampilkan follow-up hari ini">
        <div class="lead-stat-icon"><i class="bi bi-calendar2-check"></i></div>
        <div>
            <span>Follow-up Hari Ini</span>
            <strong><?= number_format($followUpToday, 0, ',', '.'); ?></strong>
            <small>Agenda follow-up hari ini</small>
        </div>
        <i class="bi bi-arrow-up-right lead-stat-link-icon" aria-hidden="true"></i>
    </a>

    <a href="<?= base_url('/admin/leads?status=converted'); ?>" class="lead-stat-card lead-stat-success" aria-label="Tampilkan lead yang berhasil daftar">
        <div class="lead-stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
        <div>
            <span>Conversion Rate</span>
            <strong><?= esc($conversionRate); ?>%</strong>
            <small><?= esc($convertedLeads); ?> berhasil daftar</small>
        </div>
        <i class="bi bi-arrow-up-right lead-stat-link-icon" aria-hidden="true"></i>
    </a>
</div>

<div class="admin-card mb-4 lead-filter-card">
    <div class="admin-card-header lead-filter-header">
        <div>
            <span class="lead-card-kicker">Pencarian &amp; Segmentasi</span>
            <h2>Filter Leads</h2>
        </div>

        <div class="lead-filter-header-actions">
            <button
                type="button"
                class="btn btn-admin-outline btn-sm lead-filter-toggle"
                data-bs-toggle="collapse"
                data-bs-target="#leadFilterPanel"
                aria-expanded="<?= $hasFilters ? 'true' : 'false'; ?>"
                aria-controls="leadFilterPanel">
                <i class="bi bi-sliders"></i>
                Atur Filter
            </button>

            <?php if ($hasFilters) : ?>
                <a href="<?= base_url('/admin/leads'); ?>" class="btn btn-admin-outline btn-sm">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Reset
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($hasFilters) : ?>
        <div class="lead-active-filters">
            <span class="lead-active-filter-label">Filter aktif:</span>
            <?php foreach ($activeFilters as $activeFilter) : ?>
                <span class="lead-filter-chip"><i class="bi bi-check2"></i><?= esc($activeFilter); ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="collapse <?= $hasFilters ? 'show' : ''; ?>" id="leadFilterPanel">
        <div class="admin-card-body">
            <form method="get" action="<?= base_url('/admin/leads'); ?>" class="lead-filter-form">
                <div class="row g-3">
                    <div class="col-xl-4 col-lg-4 col-md-6">
                        <label class="form-label">Cari lead</label>
                        <div class="lead-search-input">
                            <i class="bi bi-search"></i>
                            <input
                                type="search"
                                name="q"
                                class="form-control"
                                value="<?= esc($filters['q'] ?? ''); ?>"
                                placeholder="Nama, nomor lead, WhatsApp, kota, paket...">
                        </div>
                    </div>

                    <div class="col-xl-2 col-lg-2 col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua status</option>
                            <?php foreach ($statusLabels as $key => $label) : ?>
                                <option value="<?= esc($key); ?>" <?= ($filters['status'] ?? '') === $key ? 'selected' : ''; ?>><?= esc($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-xl-2 col-lg-2 col-md-6">
                        <label class="form-label">Potensi</label>
                        <select name="temperature" class="form-select">
                            <option value="">Semua potensi</option>
                            <?php foreach ($temperatureLabels as $key => $label) : ?>
                                <option value="<?= esc($key); ?>" <?= ($filters['temperature'] ?? '') === $key ? 'selected' : ''; ?>><?= esc($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-xl-4 col-lg-4 col-md-6">
                        <label class="form-label">Paket</label>
                        <select name="package_id" class="form-select">
                            <option value="">Semua paket</option>
                            <?php foreach ($packages as $package) : ?>
                                <option value="<?= esc($package['id']); ?>" <?= (string) ($filters['package_id'] ?? '') === (string) $package['id'] ? 'selected' : ''; ?>><?= esc($package['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-xl-3 col-lg-3 col-md-6">
                        <label class="form-label">Admin PIC</label>
                        <select name="assigned_admin_id" class="form-select">
                            <option value="">Semua admin</option>
                            <?php foreach ($admins as $admin) : ?>
                                <option value="<?= esc($admin['id']); ?>" <?= (string) ($filters['assigned_admin_id'] ?? '') === (string) $admin['id'] ? 'selected' : ''; ?>><?= esc($admin['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-xl-2 col-lg-3 col-md-6">
                        <label class="form-label">Dari tanggal</label>
                        <input type="date" name="date_start" class="form-control" value="<?= esc($filters['date_start'] ?? ''); ?>">
                    </div>

                    <div class="col-xl-2 col-lg-3 col-md-6">
                        <label class="form-label">Sampai tanggal</label>
                        <input type="date" name="date_end" class="form-control" value="<?= esc($filters['date_end'] ?? ''); ?>">
                    </div>

                    <div class="col-xl-2 col-lg-3 col-md-6">
                        <label class="form-label">Data per halaman</label>
                        <select name="per_page" class="form-select">
                            <?php foreach ([10, 20, 50, 100] as $size) : ?>
                                <option value="<?= $size; ?>" <?= $perPage === $size ? 'selected' : ''; ?>><?= $size; ?> data</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-xl-3 col-lg-12 col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-admin-primary w-100">
                            <i class="bi bi-funnel"></i>
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row g-4 mb-4 lead-chart-row">
    <div class="col-xl-7 col-lg-7">
        <div class="admin-card h-100 lead-chart-card">
            <div class="admin-card-header">
                <div>
                    <span class="lead-card-kicker">Tren Prospek</span>
                    <h2>Lead 6 Bulan Terakhir</h2>
                </div>
                <small class="lead-chart-context"><?= $hasFilters ? 'Mengikuti filter aktif' : 'Semua lead aktif'; ?></small>
            </div>
            <div class="admin-card-body lead-chart-body">
                <canvas id="leadMonthlyChart" aria-label="Grafik lead enam bulan terakhir"></canvas>
            </div>
        </div>
    </div>

    <div class="col-xl-5 col-lg-5">
        <div class="admin-card h-100 lead-chart-card">
            <div class="admin-card-header">
                <div>
                    <span class="lead-card-kicker">Minat Paket</span>
                    <h2>Paket Paling Diminati</h2>
                </div>
            </div>
            <div class="admin-card-body lead-chart-body lead-chart-body-doughnut">
                <?php if (empty($packageRows)) : ?>
                    <div class="admin-empty"><i class="bi bi-pie-chart"></i> Belum ada data minat paket.</div>
                <?php else : ?>
                    <canvas id="leadPackageChart" aria-label="Grafik paket paling diminati"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<form action="<?= base_url('/admin/leads/bulk-delete'); ?>" method="post" data-bulk-scope data-bulk-form>
    <?= csrf_field(); ?>

    <div class="admin-card lead-list-card">
        <div class="admin-card-header lead-list-header">
            <div>
                <span class="lead-card-kicker">Pipeline Jamaah</span>
                <h2>Daftar Lead Aktif</h2>
                <small>Menampilkan <?= number_format($rangeStart, 0, ',', '.'); ?>–<?= number_format($rangeEnd, 0, ',', '.'); ?> dari <?= number_format($totalLeads, 0, ',', '.'); ?> data</small>
            </div>

            <span class="lead-live-badge"><span></span> Data website</span>
        </div>

        <div class="bulk-action-bar" data-bulk-toolbar hidden>
            <div class="bulk-action-copy">
                <i class="bi bi-check2-square"></i>
                <span><strong data-bulk-count>0</strong> lead dipilih</span>
            </div>
            <div class="bulk-action-controls">
                <button type="submit" class="btn btn-danger btn-sm rounded-pill" data-confirm="Pindahkan {count} lead terpilih ke Sampah?">
                    <i class="bi bi-trash3"></i>
                    Pindahkan ke Sampah
                </button>
            </div>
        </div>

        <div class="table-responsive lead-report-table-wrap">
            <table class="admin-table lead-table">
                <thead>
                    <tr>
                        <th class="admin-select-cell">
                            <input type="checkbox" class="admin-bulk-check" data-check-all aria-label="Pilih semua lead">
                        </th>
                        <th>Lead</th>
                        <th>Calon Jamaah</th>
                        <th>Paket &amp; Jadwal</th>
                        <th>Potensi</th>
                        <th>Status</th>
                        <th>Admin PIC</th>
                        <th>Follow-up</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leads)) : ?>
                        <tr class="lead-empty-row">
                            <td colspan="9">
                                <div class="admin-empty">
                                    <i class="bi bi-person-lines-fill"></i>
                                    Belum ada lead yang sesuai dengan filter.
                                </div>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($leads as $lead) : ?>
                            <?php
                            $temperature = $lead['lead_temperature'] ?? 'warm';
                            $status      = $lead['status'] ?? 'new';
                            $source      = strtolower((string) ($lead['source'] ?? 'website'));
                            $sourceClass = str_starts_with($source, 'website') ? 'website' : $source;
                            $sourceLabels = [
                                'chatbot'                => 'Chatbot',
                                'website'                => 'Website',
                                'website-package-detail' => 'Website',
                                'whatsapp'               => 'WhatsApp',
                                'manual'                 => 'Manual',
                            ];
                            $sourceIcons = [
                                'chatbot'                => 'bi-robot',
                                'website'                => 'bi-globe2',
                                'website-package-detail' => 'bi-globe2',
                                'whatsapp'               => 'bi-whatsapp',
                                'manual'                 => 'bi-pencil-square',
                            ];
                            $waPhone     = preg_replace('/[^0-9]/', '', (string) ($lead['phone'] ?? ''));
                            if (str_starts_with($waPhone, '0')) {
                                $waPhone = '62' . substr($waPhone, 1);
                            }

                            $followTimestamp = !empty($lead['next_follow_up_at'])
                                ? strtotime((string) $lead['next_follow_up_at'])
                                : false;
                            $isOverdue = $followTimestamp !== false
                                && $followTimestamp < time()
                                && !in_array($status, ['converted', 'not_interested', 'unreachable', 'cancelled'], true);
                            ?>
                            <tr>
                                <td class="admin-select-cell" data-label="Pilih">
                                    <input
                                        type="checkbox"
                                        name="selected_ids[]"
                                        value="<?= esc($lead['id']); ?>"
                                        class="admin-bulk-check"
                                        data-bulk-item
                                        aria-label="Pilih <?= esc($lead['lead_no'] ?? 'lead'); ?>">
                                </td>

                                <td data-label="Lead" class="lead-cell-lead">
                                    <div class="lead-number-line">
                                        <strong><?= esc($lead['lead_no'] ?? '-'); ?></strong>
                                        <span class="lead-source-badge lead-source-<?= esc($sourceClass); ?>">
                                            <i class="bi <?= esc($sourceIcons[$source] ?? 'bi-globe2'); ?>"></i>
                                            <?= esc($sourceLabels[$source] ?? ucfirst($source)); ?>
                                        </span>
                                    </div>
                                    <small><?= !empty($lead['created_at']) ? date('d M Y H:i', strtotime($lead['created_at'])) . ' WIB' : '-'; ?></small>
                                </td>

                                <td data-label="Calon Jamaah">
                                    <strong><?= esc($lead['full_name'] ?? '-'); ?></strong>
                                    <a href="https://wa.me/<?= esc($waPhone); ?>" target="_blank" rel="noopener" class="lead-wa-link">
                                        <i class="bi bi-whatsapp"></i>
                                        <?= esc($lead['phone'] ?? '-'); ?>
                                    </a>
                                    <small><?= esc($lead['city'] ?? '-'); ?> · <?= esc($lead['total_participants'] ?? 1); ?> jamaah</small>
                                </td>

                                <td data-label="Paket & Jadwal">
                                    <strong><?= esc($lead['package_name'] ?? 'Belum memilih paket'); ?></strong>
                                    <small><?= !empty($lead['departure_date']) ? date('d M Y', strtotime($lead['departure_date'])) : 'Belum memilih jadwal'; ?></small>
                                </td>

                                <td data-label="Potensi">
                                    <span class="lead-temperature lead-temperature-<?= esc($temperature); ?>">
                                        <i class="bi bi-fire"></i>
                                        <?= esc($temperatureLabels[$temperature] ?? ucfirst($temperature)); ?>
                                    </span>
                                </td>

                                <td data-label="Status">
                                    <span class="lead-status lead-status-<?= esc($status); ?>">
                                        <?= esc($statusLabels[$status] ?? $status); ?>
                                    </span>
                                </td>

                                <td data-label="Admin PIC">
                                    <?php if (!empty($lead['assigned_admin_name'])) : ?>
                                        <span class="lead-pic-name"><i class="bi bi-person-check"></i><?= esc($lead['assigned_admin_name']); ?></span>
                                    <?php else : ?>
                                        <span class="lead-pic-empty"><i class="bi bi-person-exclamation"></i>Belum ditugaskan</span>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Follow-up">
                                    <?php if ($followTimestamp !== false) : ?>
                                        <span class="lead-followup <?= $isOverdue ? 'is-overdue' : ''; ?>">
                                            <strong><?= date('d M Y', $followTimestamp); ?></strong>
                                            <small><?= date('H:i', $followTimestamp); ?> WIB<?= $isOverdue ? ' · Terlambat' : ''; ?></small>
                                        </span>
                                    <?php else : ?>
                                        <span class="lead-followup-empty">Belum dijadwalkan</span>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Aksi" class="lead-cell-actions">
                                    <div class="admin-table-action">
                                        <a href="<?= base_url('/admin/leads/detail/' . $lead['id']); ?>" class="btn btn-admin-primary btn-sm">
                                            <i class="bi bi-eye"></i>
                                            Detail
                                        </a>
                                        <?php if ($waPhone !== '') : ?>
                                            <a href="https://wa.me/<?= esc($waPhone); ?>" target="_blank" rel="noopener" class="btn btn-admin-outline btn-sm" title="Hubungi melalui WhatsApp">
                                                <i class="bi bi-whatsapp"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pager && $totalLeads > 0) : ?>
            <div class="lead-pagination-footer">
                <span>Halaman <?= esc($currentPage); ?> · <?= number_format($totalLeads, 0, ',', '.'); ?> data sesuai filter</span>
                <div class="lead-pagination-links"><?= $pager->links('leads', 'default_full'); ?></div>
            </div>
        <?php endif; ?>
    </div>
</form>

<?= $this->endSection(); ?>

<?= $this->section('script'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
const monthlyLabels = <?= json_encode(array_column($monthlyRows, 'month_label')); ?>;
const monthlyValues = <?= json_encode(array_map('intval', array_column($monthlyRows, 'total'))); ?>;
const packageLabels = <?= json_encode(array_map(static fn($row) => $row['package_name'] ?: 'Tanpa Paket', $packageRows)); ?>;
const packageValues = <?= json_encode(array_map('intval', array_column($packageRows, 'total'))); ?>;

const chartTextColor = '#6f655b';
const chartGridColor = 'rgba(130, 91, 44, .10)';

const brandGradient = (ctx) => {
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(192, 136, 61, .62)');
    gradient.addColorStop(1, 'rgba(192, 136, 61, .025)');
    return gradient;
};

const monthlyCanvas = document.getElementById('leadMonthlyChart');
if (monthlyCanvas) {
    new Chart(monthlyCanvas, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Lead masuk',
                data: monthlyValues,
                fill: true,
                borderColor: '#a66e2d',
                backgroundColor: brandGradient(monthlyCanvas.getContext('2d')),
                tension: .38,
                borderWidth: 3,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#17120e',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    displayColors: false,
                    callbacks: { label: (context) => `${context.parsed.y} lead` }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0, color: chartTextColor },
                    grid: { color: chartGridColor }
                },
                x: {
                    ticks: { color: chartTextColor, maxRotation: 0 },
                    grid: { display: false }
                }
            }
        }
    });
}

const packageCanvas = document.getElementById('leadPackageChart');
if (packageCanvas) {
    new Chart(packageCanvas, {
        type: 'doughnut',
        data: {
            labels: packageLabels,
            datasets: [{
                data: packageValues,
                backgroundColor: ['#17120e', '#a66e2d', '#d5ae6c', '#79501f', '#d9c7a6', '#665445'],
                borderColor: '#fffefd',
                borderWidth: 3,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '67%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        boxHeight: 10,
                        usePointStyle: true,
                        color: chartTextColor,
                        padding: 16
                    }
                },
                tooltip: {
                    callbacks: { label: (context) => `${context.label}: ${context.parsed} lead` }
                }
            }
        }
    });
}
</script>
<?= $this->endSection(); ?>

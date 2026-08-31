<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LeadActivityModel;
use App\Models\LeadModel;
use App\Controllers\Admin\Concerns\BulkActionSupport;

class LeadController extends BaseController
{
    use BulkActionSupport;
    protected LeadModel $leadModel;
    protected LeadActivityModel $activityModel;

    public function __construct()
    {
        $this->leadModel     = new LeadModel();
        $this->activityModel = new LeadActivityModel();
    }


    private function readFilters(): array
    {
        $perPage = (int) $this->request->getGet('per_page');
        $allowedPerPage = [10, 20, 50, 100];
        $attention = trim((string) $this->request->getGet('attention'));

        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 20;
        }

        if (!in_array($attention, ['pipeline', 'today', 'overdue', 'unassigned'], true)) {
            $attention = '';
        }

        return [
            'q'                 => trim((string) $this->request->getGet('q')),
            'status'            => trim((string) $this->request->getGet('status')),
            'temperature'       => trim((string) $this->request->getGet('temperature')),
            'package_id'        => trim((string) $this->request->getGet('package_id')),
            'assigned_admin_id' => trim((string) $this->request->getGet('assigned_admin_id')),
            'date_start'        => trim((string) $this->request->getGet('date_start')),
            'date_end'          => trim((string) $this->request->getGet('date_end')),
            'attention'         => $attention,
            'per_page'          => $perPage,
        ];
    }

    private function leadQuery(): LeadModel
    {
        return (new LeadModel())
            ->select('travel_leads.*, packages.name AS package_name, packages.slug AS package_slug, package_departures.departure_date, package_departures.return_date, users.name AS assigned_admin_name')
            ->join('packages', 'packages.id = travel_leads.package_id', 'left')
            ->join('package_departures', 'package_departures.id = travel_leads.departure_id', 'left')
            ->join('users', 'users.id = travel_leads.assigned_admin_id', 'left');
    }

    private function applyLeadFilters($query, array $filters)
    {
        if ($filters['q'] !== '') {
            $query->groupStart()
                ->like('travel_leads.lead_no', $filters['q'])
                ->orLike('travel_leads.full_name', $filters['q'])
                ->orLike('travel_leads.phone', $filters['q'])
                ->orLike('travel_leads.email', $filters['q'])
                ->orLike('travel_leads.city', $filters['q'])
                ->orLike('packages.name', $filters['q'])
                ->groupEnd();
        }

        if ($filters['status'] !== '') {
            $query->where('travel_leads.status', $filters['status']);
        }

        if ($filters['temperature'] !== '') {
            $query->where('travel_leads.lead_temperature', $filters['temperature']);
        }

        if ($filters['package_id'] !== '') {
            $query->where('travel_leads.package_id', (int) $filters['package_id']);
        }

        if ($filters['assigned_admin_id'] !== '') {
            $query->where('travel_leads.assigned_admin_id', (int) $filters['assigned_admin_id']);
        }

        if ($filters['date_start'] !== '') {
            $query->where('travel_leads.created_at >=', $filters['date_start'] . ' 00:00:00');
        }

        if ($filters['date_end'] !== '') {
            $query->where('travel_leads.created_at <=', $filters['date_end'] . ' 23:59:59');
        }

        if ($filters['attention'] === 'pipeline') {
            $query->whereIn('travel_leads.status', ['contacted', 'follow_up', 'qualified', 'waiting_decision']);
        }

        if ($filters['attention'] === 'today') {
            $query
                ->where('travel_leads.next_follow_up_at >=', date('Y-m-d 00:00:00'))
                ->where('travel_leads.next_follow_up_at <=', date('Y-m-d 23:59:59'))
                ->whereNotIn('travel_leads.status', ['converted', 'not_interested', 'unreachable', 'cancelled']);
        }

        if ($filters['attention'] === 'overdue') {
            $query
                ->where('travel_leads.next_follow_up_at IS NOT NULL', null, false)
                ->where('travel_leads.next_follow_up_at <', date('Y-m-d H:i:s'))
                ->whereNotIn('travel_leads.status', ['converted', 'not_interested', 'unreachable', 'cancelled']);
        }

        if ($filters['attention'] === 'unassigned') {
            $query->where('travel_leads.assigned_admin_id', null);
        }

        return $query;
    }

    private function cleanCsvValue(mixed $value): string
    {
        $value = trim(preg_replace('/\\s+/u', ' ', (string) ($value ?? '')) ?? '');

        // Mencegah CSV formula injection ketika file dibuka di Excel.
        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) {
            $value = "'" . $value;
        }

        return $value;
    }

    private function csvPhone(mixed $value): string
    {
        $phone = preg_replace('/[^0-9+]/', '', (string) ($value ?? '')) ?? '';

        if ($phone === '') {
            return '';
        }

        // Paksa Excel membaca nomor sebagai teks agar angka 0 di depan tidak hilang.
        return '="' . str_replace('"', '', $phone) . '"';
    }

    private function formatCsvDate(mixed $value, bool $withTime = true): string
    {
        if (empty($value)) {
            return '';
        }

        $timestamp = strtotime((string) $value);

        if ($timestamp === false) {
            return $this->cleanCsvValue($value);
        }

        return date($withTime ? 'd/m/Y H:i' : 'd/m/Y', $timestamp);
    }

    private function sourceLabel(mixed $source): string
    {
        return match (strtolower(trim((string) $source))) {
            'chatbot'                => 'Chatbot Website',
            'website',
            'website-package-detail' => 'Form Website',
            'whatsapp'               => 'WhatsApp',
            'manual'                 => 'Input Manual',
            default                  => ucwords(str_replace(['_', '-'], ' ', (string) ($source ?: 'Website'))),
        };
    }

    public function index()
    {
        $db      = \Config\Database::connect();
        $filters = $this->readFilters();

        $listModel = $this->applyLeadFilters($this->leadQuery(), $filters);
        $leads = $listModel
            ->orderBy('travel_leads.id', 'DESC')
            ->paginate($filters['per_page'], 'leads');
        $pager = $listModel->pager;

        // Data analitik mengikuti filter yang sedang aktif agar angka dan export konsisten.
        $analyticsRows = $this->applyLeadFilters($this->leadQuery(), $filters)
            ->orderBy('travel_leads.id', 'DESC')
            ->findAll();

        $globalTotalLeads = (new LeadModel())->countAllResults();
        $totalLeads       = count($analyticsRows);
        $newLeads         = 0;
        $followUpLeads    = 0;
        $hotLeads         = 0;
        $convertedLeads   = 0;
        $followUpToday    = 0;
        $overdueFollowUps = 0;
        $unassignedLeads  = 0;

        $followStatuses = ['contacted', 'follow_up', 'qualified', 'waiting_decision'];
        $closedStatuses = ['converted', 'not_interested', 'unreachable', 'cancelled'];
        $todayStart      = strtotime(date('Y-m-d 00:00:00'));
        $todayEnd        = strtotime(date('Y-m-d 23:59:59'));
        $now             = time();

        foreach ($analyticsRows as $row) {
            $status = $row['status'] ?? 'new';

            if ($status === 'new') {
                $newLeads++;
            }

            if (in_array($status, $followStatuses, true)) {
                $followUpLeads++;
            }

            if (($row['lead_temperature'] ?? '') === 'hot') {
                $hotLeads++;
            }

            if ($status === 'converted') {
                $convertedLeads++;
            }

            if (empty($row['assigned_admin_id'])) {
                $unassignedLeads++;
            }

            if (!empty($row['next_follow_up_at'])) {
                $followTimestamp = strtotime((string) $row['next_follow_up_at']);

                if (
                    $followTimestamp !== false
                    && $followTimestamp >= $todayStart
                    && $followTimestamp <= $todayEnd
                    && !in_array($status, $closedStatuses, true)
                ) {
                    $followUpToday++;
                }

                if (
                    $followTimestamp !== false
                    && $followTimestamp < $now
                    && !in_array($status, $closedStatuses, true)
                ) {
                    $overdueFollowUps++;
                }
            }
        }

        $conversionRate = $totalLeads > 0
            ? round(($convertedLeads / $totalLeads) * 100, 1)
            : 0;

        // Lengkapi 6 bulan terakhir, termasuk bulan tanpa lead.
        $monthMap = [];
        $monthCursor = new \DateTimeImmutable('first day of -5 months');
        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        for ($i = 0; $i < 6; $i++) {
            $key = $monthCursor->format('Y-m');
            $monthMap[$key] = [
                'month_key'   => $key,
                'month_label' => $monthNames[(int) $monthCursor->format('n')] . ' ' . $monthCursor->format('Y'),
                'total'       => 0,
            ];
            $monthCursor = $monthCursor->modify('+1 month');
        }

        $packageMap = [];

        foreach ($analyticsRows as $row) {
            if (!empty($row['created_at'])) {
                $key = date('Y-m', strtotime((string) $row['created_at']));
                if (isset($monthMap[$key])) {
                    $monthMap[$key]['total']++;
                }
            }

            $packageName = trim((string) ($row['package_name'] ?? '')) ?: 'Tanpa Paket';
            $packageMap[$packageName] = ($packageMap[$packageName] ?? 0) + 1;
        }

        arsort($packageMap);
        $packageRows = [];
        foreach (array_slice($packageMap, 0, 6, true) as $packageName => $total) {
            $packageRows[] = ['package_name' => $packageName, 'total' => $total];
        }

        $packages = $db->table('packages')
            ->select('id, name')
            ->where('deleted_at', null)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        $admins = $db->table('users')
            ->select('id, name')
            ->whereIn('role', ['admin', 'super_admin'])
            ->where('status', 'active')
            ->where('deleted_at', null)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        $packageNames = array_column($packages, 'name', 'id');
        $adminNames   = array_column($admins, 'name', 'id');
        $statusLabels = $this->leadModel->statusLabels();
        $temperatureLabels = $this->leadModel->temperatureLabels();

        $activeFilters = [];
        if ($filters['q'] !== '') {
            $activeFilters[] = 'Pencarian: “' . $filters['q'] . '”';
        }
        if ($filters['status'] !== '') {
            $activeFilters[] = 'Status: ' . ($statusLabels[$filters['status']] ?? $filters['status']);
        }
        if ($filters['temperature'] !== '') {
            $activeFilters[] = 'Potensi: ' . ($temperatureLabels[$filters['temperature']] ?? $filters['temperature']);
        }
        if ($filters['package_id'] !== '') {
            $activeFilters[] = 'Paket: ' . ($packageNames[(int) $filters['package_id']] ?? 'Tidak ditemukan');
        }
        if ($filters['assigned_admin_id'] !== '') {
            $activeFilters[] = 'PIC: ' . ($adminNames[(int) $filters['assigned_admin_id']] ?? 'Tidak ditemukan');
        }
        if ($filters['date_start'] !== '') {
            $activeFilters[] = 'Mulai: ' . date('d M Y', strtotime($filters['date_start']));
        }
        if ($filters['date_end'] !== '') {
            $activeFilters[] = 'Sampai: ' . date('d M Y', strtotime($filters['date_end']));
        }
        if ($filters['attention'] !== '') {
            $attentionLabels = [
                'pipeline'   => 'Pipeline berjalan',
                'today'      => 'Follow-up hari ini',
                'overdue'    => 'Follow-up terlambat',
                'unassigned' => 'Belum ada PIC',
            ];
            $activeFilters[] = 'Prioritas: ' . ($attentionLabels[$filters['attention']] ?? $filters['attention']);
        }

        $exportFilters = $filters;
        unset($exportFilters['per_page']);
        $exportFilters = array_filter($exportFilters, static fn ($value) => $value !== '' && $value !== null);
        $exportUrl = base_url('/admin/leads/export-csv')
            . ($exportFilters !== [] ? '?' . http_build_query($exportFilters) : '');

        return view('admin/leads/index', [
            'title'             => 'Reporting Leads',
            'leads'             => $leads,
            'pager'             => $pager,
            'filters'           => $filters,
            'activeFilters'     => $activeFilters,
            'exportUrl'         => $exportUrl,
            'statusLabels'      => $statusLabels,
            'temperatureLabels' => $temperatureLabels,
            'packages'          => $packages,
            'admins'            => $admins,
            'globalTotalLeads'  => $globalTotalLeads,
            'totalLeads'        => $totalLeads,
            'newLeads'          => $newLeads,
            'followUpLeads'     => $followUpLeads,
            'hotLeads'          => $hotLeads,
            'convertedLeads'    => $convertedLeads,
            'followUpToday'     => $followUpToday,
            'overdueFollowUps'  => $overdueFollowUps,
            'unassignedLeads'   => $unassignedLeads,
            'conversionRate'    => $conversionRate,
            'monthlyRows'       => array_values($monthMap),
            'packageRows'       => $packageRows,
        ]);
    }

    public function detail(int $id)
    {
        $db = \Config\Database::connect();

        $lead = $this->leadModel
            ->select('travel_leads.*, packages.name AS package_name, packages.slug AS package_slug, packages.price AS package_price, package_departures.departure_date, package_departures.return_date, users.name AS assigned_admin_name')
            ->join('packages', 'packages.id = travel_leads.package_id', 'left')
            ->join('package_departures', 'package_departures.id = travel_leads.departure_id', 'left')
            ->join('users', 'users.id = travel_leads.assigned_admin_id', 'left')
            ->where('travel_leads.id', $id)
            ->first();

        if (!$lead) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Lead tidak ditemukan.');
        }

        $activities = $this->activityModel
            ->select('travel_lead_activities.*, users.name AS admin_name')
            ->join('users', 'users.id = travel_lead_activities.admin_id', 'left')
            ->where('lead_id', $id)
            ->orderBy('travel_lead_activities.id', 'DESC')
            ->findAll();

        $admins = $db->table('users')
            ->select('id, name')
            ->whereIn('role', ['admin', 'super_admin'])
            ->where('status', 'active')
            ->where('deleted_at', null)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        return view('admin/leads/detail', [
            'title'             => 'Detail Lead ' . ($lead['lead_no'] ?? ''),
            'lead'              => $lead,
            'activities'        => $activities,
            'admins'            => $admins,
            'statusLabels'      => $this->leadModel->statusLabels(),
            'temperatureLabels' => $this->leadModel->temperatureLabels(),
        ]);
    }

    public function update(int $id)
    {
        $lead = $this->leadModel->find($id);

        if (!$lead) {
            return redirect()->to('/admin/leads')->with('error', 'Lead tidak ditemukan.');
        }

        $status      = (string) $this->request->getPost('status');
        $temperature = (string) $this->request->getPost('lead_temperature');
        $adminId     = $this->request->getPost('assigned_admin_id');
        $followUpAt  = trim((string) $this->request->getPost('next_follow_up_at'));
        $followUpNote= trim((string) $this->request->getPost('follow_up_note'));
        $lostReason  = trim((string) $this->request->getPost('lost_reason'));

        if (!array_key_exists($status, $this->leadModel->statusLabels())) {
            return redirect()->back()->with('error', 'Status lead tidak valid.');
        }

        if (!array_key_exists($temperature, $this->leadModel->temperatureLabels())) {
            return redirect()->back()->with('error', 'Kategori lead tidak valid.');
        }

        $data = [
            'status'            => $status,
            'lead_temperature'  => $temperature,
            'assigned_admin_id' => $adminId !== '' ? (int) $adminId : null,
            'next_follow_up_at' => $followUpAt !== '' ? date('Y-m-d H:i:s', strtotime($followUpAt)) : null,
            'lost_reason'       => $lostReason !== '' ? $lostReason : null,
        ];

        if (in_array($status, ['contacted', 'follow_up', 'qualified', 'waiting_decision'], true)) {
            $data['last_contacted_at'] = date('Y-m-d H:i:s');
        }

        if ($status === 'converted' && empty($lead['converted_at'])) {
            $data['converted_at'] = date('Y-m-d H:i:s');
        }

        if ($status !== 'converted') {
            $data['converted_at'] = null;
        }

        $this->leadModel->update($id, $data);

        $description = $followUpNote !== ''
            ? $followUpNote
            : 'Data pipeline lead diperbarui.';

        $this->activityModel->record(
            $id,
            'lead_updated',
            $description,
            $lead['status'] ?? null,
            $status,
            (int) session()->get('admin_user_id')
        );

        log_admin_activity(
            'Reporting Leads',
            'Update Lead',
            'Admin memperbarui lead ' . ($lead['lead_no'] ?? '#' . $id) . ' menjadi ' . ($this->leadModel->statusLabels()[$status] ?? $status) . '.'
        );

        return redirect()->back()->with('success', 'Lead berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $lead = $this->leadModel->find($id);

        if (!$lead) {
            return redirect()->back()->with('error', 'Lead tidak ditemukan.');
        }

        $reason = trim((string) $this->request->getPost('delete_reason')) ?: 'Dipindahkan ke Sampah oleh admin.';

        $this->leadModel->update($id, [
            'deleted_by'   => (int) session()->get('admin_user_id'),
            'delete_reason'=> $reason,
        ]);
        $this->leadModel->delete($id);

        $this->activityModel->record(
            $id,
            'soft_delete',
            $reason,
            $lead['status'] ?? null,
            null,
            (int) session()->get('admin_user_id')
        );

        log_admin_activity('Reporting Leads', 'Pindah ke Sampah', 'Lead ' . ($lead['lead_no'] ?? '#' . $id) . ' dipindahkan ke Sampah.');

        return redirect()->to('/admin/leads')->with('success', 'Lead berhasil dipindahkan ke Sampah.');
    }


    public function bulkDelete()
    {
        $ids = $this->selectedIds();

        if ($ids === []) {
            return redirect()->back()->with('error', 'Pilih minimal satu lead yang akan dipindahkan ke Sampah.');
        }

        $deleted = 0;
        foreach ($ids as $id) {
            $lead = $this->leadModel->find($id);
            if (!$lead) {
                continue;
            }

            $this->leadModel->update($id, [
                'deleted_by'    => (int) session()->get('admin_user_id'),
                'delete_reason' => 'Penghapusan massal dari daftar leads.',
            ]);
            $this->leadModel->delete($id);
            $this->activityModel->record(
                $id,
                'soft_delete',
                'Dipindahkan ke Sampah melalui aksi massal.',
                $lead['status'] ?? null,
                null,
                (int) session()->get('admin_user_id')
            );
            $deleted++;
        }

        if ($deleted > 0) {
            log_admin_activity('Reporting Leads', 'Pindah Massal ke Sampah', $deleted . ' lead dipindahkan ke Sampah.');
        }

        return redirect()->to('/admin/leads')->with('success', $deleted . ' lead berhasil dipindahkan ke Sampah.');
    }

    public function trash()
    {
        $leads = $this->leadModel
            ->onlyDeleted()
            ->select('travel_leads.*, packages.name AS package_name, users.name AS deleted_by_name')
            ->join('packages', 'packages.id = travel_leads.package_id', 'left')
            ->join('users', 'users.id = travel_leads.deleted_by', 'left')
            ->orderBy('travel_leads.deleted_at', 'DESC')
            ->findAll();

        return view('admin/leads/trash', [
            'title'          => 'Sampah Leads',
            'leads'          => $leads,
            'statusLabels'   => $this->leadModel->statusLabels(),
        ]);
    }

    public function restore(int $id)
    {
        $lead = $this->leadModel->withDeleted()->find($id);

        if (!$lead || empty($lead['deleted_at'])) {
            return redirect()->back()->with('error', 'Lead tidak ditemukan di Sampah.');
        }

        $this->leadModel->builder()
            ->where('id', $id)
            ->update([
                'deleted_at'    => null,
                'deleted_by'    => null,
                'delete_reason' => null,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);

        $this->activityModel->record($id, 'restore', 'Lead dipulihkan dari Sampah.', null, $lead['status'] ?? null, (int) session()->get('admin_user_id'));
        log_admin_activity('Reporting Leads', 'Pulihkan Lead', 'Lead ' . ($lead['lead_no'] ?? '#' . $id) . ' dipulihkan dari Sampah.');

        return redirect()->to('/admin/leads/trash')->with('success', 'Lead berhasil dipulihkan.');
    }

    public function forceDelete(int $id)
    {
        if (session()->get('admin_role') !== 'super_admin') {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat menghapus lead permanen.');
        }

        $lead = $this->leadModel->withDeleted()->find($id);

        if (!$lead || empty($lead['deleted_at'])) {
            return redirect()->back()->with('error', 'Lead harus berada di Sampah sebelum dihapus permanen.');
        }

        $this->activityModel->where('lead_id', $id)->delete();
        $this->leadModel->delete($id, true);

        log_admin_activity('Reporting Leads', 'Hapus Permanen', 'Lead ' . ($lead['lead_no'] ?? '#' . $id) . ' dihapus permanen.');

        return redirect()->to('/admin/leads/trash')->with('success', 'Lead berhasil dihapus permanen.');
    }


    public function trashBulkAction()
    {
        $ids = $this->selectedIds();
        $action = (string) $this->request->getPost('bulk_action');

        if ($ids === []) {
            return redirect()->back()->with('error', 'Pilih minimal satu lead di Sampah.');
        }

        if (!in_array($action, ['restore', 'force_delete'], true)) {
            return redirect()->back()->with('error', 'Aksi massal tidak valid.');
        }

        if ($action === 'force_delete' && !$this->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat menghapus lead permanen.');
        }

        $processed = 0;
        foreach ($ids as $id) {
            $lead = $this->leadModel->withDeleted()->find($id);
            if (!$lead || empty($lead['deleted_at'])) {
                continue;
            }

            if ($action === 'restore') {
                $this->leadModel->builder()->where('id', $id)->update([
                    'deleted_at' => null,
                    'deleted_by' => null,
                    'delete_reason' => null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $this->activityModel->record($id, 'restore', 'Dipulihkan melalui aksi massal.', null, $lead['status'] ?? null, (int) session()->get('admin_user_id'));
            } else {
                $this->activityModel->where('lead_id', $id)->delete();
                $this->leadModel->delete($id, true);
            }
            $processed++;
        }

        $label = $action === 'restore' ? 'dipulihkan' : 'dihapus permanen';
        log_admin_activity('Reporting Leads', 'Aksi Massal Sampah', $processed . ' lead ' . $label . '.');

        return redirect()->to('/admin/leads/trash')->with('success', $processed . ' lead berhasil ' . $label . '.');
    }

    public function emptyTrash()
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat mengosongkan Sampah leads.');
        }

        $leads = $this->leadModel->onlyDeleted()->findAll();
        $deleted = 0;

        foreach ($leads as $lead) {
            $id = (int) $lead['id'];
            $this->activityModel->where('lead_id', $id)->delete();
            $this->leadModel->delete($id, true);
            $deleted++;
        }

        if ($deleted > 0) {
            log_admin_activity('Reporting Leads', 'Kosongkan Sampah', $deleted . ' lead dihapus permanen dari Sampah.');
        }

        return redirect()->to('/admin/leads/trash')->with('success', 'Sampah leads dikosongkan. ' . $deleted . ' data dihapus permanen.');
    }

    public function exportCsv()
    {
        $filters = $this->readFilters();

        $rows = $this->applyLeadFilters($this->leadQuery(), $filters)
            ->orderBy('travel_leads.id', 'DESC')
            ->findAll();

        $filename = 'reporting-leads-WIB-' . date('Ymd-His') . '.csv';
        $handle   = fopen('php://temp', 'w+');

        if ($handle === false) {
            return redirect()->back()->with('error', 'File export tidak dapat dibuat.');
        }

        // UTF-8 BOM + deklarasi separator membantu Excel membaca karakter Indonesia dan kolom dengan benar.
        fwrite($handle, "\xEF\xBB\xBF");
        fwrite($handle, "sep=;\r\n");

        fputcsv($handle, [
            'No.',
            'No. Lead',
            'Tanggal Masuk (WIB)',
            'Nama Calon Jamaah',
            'Nomor WhatsApp',
            'Email',
            'Domisili',
            'Sumber Lead',
            'Nama Paket',
            'Tanggal Berangkat',
            'Tanggal Pulang',
            'Jumlah Jamaah',
            'Status Lead',
            'Tingkat Potensi',
            'Admin PIC',
            'Follow-up Berikutnya (WIB)',
            'Terakhir Dihubungi (WIB)',
            'Tanggal Berhasil Daftar (WIB)',
            'Catatan Calon Jamaah',
            'Alasan Tidak Lanjut',
        ], ';', '"', '\\', "\r\n");

        $statusLabels      = $this->leadModel->statusLabels();
        $temperatureLabels = $this->leadModel->temperatureLabels();

        foreach ($rows as $index => $row) {
            fputcsv($handle, [
                $index + 1,
                $this->cleanCsvValue($row['lead_no'] ?? ''),
                $this->formatCsvDate($row['created_at'] ?? null),
                $this->cleanCsvValue($row['full_name'] ?? ''),
                $this->csvPhone($row['phone'] ?? ''),
                $this->cleanCsvValue($row['email'] ?? ''),
                $this->cleanCsvValue($row['city'] ?? ''),
                $this->cleanCsvValue($this->sourceLabel($row['source'] ?? 'website')),
                $this->cleanCsvValue($row['package_name'] ?? 'Tanpa Paket'),
                $this->formatCsvDate($row['departure_date'] ?? null, false),
                $this->formatCsvDate($row['return_date'] ?? null, false),
                (int) ($row['total_participants'] ?? 1),
                $this->cleanCsvValue($statusLabels[$row['status'] ?? ''] ?? ($row['status'] ?? '')),
                $this->cleanCsvValue($temperatureLabels[$row['lead_temperature'] ?? ''] ?? ucfirst((string) ($row['lead_temperature'] ?? ''))),
                $this->cleanCsvValue($row['assigned_admin_name'] ?? 'Belum ditugaskan'),
                $this->formatCsvDate($row['next_follow_up_at'] ?? null),
                $this->formatCsvDate($row['last_contacted_at'] ?? null),
                $this->formatCsvDate($row['converted_at'] ?? null),
                $this->cleanCsvValue($row['notes'] ?? ''),
                $this->cleanCsvValue($row['lost_reason'] ?? ''),
            ], ';', '"', '\\', "\r\n");
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        log_admin_activity(
            'Reporting Leads',
            'Export CSV',
            count($rows) . ' data lead diekspor ke CSV' . ($filters['q'] !== '' ? ' dengan filter pencarian.' : '.')
        );

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setBody($content);
    }

}

<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Admin\Concerns\BulkActionSupport;
use App\Models\PackageModel;

class PackageController extends BaseController
{
    use BulkActionSupport;
    private function generateSlug($text)
    {
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
    }

    private function makeUniqueSlug($name, $ignoreId = null)
    {
        $packageModel = new PackageModel();

        $baseSlug = $this->generateSlug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (true) {
            $builder = $packageModel->withDeleted()->where('slug', $slug);

            if ($ignoreId) {
                $builder->where('id !=', $ignoreId);
            }

            $exists = $builder->first();

            if (!$exists) {
                return $slug;
            }

            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
    }

    public function index()
    {
        $packageModel = new PackageModel();

        $packages = $packageModel
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('admin/packages/index', [
            'title'          => 'Data Paket Umroh',
            'packages'       => $packages,
            'programOptions' => PackageModel::getProgramOptions(),
        ]);
    }

    public function create()
    {
        return view('admin/packages/create', [
            'title'          => 'Tambah Paket Umroh',
            'programOptions' => PackageModel::getProgramOptions(),
        ]);
    }

    public function store()
    {
        $programKeys = array_keys(PackageModel::getProgramOptions());

        $rules = [
            'name'            => 'required|min_length[3]',
            'program'         => 'required|in_list[' . implode(',', $programKeys) . ']',
            'description'     => 'required',
            'airline'         => 'permit_empty|max_length[255]',
            'hotel_makkah'    => 'permit_empty|max_length[2000]',
            'hotel_madinah'   => 'permit_empty|max_length[2000]',
            'facilities_text' => 'permit_empty|max_length[5000]',
            'price'           => 'required|numeric',
            'duration_days'   => 'required|numeric',
            'duration_nights' => 'required|numeric',
            'status'          => 'required|in_list[active,inactive]',
            'cover_image'     => 'permit_empty|max_size[cover_image,2048]|mime_in[cover_image,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $packageModel = new PackageModel();

        $coverImagePath = null;
        $coverImage = $this->request->getFile('cover_image');

        if ($coverImage && $coverImage->isValid() && !$coverImage->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/packages';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0775, true);
            }

            $newName = $coverImage->getRandomName();
            $coverImage->move($uploadPath, $newName);

            $coverImagePath = 'uploads/packages/' . $newName;
        }

        $name = $this->request->getPost('name');

        $packageModel->insert([
            'name'            => $name,
            'slug'            => $this->makeUniqueSlug($name),
            'badge'           => $this->request->getPost('badge'),
            'program'         => strtolower(trim((string) $this->request->getPost('program'))),
            'description'     => $this->request->getPost('description'),
            'airline'         => trim((string) $this->request->getPost('airline')),
            'hotel_makkah'    => trim((string) $this->request->getPost('hotel_makkah')),
            'hotel_madinah'   => trim((string) $this->request->getPost('hotel_madinah')),
            'facilities_text' => trim((string) $this->request->getPost('facilities_text')),
            'price'           => $this->request->getPost('price'),
            'duration_days'   => $this->request->getPost('duration_days'),
            'duration_nights' => $this->request->getPost('duration_nights'),
            'cover_image'     => $coverImagePath,
            'status'          => $this->request->getPost('status'),
        ]);
        log_admin_activity(
            'Paket Umroh',
            'Tambah Paket',
            'Admin menambahkan paket: ' . $name . '.'
        );
        return redirect()
            ->to('/admin/packages')
            ->with('success', 'Paket umroh berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $packageModel = new PackageModel();

        $package = $packageModel->find($id);

        if (!$package) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Paket tidak ditemukan');
        }

        return view('admin/packages/edit', [
            'title'          => 'Edit Paket Umroh',
            'package'        => $package,
            'programOptions' => PackageModel::getProgramOptions(),
        ]);
    }

    public function update($id)
    {
        $packageModel = new PackageModel();

        $package = $packageModel->find($id);

        if (!$package) {
            return redirect()
                ->to('/admin/packages')
                ->with('error', 'Paket tidak ditemukan.');
        }

        $programKeys = array_keys(PackageModel::getProgramOptions());

        $rules = [
            'name'            => 'required|min_length[3]',
            'program'         => 'required|in_list[' . implode(',', $programKeys) . ']',
            'description'     => 'required',
            'airline'         => 'permit_empty|max_length[255]',
            'hotel_makkah'    => 'permit_empty|max_length[2000]',
            'hotel_madinah'   => 'permit_empty|max_length[2000]',
            'facilities_text' => 'permit_empty|max_length[5000]',
            'price'           => 'required|numeric',
            'duration_days'   => 'required|numeric',
            'duration_nights' => 'required|numeric',
            'status'          => 'required|in_list[active,inactive]',
            'cover_image'     => 'permit_empty|max_size[cover_image,2048]|mime_in[cover_image,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $coverImagePath = $package['cover_image'];
        $coverImage = $this->request->getFile('cover_image');

        if ($coverImage && $coverImage->isValid() && !$coverImage->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/packages';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0775, true);
            }

            $newName = $coverImage->getRandomName();
            $coverImage->move($uploadPath, $newName);

            if (!empty($package['cover_image']) && file_exists(FCPATH . $package['cover_image'])) {
                unlink(FCPATH . $package['cover_image']);
            }

            $coverImagePath = 'uploads/packages/' . $newName;
        }

        $name = $this->request->getPost('name');

        $packageModel->update($id, [
            'name'            => $name,
            'slug'            => $this->makeUniqueSlug($name, $id),
            'badge'           => $this->request->getPost('badge'),
            'program'         => strtolower(trim((string) $this->request->getPost('program'))),
            'description'     => $this->request->getPost('description'),
            'airline'         => trim((string) $this->request->getPost('airline')),
            'hotel_makkah'    => trim((string) $this->request->getPost('hotel_makkah')),
            'hotel_madinah'   => trim((string) $this->request->getPost('hotel_madinah')),
            'facilities_text' => trim((string) $this->request->getPost('facilities_text')),
            'price'           => $this->request->getPost('price'),
            'duration_days'   => $this->request->getPost('duration_days'),
            'duration_nights' => $this->request->getPost('duration_nights'),
            'cover_image'     => $coverImagePath,
            'status'          => $this->request->getPost('status'),
        ]);
        log_admin_activity(
            'Paket Umroh',
            'Update Paket',
            'Admin memperbarui paket ID #' . $id . '.'
        );
        return redirect()
            ->to('/admin/packages')
            ->with('success', 'Paket umroh berhasil diperbarui.');
    }

    public function delete($id)
    {
        $packageModel = new PackageModel();
        $package = $packageModel->find($id);

        if (!$package) {
            return redirect()->to('/admin/packages')->with('error', 'Paket tidak ditemukan.');
        }

        $packageModel->delete($id);
        log_admin_activity('Paket Umroh', 'Pindah ke Sampah', 'Paket ' . ($package['name'] ?? '#' . $id) . ' dipindahkan ke Sampah.');

        return redirect()->to('/admin/packages')->with('success', 'Paket berhasil dipindahkan ke Sampah.');
    }

    public function bulkDelete()
    {
        $ids = $this->selectedIds();
        if ($ids === []) {
            return redirect()->back()->with('error', 'Pilih minimal satu paket.');
        }

        $packageModel = new PackageModel();
        $deleted = 0;
        foreach ($ids as $id) {
            if (!$packageModel->find($id)) {
                continue;
            }
            $packageModel->delete($id);
            $deleted++;
        }

        if ($deleted > 0) {
            log_admin_activity('Paket Umroh', 'Pindah Massal ke Sampah', $deleted . ' paket dipindahkan ke Sampah.');
        }

        return redirect()->to('/admin/packages')->with('success', $deleted . ' paket berhasil dipindahkan ke Sampah.');
    }

    public function trash()
    {
        $packages = (new PackageModel())
            ->onlyDeleted()
            ->orderBy('deleted_at', 'DESC')
            ->findAll();

        return view('admin/packages/trash', [
            'title' => 'Sampah Paket',
            'packages' => $packages,
        ]);
    }

    public function restore($id)
    {
        $model = new PackageModel();
        $package = $model->withDeleted()->find($id);

        if (!$package || empty($package['deleted_at'])) {
            return redirect()->back()->with('error', 'Paket tidak ditemukan di Sampah.');
        }

        $slug = (string) $package['slug'];
        $conflict = $model->withDeleted()
            ->where('slug', $slug)
            ->where('id !=', $id)
            ->first();

        if ($conflict) {
            $slug .= '-restored-' . $id;
        }

        $model->builder()->where('id', $id)->update([
            'slug' => $slug,
            'deleted_at' => null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        log_admin_activity('Paket Umroh', 'Pulihkan Paket', 'Paket ' . ($package['name'] ?? '#' . $id) . ' dipulihkan dari Sampah.');
        return redirect()->to('/admin/packages/trash')->with('success', 'Paket berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat menghapus paket permanen.');
        }

        $result = $this->forceDeletePackage((int) $id);
        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        log_admin_activity('Paket Umroh', 'Hapus Permanen', $result['message']);
        return redirect()->to('/admin/packages/trash')->with('success', $result['message']);
    }

    public function trashBulkAction()
    {
        $ids = $this->selectedIds();
        $action = (string) $this->request->getPost('bulk_action');

        if ($ids === []) {
            return redirect()->back()->with('error', 'Pilih minimal satu paket di Sampah.');
        }

        if (!in_array($action, ['restore', 'force_delete'], true)) {
            return redirect()->back()->with('error', 'Aksi massal tidak valid.');
        }

        if ($action === 'force_delete' && !$this->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat menghapus paket permanen.');
        }

        $processed = 0;
        $skipped = 0;
        $model = new PackageModel();

        foreach ($ids as $id) {
            if ($action === 'restore') {
                $package = $model->withDeleted()->find($id);
                if (!$package || empty($package['deleted_at'])) {
                    $skipped++;
                    continue;
                }
                $slug = (string) $package['slug'];
                $conflict = $model->withDeleted()->where('slug', $slug)->where('id !=', $id)->first();
                if ($conflict) {
                    $slug .= '-restored-' . $id;
                }
                $model->builder()->where('id', $id)->update(['slug' => $slug, 'deleted_at' => null, 'updated_at' => date('Y-m-d H:i:s')]);
                $processed++;
            } else {
                $result = $this->forceDeletePackage($id);
                $result['success'] ? $processed++ : $skipped++;
            }
        }

        $label = $action === 'restore' ? 'dipulihkan' : 'dihapus permanen';
        log_admin_activity('Paket Umroh', 'Aksi Massal Sampah', $processed . ' paket ' . $label . '; ' . $skipped . ' dilewati.');

        return redirect()->to('/admin/packages/trash')->with('success', $processed . ' paket ' . $label . '. ' . $skipped . ' paket dilewati karena tidak ditemukan atau tidak berada di Sampah.');
    }

    public function emptyTrash()
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat mengosongkan Sampah paket.');
        }

        $packages = (new PackageModel())->onlyDeleted()->findAll();
        $deleted = 0;
        $skipped = 0;

        foreach ($packages as $package) {
            $result = $this->forceDeletePackage((int) $package['id']);
            $result['success'] ? $deleted++ : $skipped++;
        }

        if ($deleted > 0) {
            log_admin_activity('Paket Umroh', 'Kosongkan Sampah', $deleted . ' paket dihapus permanen; ' . $skipped . ' paket dilewati.');
        }

        return redirect()->to('/admin/packages/trash')->with('success', 'Sampah paket diproses. ' . $deleted . ' dihapus permanen beserta seluruh data uji terkait dan ' . $skipped . ' dilewati.');
    }

    private function forceDeletePackage(int $id): array
    {
        $db = \Config\Database::connect();
        $model = new PackageModel();
        $package = $model->withDeleted()->find($id);

        if (!$package || empty($package['deleted_at'])) {
            return [
                'success' => false,
                'message' => 'Paket harus berada di Sampah sebelum dihapus permanen.',
            ];
        }

        /*
         * Project masih dalam tahap testing. Saat paket dihapus permanen,
         * seluruh data uji yang terkait dengan paket tersebut ikut dibersihkan:
         * lead, aktivitas lead, pendaftaran, pembayaran, peserta, dokumen,
         * jadwal, fasilitas, itinerary, notifikasi, dan file upload terkait.
         */
        $departureIds = [];
        if ($db->tableExists('package_departures') && $db->fieldExists('package_id', 'package_departures')) {
            $departureRows = $db->table('package_departures')
                ->select('id')
                ->where('package_id', $id)
                ->get()
                ->getResultArray();

            $departureIds = array_map('intval', array_column($departureRows, 'id'));
        }

        $registrationIds = [];
        if ($db->tableExists('registrations') && $db->fieldExists('package_id', 'registrations')) {
            $registrationRows = $db->table('registrations')
                ->select('id')
                ->where('package_id', $id)
                ->get()
                ->getResultArray();

            $registrationIds = array_map('intval', array_column($registrationRows, 'id'));
        }

        $leadIds = [];
        if ($db->tableExists('travel_leads')) {
            $leadBuilder = $db->table('travel_leads')->select('id');
            $leadBuilder->groupStart()->where('package_id', $id);

            if ($departureIds !== [] && $db->fieldExists('departure_id', 'travel_leads')) {
                $leadBuilder->orWhereIn('departure_id', $departureIds);
            }

            $leadRows = $leadBuilder
                ->groupEnd()
                ->get()
                ->getResultArray();

            $leadIds = array_map('intval', array_column($leadRows, 'id'));
        }

        $documentIds = [];
        $documentFiles = [];
        if (
            $registrationIds !== []
            && $db->tableExists('pilgrim_documents')
            && $db->fieldExists('registration_id', 'pilgrim_documents')
        ) {
            $documentRows = $db->table('pilgrim_documents')
                ->select('id, document_file')
                ->whereIn('registration_id', $registrationIds)
                ->get()
                ->getResultArray();

            $documentIds = array_map('intval', array_column($documentRows, 'id'));
            $documentFiles = array_values(array_filter(array_column($documentRows, 'document_file')));
        }

        $paymentIds = [];
        if (
            $registrationIds !== []
            && $db->tableExists('payments')
            && $db->fieldExists('registration_id', 'payments')
        ) {
            $paymentRows = $db->table('payments')
                ->select('id')
                ->whereIn('registration_id', $registrationIds)
                ->get()
                ->getResultArray();

            $paymentIds = array_map('intval', array_column($paymentRows, 'id'));
        }

        $db->transBegin();

        try {
            // Putuskan referensi konversi lead lain ke pendaftaran yang akan dihapus.
            if (
                $registrationIds !== []
                && $db->tableExists('travel_leads')
                && $db->fieldExists('converted_registration_id', 'travel_leads')
            ) {
                $db->table('travel_leads')
                    ->whereIn('converted_registration_id', $registrationIds)
                    ->update([
                        'converted_registration_id' => null,
                        'converted_at' => null,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
            }

            // Bersihkan notifikasi yang menunjuk ke data yang akan hilang.
            if ($db->tableExists('notifications')) {
                $db->table('notifications')
                    ->where('type', 'package')
                    ->where('reference_id', $id)
                    ->delete();

                foreach ([
                    'lead' => $leadIds,
                    'registration' => $registrationIds,
                    'payment' => $paymentIds,
                    'document' => $documentIds,
                ] as $type => $referenceIds) {
                    if ($referenceIds !== []) {
                        $db->table('notifications')
                            ->where('type', $type)
                            ->whereIn('reference_id', $referenceIds)
                            ->delete();
                    }
                }
            }

            if ($leadIds !== [] && $db->tableExists('travel_lead_activities')) {
                $db->table('travel_lead_activities')
                    ->whereIn('lead_id', $leadIds)
                    ->delete();
            }

            if ($leadIds !== [] && $db->tableExists('travel_leads')) {
                $db->table('travel_leads')
                    ->whereIn('id', $leadIds)
                    ->delete();
            }

            // Hapus data lama pembayaran/pendaftaran secara eksplisit.
            // Ini tetap aman jika database juga memiliki ON DELETE CASCADE.
            if ($registrationIds !== []) {
                foreach (['pilgrim_documents', 'payments', 'pilgrims'] as $childTable) {
                    if ($db->tableExists($childTable) && $db->fieldExists('registration_id', $childTable)) {
                        $db->table($childTable)
                            ->whereIn('registration_id', $registrationIds)
                            ->delete();
                    }
                }

                if ($db->tableExists('registrations')) {
                    $db->table('registrations')
                        ->whereIn('id', $registrationIds)
                        ->delete();
                }
            }

            // Registrasi harus dihapus sebelum jadwal karena departure_id memakai RESTRICT.
            foreach (['package_departures', 'package_facilities', 'package_itineraries'] as $ownedTable) {
                if ($db->tableExists($ownedTable) && $db->fieldExists('package_id', $ownedTable)) {
                    $db->table($ownedTable)
                        ->where('package_id', $id)
                        ->delete();
                }
            }

            $db->table('packages')
                ->where('id', $id)
                ->delete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi database gagal.');
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Gagal hard delete paket #' . $id . ': ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Paket gagal dihapus permanen. Periksa writable/logs untuk detail error.',
            ];
        }

        // Hapus file setelah transaksi database berhasil agar tidak terjadi record yatim.
        $removedDocumentFiles = 0;
        foreach ($documentFiles as $documentFile) {
            if ($this->removePublicFile($documentFile, ['uploads/documents'])) {
                $removedDocumentFiles++;
            }
        }

        $this->removePublicFile($package['cover_image'] ?? null, ['uploads/packages']);

        $message = sprintf(
            'Paket %s berhasil dihapus permanen beserta %d lead, %d pendaftaran, %d pembayaran, %d dokumen, dan %d file dokumen.',
            $package['name'] ?? '#' . $id,
            count($leadIds),
            count($registrationIds),
            count($paymentIds),
            count($documentIds),
            $removedDocumentFiles
        );

        return [
            'success' => true,
            'message' => $message,
        ];
    }
}

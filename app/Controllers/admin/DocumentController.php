<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Admin\Concerns\BulkActionSupport;
use App\Models\PilgrimDocumentModel;
use App\Models\RegistrationModel;

/**
 * Legacy document cleanup only.
 *
 * Upload, verification, and active document management were removed from the
 * Existing travel sales workflow. This controller is retained solely so a
 * Super Admin can restore or permanently purge historical trash data.
 */
class DocumentController extends BaseController
{
    use BulkActionSupport;

    public function trash()
    {
        if (!$this->isSuperAdmin()) {
            return redirect()
                ->to('/admin/dashboard')
                ->with('error', 'Pembersihan dokumen lama hanya dapat diakses Super Admin.');
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('pilgrim_documents')) {
            return view('admin/documents/trash', [
                'title'     => 'Sampah Dokumen Lama',
                'documents' => [],
            ]);
        }

        $documentModel = new PilgrimDocumentModel();

        $documents = $documentModel
            ->onlyDeleted()
            ->select('pilgrim_documents.*, registrations.registration_no, packages.name AS package_name, users.name AS user_name, pilgrims.full_name AS pilgrim_name, deleted_admin.name AS deleted_by_name')
            ->join('registrations', 'registrations.id = pilgrim_documents.registration_id', 'left')
            ->join('packages', 'packages.id = registrations.package_id', 'left')
            ->join('users', 'users.id = registrations.user_id', 'left')
            ->join('pilgrims', 'pilgrims.id = pilgrim_documents.pilgrim_id', 'left')
            ->join('users AS deleted_admin', 'deleted_admin.id = pilgrim_documents.deleted_by', 'left')
            ->orderBy('pilgrim_documents.deleted_at', 'DESC')
            ->findAll();

        return view('admin/documents/trash', [
            'title'     => 'Sampah Dokumen Lama',
            'documents' => $documents,
        ]);
    }

    public function restore($id)
    {
        if (!$this->isSuperAdmin()) {
            return redirect()
                ->to('/admin/dashboard')
                ->with('error', 'Pembersihan dokumen lama hanya dapat diakses Super Admin.');
        }

        $documentModel = new PilgrimDocumentModel();
        $document = $documentModel->withDeleted()->find($id);

        if (!$document || empty($document['deleted_at'])) {
            return redirect()->back()->with('error', 'Dokumen tidak ditemukan di Sampah.');
        }

        $documentModel->builder()
            ->where('id', $id)
            ->update([
                'deleted_at'    => null,
                'deleted_by'    => null,
                'delete_reason' => null,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);

        $this->syncLegacyRegistrationStatus((int) ($document['registration_id'] ?? 0));

        log_admin_activity(
            'Dokumen Lama',
            'Pulihkan Dokumen',
            'Dokumen lama #' . $id . ' dipulihkan dari Sampah.'
        );

        return redirect()
            ->to('/admin/legacy-documents/trash')
            ->with('success', 'Dokumen lama berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat menghapus dokumen permanen.');
        }

        $documentModel = new PilgrimDocumentModel();
        $document = $documentModel->withDeleted()->find($id);

        if (!$document || empty($document['deleted_at'])) {
            return redirect()->back()->with('error', 'Dokumen harus berada di Sampah sebelum dihapus permanen.');
        }

        $registrationId = (int) ($document['registration_id'] ?? 0);
        $this->removePublicFile($document['document_file'] ?? null, ['uploads/documents']);
        $documentModel->delete($id, true);

        $this->syncLegacyRegistrationStatus($registrationId);

        log_admin_activity(
            'Dokumen Lama',
            'Hapus Permanen',
            'Dokumen lama #' . $id . ' dan file terkait dihapus permanen.'
        );

        return redirect()
            ->to('/admin/legacy-documents/trash')
            ->with('success', 'Dokumen dan file berhasil dihapus permanen.');
    }

    public function trashBulkAction()
    {
        if (!$this->isSuperAdmin()) {
            return redirect()
                ->to('/admin/dashboard')
                ->with('error', 'Pembersihan dokumen lama hanya dapat diakses Super Admin.');
        }

        $ids = $this->selectedIds();
        $action = (string) $this->request->getPost('bulk_action');

        if ($ids === []) {
            return redirect()->back()->with('error', 'Pilih minimal satu dokumen di Sampah.');
        }

        if (!in_array($action, ['restore', 'force_delete'], true)) {
            return redirect()->back()->with('error', 'Aksi massal tidak valid.');
        }

        $documentModel = new PilgrimDocumentModel();
        $processed = 0;
        $registrationIds = [];

        foreach ($ids as $id) {
            $document = $documentModel->withDeleted()->find($id);
            if (!$document || empty($document['deleted_at'])) {
                continue;
            }

            $registrationIds[] = (int) ($document['registration_id'] ?? 0);

            if ($action === 'restore') {
                $documentModel->builder()->where('id', $id)->update([
                    'deleted_at'    => null,
                    'deleted_by'    => null,
                    'delete_reason' => null,
                    'updated_at'    => date('Y-m-d H:i:s'),
                ]);
            } else {
                $this->removePublicFile($document['document_file'] ?? null, ['uploads/documents']);
                $documentModel->delete($id, true);
            }

            $processed++;
        }

        foreach (array_unique(array_filter($registrationIds)) as $registrationId) {
            $this->syncLegacyRegistrationStatus((int) $registrationId);
        }

        $label = $action === 'restore' ? 'dipulihkan' : 'dihapus permanen';

        if ($processed > 0) {
            log_admin_activity(
                'Dokumen Lama',
                'Aksi Massal Sampah',
                $processed . ' dokumen lama ' . $label . '.'
            );
        }

        return redirect()
            ->to('/admin/legacy-documents/trash')
            ->with('success', $processed . ' dokumen berhasil ' . $label . '.');
    }

    public function emptyTrash()
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat mengosongkan Sampah dokumen.');
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('pilgrim_documents')) {
            return redirect()
                ->to('/admin/legacy-documents/trash')
                ->with('success', 'Tidak ada tabel dokumen lama yang perlu dibersihkan.');
        }

        $documentModel = new PilgrimDocumentModel();
        $documents = $documentModel->onlyDeleted()->findAll();
        $deleted = 0;
        $registrationIds = [];

        foreach ($documents as $document) {
            $registrationIds[] = (int) ($document['registration_id'] ?? 0);
            $this->removePublicFile($document['document_file'] ?? null, ['uploads/documents']);
            $documentModel->delete((int) $document['id'], true);
            $deleted++;
        }

        foreach (array_unique(array_filter($registrationIds)) as $registrationId) {
            $this->syncLegacyRegistrationStatus((int) $registrationId);
        }

        if ($deleted > 0) {
            log_admin_activity(
                'Dokumen Lama',
                'Kosongkan Sampah',
                $deleted . ' dokumen lama dan file terkait dihapus permanen.'
            );
        }

        return redirect()
            ->to('/admin/legacy-documents/trash')
            ->with('success', 'Sampah dokumen dikosongkan. ' . $deleted . ' dokumen dihapus permanen.');
    }

    private function syncLegacyRegistrationStatus(int $registrationId): void
    {
        if ($registrationId <= 0) {
            return;
        }

        $db = \Config\Database::connect();
        if (
            !$db->tableExists('registrations')
            || !$db->tableExists('pilgrim_documents')
        ) {
            return;
        }

        $documentModel = new PilgrimDocumentModel();
        $registrationModel = new RegistrationModel();
        $completion = $documentModel->getCompletionByRegistration($registrationId);

        if (!empty($completion['is_complete'])) {
            $registrationStatus = 'Terverifikasi';
        } elseif (($completion['rejected_total'] ?? 0) > 0) {
            $registrationStatus = 'Revisi Dokumen';
        } elseif (($completion['pending_total'] ?? 0) > 0) {
            $registrationStatus = 'Menunggu Verifikasi Dokumen';
        } else {
            $registrationStatus = 'Menunggu Kelengkapan Dokumen';
        }

        $registrationModel->update($registrationId, [
            'registration_status' => $registrationStatus,
        ]);
    }
}

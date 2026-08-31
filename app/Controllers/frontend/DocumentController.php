<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\RegistrationModel;
use App\Models\PilgrimModel;
use App\Models\PilgrimDocumentModel;


class DocumentController extends BaseController
{
    private array $allowedDocumentTypes = [
        'KTP',
        'Kartu Keluarga',
        'Paspor',
        'Buku Vaksin',
        'Pas Foto',
    ];

    public function index($registrationNo)
    {
        if (!session()->get('jamaah_logged_in')) {
            return redirect()
                ->to('/login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $registrationModel = new RegistrationModel();
        $pilgrimModel = new PilgrimModel();
        $documentModel = new PilgrimDocumentModel();

        $registration = $registrationModel->getByRegistrationNo($registrationNo);

        if (!$registration) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Data pendaftaran tidak ditemukan');
        }

        if ((int) $registration['user_id'] !== (int) session()->get('jamaah_user_id')) {
            return redirect()
                ->to('/dashboard-jamaah')
                ->with('error', 'Anda tidak memiliki akses ke dokumen ini.');
        }

        $pilgrims = $pilgrimModel->getByRegistration($registration['id']);
        $documents = $documentModel->getByRegistration($registration['id']);

        return view('frontend/documents/index', [
            'title'         => 'Upload Dokumen Jamaah - ' . site_setting('site_name', 'Travel Umroh & Haji'),
            'registration'  => $registration,
            'pilgrims'      => $pilgrims,
            'documents'     => $documents,
            'documentTypes' => $this->allowedDocumentTypes,
        ]);
    }

    public function upload()
    {
        if (!session()->get('jamaah_logged_in')) {
            return redirect()
                ->to('/login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $rules = [
            'registration_no' => 'required',
            'document_type'   => 'required',
            'document_file'   => 'uploaded[document_file]|max_size[document_file,3072]|mime_in[document_file,image/jpg,image/jpeg,image/png,image/webp,application/pdf]',
        ];

        $messages = [
            'document_file' => [
                'uploaded' => 'File dokumen wajib diupload.',
                'max_size' => 'Ukuran file maksimal 3MB.',
                'mime_in'  => 'Format file harus JPG, JPEG, PNG, WEBP, atau PDF.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $registrationModel = new RegistrationModel();
        $pilgrimModel = new PilgrimModel();
        $documentModel = new PilgrimDocumentModel();

        $registrationNo = $this->request->getPost('registration_no');
        $documentType = $this->request->getPost('document_type');

        if (!in_array($documentType, $this->allowedDocumentTypes, true)) {
            return redirect()
                ->back()
                ->with('error', 'Jenis dokumen tidak valid.');
        }

        $registration = $registrationModel->getByRegistrationNo($registrationNo);

        if (!$registration) {
            return redirect()
                ->back()
                ->with('error', 'Data pendaftaran tidak ditemukan.');
        }

        if ((int) $registration['user_id'] !== (int) session()->get('jamaah_user_id')) {
            return redirect()
                ->to('/dashboard-jamaah')
                ->with('error', 'Anda tidak memiliki akses ke dokumen ini.');
        }

        $pilgrims = $pilgrimModel->getByRegistration($registration['id']);
        $leader = $pilgrims[0] ?? null;

        $file = $this->request->getFile('document_file');

        $uploadPath = FCPATH . 'uploads/documents';

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        $newName = $file->getRandomName();
        $file->move($uploadPath, $newName);

        $existingDocument = $documentModel->getByRegistrationAndType($registration['id'], $documentType);

        if ($existingDocument) {
            if (!empty($existingDocument['document_file']) && file_exists(FCPATH . $existingDocument['document_file'])) {
                unlink(FCPATH . $existingDocument['document_file']);
            }

            $documentModel->update($existingDocument['id'], [
                'pilgrim_id'     => $leader['id'] ?? null,
                'document_file'  => 'uploads/documents/' . $newName,
                'status'         => 'Menunggu Verifikasi',
                'note'           => null,
                'verified_at'    => null,
            ]);

            $documentId = $existingDocument['id'];
        } else {
            $documentId = $documentModel->insert([
                'registration_id' => $registration['id'],
                'pilgrim_id'      => $leader['id'] ?? null,
                'document_type'   => $documentType,
                'document_file'   => 'uploads/documents/' . $newName,
                'status'          => 'Menunggu Verifikasi',
            ], true);
        }

        if (function_exists('create_notification')) {
            create_notification(
                'Dokumen Baru Diupload',
                'Jamaah mengupload dokumen ' . $documentType . ' untuk nomor pendaftaran ' . $registrationNo . '.',
                'document',
                (int) $documentId
            );
        }
        return redirect()
            ->to('/dokumen/' . $registrationNo)
            ->with('success', 'Dokumen berhasil diupload dan menunggu verifikasi admin.');
    }

    public function delete($id)
    {
        if (!session()->get('jamaah_logged_in')) {
            return redirect()
                ->to('/login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $documentModel = new PilgrimDocumentModel();
        $registrationModel = new RegistrationModel();

        $document = $documentModel->find($id);

        if (!$document) {
            return redirect()
                ->to('/dashboard-jamaah')
                ->with('error', 'Dokumen tidak ditemukan.');
        }

        $registration = $registrationModel->find($document['registration_id']);

        if (!$registration || (int) $registration['user_id'] !== (int) session()->get('jamaah_user_id')) {
            return redirect()
                ->to('/dashboard-jamaah')
                ->with('error', 'Anda tidak memiliki akses untuk menghapus dokumen ini.');
        }

        if (!empty($document['document_file']) && file_exists(FCPATH . $document['document_file'])) {
            unlink(FCPATH . $document['document_file']);
        }

        $documentModel->delete($id);
        $this->syncRegistrationStatusByDocuments($registration['id']);
        return redirect()
            ->to('/dokumen/' . $registration['registration_no'])
            ->with('success', 'Dokumen berhasil dihapus.');
    }

    private function syncRegistrationStatusByDocuments($registrationId)
    {
        $documentModel = new PilgrimDocumentModel();
        $registrationModel = new RegistrationModel();

        $completion = $documentModel->getCompletionByRegistration($registrationId);

        if ($completion['is_complete']) {
            $registrationStatus = 'Terverifikasi';
        } elseif ($completion['rejected_total'] > 0) {
            $registrationStatus = 'Revisi Dokumen';
        } elseif ($completion['pending_total'] > 0) {
            $registrationStatus = 'Menunggu Verifikasi Dokumen';
        } else {
            $registrationStatus = 'Menunggu Kelengkapan Dokumen';
        }

        $registrationModel->update($registrationId, [
            'registration_status' => $registrationStatus,
        ]);
    }
}

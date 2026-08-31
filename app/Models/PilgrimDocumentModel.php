<?php

namespace App\Models;

use CodeIgniter\Model;

class PilgrimDocumentModel extends Model
{
    protected $table          = 'pilgrim_documents';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $allowedFields = [
        'registration_id',
        'pilgrim_id',
        'document_type',
        'document_file',
        'status',
        'note',
        'verified_at',
        'deleted_by',
        'delete_reason',
    ];

    public function getByRegistration($registrationId)
    {
        return $this->where('registration_id', $registrationId)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    public function getByRegistrationAndType($registrationId, $documentType)
    {
        return $this->where('registration_id', $registrationId)
            ->where('document_type', $documentType)
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function requiredTypes(): array
    {
        return [
            'KTP',
            'Kartu Keluarga',
            'Paspor',
            'Buku Vaksin',
            'Pas Foto',
        ];
    }

    public function getCompletionByRegistration($registrationId): array
    {
        $requiredTypes = $this->requiredTypes();

        $documents = $this->where('registration_id', $registrationId)
            ->orderBy('id', 'DESC')
            ->findAll();

        $documentMap = [];

        foreach ($documents as $document) {
            $type = $document['document_type'];

            if (!isset($documentMap[$type])) {
                $documentMap[$type] = $document;
            }
        }

        $items = [];
        $uploaded = 0;
        $accepted = 0;
        $pending = 0;
        $rejected = 0;
        $missing = 0;

        foreach ($requiredTypes as $type) {
            $document = $documentMap[$type] ?? null;

            if (!$document) {
                $missing++;
                $items[] = [
                    'type'     => $type,
                    'status'   => 'Belum Upload',
                    'document' => null,
                ];
                continue;
            }

            $uploaded++;

            if ($document['status'] === 'Diterima') {
                $accepted++;
            } elseif ($document['status'] === 'Ditolak') {
                $rejected++;
            } else {
                $pending++;
            }

            $items[] = [
                'type'     => $type,
                'status'   => $document['status'],
                'document' => $document,
            ];
        }

        return [
            'required_total' => count($requiredTypes),
            'uploaded_total' => $uploaded,
            'accepted_total' => $accepted,
            'pending_total'  => $pending,
            'rejected_total' => $rejected,
            'missing_total'  => $missing,
            'is_complete'    => $accepted === count($requiredTypes),
            'items'          => $items,
        ];
    }
}

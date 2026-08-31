<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class JamaahController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        $jamaah = $db->table('users')
            ->select("
                users.*,
                COUNT(DISTINCT registrations.id) AS total_registrations,
                COUNT(DISTINCT pilgrim_documents.id) AS total_documents
            ", false)
            ->join('registrations', 'registrations.user_id = users.id AND registrations.deleted_at IS NULL', 'left')
            ->join('pilgrim_documents', 'pilgrim_documents.registration_id = registrations.id', 'left')
            ->where('users.role', 'jamaah')
            ->where('users.deleted_at', null)
            ->groupBy('users.id')
            ->orderBy('users.id', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/jamaah/index', [
            'title'  => 'Data Jamaah',
            'jamaah' => $jamaah,
        ]);
    }

    public function detail($id)
    {
        $db = \Config\Database::connect();

        $jamaah = $db->table('users')
            ->where('id', $id)
            ->where('role', 'jamaah')
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$jamaah) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Data jamaah tidak ditemukan');
        }

        $registrations = $db->table('registrations')
            ->select("
                registrations.*,
                packages.name AS package_name,
                packages.slug AS package_slug,
                package_departures.departure_date,
                package_departures.return_date
            ", false)
            ->join('packages', 'packages.id = registrations.package_id', 'left')
            ->join('package_departures', 'package_departures.id = registrations.departure_id', 'left')
            ->where('registrations.user_id', $id)
            ->where('registrations.deleted_at', null)
            ->orderBy('registrations.id', 'DESC')
            ->get()
            ->getResultArray();

        $payments = $db->table('payments')
            ->select("
                payments.*,
                registrations.registration_no,
                registrations.payment_status,
                packages.name AS package_name
            ", false)
            ->join('registrations', 'registrations.id = payments.registration_id', 'left')
            ->join('packages', 'packages.id = registrations.package_id', 'left')
            ->where('registrations.user_id', $id)
            ->orderBy('payments.id', 'DESC')
            ->get()
            ->getResultArray();

        $documents = $db->table('pilgrim_documents')
            ->select("
                pilgrim_documents.*,
                registrations.registration_no,
                packages.name AS package_name,
                pilgrims.full_name AS pilgrim_name
            ", false)
            ->join('registrations', 'registrations.id = pilgrim_documents.registration_id', 'left')
            ->join('packages', 'packages.id = registrations.package_id', 'left')
            ->join('pilgrims', 'pilgrims.id = pilgrim_documents.pilgrim_id', 'left')
            ->where('registrations.user_id', $id)
            ->orderBy('pilgrim_documents.id', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/jamaah/detail', [
            'title'         => 'Detail Jamaah',
            'jamaah'        => $jamaah,
            'registrations' => $registrations,
            'payments'      => $payments,
            'documents'     => $documents,
        ]);
    }

    public function updateStatus($id)
    {
        $db = \Config\Database::connect();

        $jamaah = $db->table('users')
            ->where('id', $id)
            ->where('role', 'jamaah')
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$jamaah) {
            return redirect()
                ->to('/admin/jamaah')
                ->with('error', 'Data jamaah tidak ditemukan.');
        }

        $status = $this->request->getPost('status');

        $allowedStatus = [
            'active',
            'inactive',
            'blocked',
        ];

        if (!in_array($status, $allowedStatus, true)) {
            return redirect()
                ->back()
                ->with('error', 'Status akun tidak valid.');
        }

        $db->table('users')
            ->where('id', $id)
            ->update([
                'status'     => $status,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return redirect()
            ->back()
            ->with('success', 'Status akun jamaah berhasil diperbarui.');
    }
}
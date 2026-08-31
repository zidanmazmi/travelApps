<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Admin\Concerns\BulkActionSupport;

class AdminUserController extends BaseController
{
    use BulkActionSupport;
    private function getCurrentAdmin()
    {
        $db = \Config\Database::connect();

        $adminId = session()->get('admin_user_id')
            ?? session()->get('user_id')
            ?? session()->get('id');

        if (!$adminId) {
            return null;
        }

        return $db->table('users')
            ->where('id', $adminId)
            ->whereIn('role', ['super_admin', 'admin'])
            ->get()
            ->getRowArray();
    }

    private function onlySuperAdmin()
    {
        if (session()->get('admin_role') !== 'super_admin') {
            return redirect()
                ->to('/admin/dashboard')
                ->with('error', 'Akses ditolak. Fitur ini hanya untuk Super Admin.');
        }

        return null;
    }

    public function index()
    {
        if ($redirect = $this->onlySuperAdmin()) {
            return $redirect;
        }

        $db = \Config\Database::connect();

        $admins = $db->table('users')
            ->whereIn('role', ['super_admin', 'admin'])
            ->where('deleted_at', null)
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/admin_users/index', [
            'title'  => 'Manajemen Admin',
            'admins' => $admins,
        ]);
    }

    public function create()
    {
        if ($redirect = $this->onlySuperAdmin()) {
            return $redirect;
        }

        return view('admin/admin_users/create', [
            'title' => 'Tambah Admin',
        ]);
    }

    public function store()
    {
        if ($redirect = $this->onlySuperAdmin()) {
            return $redirect;
        }

        $rules = [
            'name'     => 'required|min_length[3]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'phone'    => 'permit_empty|min_length[10]',
            'role'     => 'required|in_list[super_admin,admin]',
            'status'   => 'required|in_list[active,inactive,blocked]',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();

        $db->table('users')->insert([
            'name'              => $this->request->getPost('name'),
            'email'             => $this->request->getPost('email'),
            'phone'             => $this->request->getPost('phone'),
            'nik'               => null,
            'password'          => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'              => $this->request->getPost('role'),
            'status'            => $this->request->getPost('status'),
            'is_email_verified' => 1,
            'email_verified_at' => date('Y-m-d H:i:s'),
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);
        log_admin_activity(
            'Manajemen Admin',
            'Tambah Admin',
            'Super Admin menambahkan akun admin: ' . $this->request->getPost('email') . '.'
        );
        return redirect()
            ->to('/admin/admin-users')
            ->with('success', 'Akun admin berhasil ditambahkan.');
    }

    public function edit($id)
    {
        if ($redirect = $this->onlySuperAdmin()) {
            return $redirect;
        }

        $db = \Config\Database::connect();

        $admin = $db->table('users')
            ->where('id', $id)
            ->whereIn('role', ['super_admin', 'admin'])
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$admin) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Data admin tidak ditemukan');
        }

        return view('admin/admin_users/edit', [
            'title' => 'Edit Admin',
            'admin' => $admin,
        ]);
    }

    public function update($id)
{
    if ($redirect = $this->onlySuperAdmin()) {
        return $redirect;
    }

    $db = \Config\Database::connect();

    $admin = $db->table('users')
        ->where('id', $id)
        ->whereIn('role', ['super_admin', 'admin'])
        ->where('deleted_at', null)
        ->get()
        ->getRowArray();

    if (!$admin) {
        return redirect()
            ->to('/admin/admin-users')
            ->with('error', 'Data admin tidak ditemukan.');
    }

    $isCurrentAdmin = (int) $id === (int) session()->get('admin_user_id');

    $rules = [
        'name'  => 'required|min_length[3]',
        'email' => 'required|valid_email',
        'phone' => 'permit_empty|min_length[10]',
    ];

    if (!$isCurrentAdmin) {
        $rules['role']   = 'required|in_list[super_admin,admin]';
        $rules['status'] = 'required|in_list[active,inactive,blocked]';
    }

    if ($this->request->getPost('email') !== $admin['email']) {
        $rules['email'] = 'required|valid_email|is_unique[users.email]';
    }

    if (!$this->validate($rules)) {
        return redirect()
            ->back()
            ->withInput()
            ->with('errors', $this->validator->getErrors());
    }

    /*
     * Kalau super admin sedang edit akun dirinya sendiri,
     * role dan status tidak boleh berubah.
     */
    if ($isCurrentAdmin) {
        $role   = $admin['role'];
        $status = $admin['status'];
    } else {
        $role   = $this->request->getPost('role');
        $status = $this->request->getPost('status');
    }

    $db->table('users')
        ->where('id', $id)
        ->update([
            'name'       => $this->request->getPost('name'),
            'email'      => $this->request->getPost('email'),
            'phone'      => $this->request->getPost('phone'),
            'role'       => $role,
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

    /*
     * Kalau yang diedit adalah akun sendiri,
     * update juga data session supaya nama/email di dashboard ikut berubah.
     */
    if ($isCurrentAdmin) {
        session()->set([
            'admin_name'  => $this->request->getPost('name'),
            'admin_email' => $this->request->getPost('email'),
            'admin_role'  => $role,
        ]);
    }

    log_admin_activity(
        'Manajemen Admin',
        'Update Admin',
        'Super Admin memperbarui akun admin ID #' . $id . '.'
    );

    return redirect()
        ->to('/admin/admin-users/edit/' . $id)
        ->with('success', 'Data admin berhasil diperbarui.');
}

    public function updatePassword($id)
    {
        if ($redirect = $this->onlySuperAdmin()) {
            return $redirect;
        }

        $rules = [
            'password'              => 'required|min_length[6]',
            'password_confirmation' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();

        $admin = $db->table('users')
            ->where('id', $id)
            ->whereIn('role', ['super_admin', 'admin'])
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()
                ->to('/admin/admin-users')
                ->with('error', 'Data admin tidak ditemukan.');
        }

        $db->table('users')
            ->where('id', $id)
            ->update([
                'password'   => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        log_admin_activity(
            'Manajemen Admin',
            'Reset Password',
            'Super Admin mereset password admin ID #' . $id . '.'
        );
        return redirect()
            ->back()
            ->with('success', 'Password admin berhasil diperbarui.');
    }

    public function updateStatus($id)
    {
        if ((int) $id === (int) session()->get('admin_user_id')) {
            return redirect()
                ->back()
                ->with('error', 'Anda tidak dapat mengubah status akun Anda sendiri.');
        }
        if ($redirect = $this->onlySuperAdmin()) {
            return $redirect;
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

        $db = \Config\Database::connect();

        $admin = $db->table('users')
            ->where('id', $id)
            ->whereIn('role', ['super_admin', 'admin'])
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()
                ->to('/admin/admin-users')
                ->with('error', 'Data admin tidak ditemukan.');
        }

        $db->table('users')
            ->where('id', $id)
            ->update([
                'status'     => $status,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return redirect()
            ->back()
            ->with('success', 'Status admin berhasil diperbarui.');
    }


    public function delete($id)
    {
        if ($redirect = $this->onlySuperAdmin()) {
            return $redirect;
        }

        $db = \Config\Database::connect();
        $currentId = (int) session()->get('admin_user_id');

        if ((int) $id === $currentId) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $admin = $db->table('users')
            ->where('id', $id)
            ->whereIn('role', ['super_admin', 'admin'])
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()->back()->with('error', 'Data admin tidak ditemukan.');
        }

        if ($admin['role'] === 'super_admin' && $admin['status'] === 'active') {
            $activeSuperAdmins = $db->table('users')
                ->where('role', 'super_admin')
                ->where('status', 'active')
                ->where('deleted_at', null)
                ->countAllResults();

            if ($activeSuperAdmins <= 1) {
                return redirect()->back()->with('error', 'Super Admin terakhir tidak dapat dihapus.');
            }
        }

        $reason = trim((string) $this->request->getPost('delete_reason')) ?: 'Dipindahkan ke Sampah oleh Super Admin.';

        $db->table('users')
            ->where('id', $id)
            ->update([
                'status'        => 'inactive',
                'deleted_at'    => date('Y-m-d H:i:s'),
                'deleted_by'    => $currentId,
                'delete_reason' => $reason,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);

        log_admin_activity(
            'Manajemen Admin',
            'Pindah ke Sampah',
            'Akun admin ' . ($admin['email'] ?? '#' . $id) . ' dipindahkan ke Sampah.'
        );

        return redirect()->to('/admin/admin-users')->with('success', 'Akun admin berhasil dipindahkan ke Sampah.');
    }


    public function bulkDelete()
    {
        if ($redirect = $this->onlySuperAdmin()) {
            return $redirect;
        }

        $ids = $this->selectedIds();
        if ($ids === []) {
            return redirect()->back()->with('error', 'Pilih minimal satu akun admin.');
        }

        $db = \Config\Database::connect();
        $currentId = (int) session()->get('admin_user_id');
        $activeSuperAdmins = $db->table('users')
            ->where('role', 'super_admin')
            ->where('status', 'active')
            ->where('deleted_at', null)
            ->countAllResults();

        $deleted = 0;
        $skipped = 0;

        foreach ($ids as $id) {
            if ($id === $currentId) {
                $skipped++;
                continue;
            }

            $admin = $db->table('users')
                ->where('id', $id)
                ->whereIn('role', ['super_admin', 'admin'])
                ->where('deleted_at', null)
                ->get()
                ->getRowArray();

            if (!$admin) {
                $skipped++;
                continue;
            }

            if ($admin['role'] === 'super_admin' && $admin['status'] === 'active') {
                if ($activeSuperAdmins <= 1) {
                    $skipped++;
                    continue;
                }
                $activeSuperAdmins--;
            }

            $db->table('users')->where('id', $id)->update([
                'status' => 'inactive',
                'deleted_at' => date('Y-m-d H:i:s'),
                'deleted_by' => $currentId,
                'delete_reason' => 'Penghapusan massal akun admin.',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $deleted++;
        }

        if ($deleted > 0) {
            log_admin_activity('Manajemen Admin', 'Pindah Massal ke Sampah', $deleted . ' akun admin dipindahkan ke Sampah.');
        }

        $message = $deleted . ' akun berhasil dipindahkan ke Sampah.';
        if ($skipped > 0) {
            $message .= ' ' . $skipped . ' akun dilewati karena proteksi sistem.';
        }

        return redirect()->to('/admin/admin-users')->with('success', $message);
    }

    public function trash()
    {
        if ($redirect = $this->onlySuperAdmin()) {
            return $redirect;
        }

        $db = \Config\Database::connect();

        $admins = $db->table('users')
            ->select('users.*, deleted_admin.name AS deleted_by_name')
            ->join('users AS deleted_admin', 'deleted_admin.id = users.deleted_by', 'left')
            ->whereIn('users.role', ['super_admin', 'admin'])
            ->where('users.deleted_at IS NOT NULL', null, false)
            ->orderBy('users.deleted_at', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/admin_users/trash', [
            'title'  => 'Sampah Admin',
            'admins' => $admins,
        ]);
    }

    public function restore($id)
    {
        if ($redirect = $this->onlySuperAdmin()) {
            return $redirect;
        }

        $db = \Config\Database::connect();

        $admin = $db->table('users')
            ->where('id', $id)
            ->whereIn('role', ['super_admin', 'admin'])
            ->where('deleted_at IS NOT NULL', null, false)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()->back()->with('error', 'Akun admin tidak ditemukan di Sampah.');
        }

        $db->table('users')
            ->where('id', $id)
            ->update([
                'status'        => 'inactive',
                'deleted_at'    => null,
                'deleted_by'    => null,
                'delete_reason' => null,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);

        log_admin_activity('Manajemen Admin', 'Pulihkan Admin', 'Akun admin ' . ($admin['email'] ?? '#' . $id) . ' dipulihkan dari Sampah.');

        return redirect()->to('/admin/admin-users/trash')->with('success', 'Akun admin berhasil dipulihkan sebagai nonaktif.');
    }

    public function forceDelete($id)
    {
        if ($redirect = $this->onlySuperAdmin()) {
            return $redirect;
        }

        $db = \Config\Database::connect();
        $currentId = (int) session()->get('admin_user_id');

        if ((int) $id === $currentId) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $admin = $db->table('users')
            ->where('id', $id)
            ->whereIn('role', ['super_admin', 'admin'])
            ->where('deleted_at IS NOT NULL', null, false)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()->back()->with('error', 'Akun harus berada di Sampah sebelum dihapus permanen.');
        }

        if ($admin['role'] === 'super_admin') {
            $activeSuperAdmins = $db->table('users')
                ->where('role', 'super_admin')
                ->where('status', 'active')
                ->where('deleted_at', null)
                ->countAllResults();

            if ($activeSuperAdmins < 1) {
                return redirect()->back()->with('error', 'Sistem harus memiliki minimal satu Super Admin aktif.');
            }
        }

        // Histori activity log tetap aman karena menyimpan nama/email admin secara snapshot.
        $this->detachAdminReferences((int) $id);
        $db->table('users')->where('id', $id)->delete();

        log_admin_activity('Manajemen Admin', 'Hapus Permanen', 'Akun admin ' . ($admin['email'] ?? '#' . $id) . ' dihapus permanen.');

        return redirect()->to('/admin/admin-users/trash')->with('success', 'Akun admin berhasil dihapus permanen.');
    }


    public function trashBulkAction()
    {
        if ($redirect = $this->onlySuperAdmin()) {
            return $redirect;
        }

        $ids = $this->selectedIds();
        $action = (string) $this->request->getPost('bulk_action');

        if ($ids === []) {
            return redirect()->back()->with('error', 'Pilih minimal satu akun di Sampah.');
        }

        if (!in_array($action, ['restore', 'force_delete'], true)) {
            return redirect()->back()->with('error', 'Aksi massal tidak valid.');
        }

        $db = \Config\Database::connect();
        $processed = 0;

        foreach ($ids as $id) {
            $admin = $db->table('users')
                ->where('id', $id)
                ->whereIn('role', ['super_admin', 'admin'])
                ->where('deleted_at IS NOT NULL', null, false)
                ->get()
                ->getRowArray();

            if (!$admin) {
                continue;
            }

            if ($action === 'restore') {
                $db->table('users')->where('id', $id)->update([
                    'status' => 'inactive',
                    'deleted_at' => null,
                    'deleted_by' => null,
                    'delete_reason' => null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $this->detachAdminReferences($id);
                $db->table('users')->where('id', $id)->delete();
            }

            $processed++;
        }

        $label = $action === 'restore' ? 'dipulihkan sebagai nonaktif' : 'dihapus permanen';
        log_admin_activity('Manajemen Admin', 'Aksi Massal Sampah', $processed . ' akun admin ' . $label . '.');

        return redirect()->to('/admin/admin-users/trash')->with('success', $processed . ' akun berhasil ' . $label . '.');
    }

    public function emptyTrash()
    {
        if ($redirect = $this->onlySuperAdmin()) {
            return $redirect;
        }

        $db = \Config\Database::connect();
        $admins = $db->table('users')
            ->whereIn('role', ['super_admin', 'admin'])
            ->where('deleted_at IS NOT NULL', null, false)
            ->get()
            ->getResultArray();

        $deleted = 0;
        foreach ($admins as $admin) {
            $id = (int) $admin['id'];
            $this->detachAdminReferences($id);
            $db->table('users')->where('id', $id)->delete();
            $deleted++;
        }

        if ($deleted > 0) {
            log_admin_activity('Manajemen Admin', 'Kosongkan Sampah', $deleted . ' akun admin dihapus permanen.');
        }

        return redirect()->to('/admin/admin-users/trash')->with('success', 'Sampah admin dikosongkan. ' . $deleted . ' akun dihapus permanen.');
    }

    private function detachAdminReferences(int $adminId): void
    {
        $db = \Config\Database::connect();
        $references = [
            ['table' => 'travel_leads', 'field' => 'assigned_admin_id'],
            ['table' => 'travel_leads', 'field' => 'deleted_by'],
            ['table' => 'travel_lead_activities', 'field' => 'admin_id'],
            ['table' => 'admin_activity_logs', 'field' => 'admin_id'],
            ['table' => 'pilgrim_documents', 'field' => 'deleted_by'],
            ['table' => 'users', 'field' => 'deleted_by'],
        ];

        foreach ($references as $reference) {
            if ($db->tableExists($reference['table']) && $db->fieldExists($reference['field'], $reference['table'])) {
                $db->table($reference['table'])
                    ->where($reference['field'], $adminId)
                    ->update([$reference['field'] => null]);
            }
        }
    }

    private function ensureSuperAdmin()
    {
        if (session()->get('admin_role') !== 'super_admin') {
            return redirect()
                ->to('/admin/dashboard')
                ->with('error', 'Anda tidak memiliki akses ke halaman Admin Users.');
        }

        return null;
    }
}

<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class RecycleBinController extends BaseController
{
    public function index()
    {
        if (session()->get('admin_role') !== 'super_admin') {
            return redirect()->to('/admin/dashboard')->with('error', 'Hanya Super Admin yang dapat membuka pusat Sampah Data.');
        }

        $db = \Config\Database::connect();

        $items = [
            ['label' => 'Leads', 'table' => 'travel_leads', 'icon' => 'bi-person-lines-fill', 'url' => '/admin/leads/trash', 'description' => 'Prospek calon jamaah yang dipindahkan ke Sampah.'],
            ['label' => 'Paket', 'table' => 'packages', 'icon' => 'bi-box-seam', 'url' => '/admin/packages/trash', 'description' => 'Paket umroh yang sudah tidak aktif atau terhapus.'],
            ['label' => 'Galeri', 'table' => 'galleries', 'icon' => 'bi-images', 'url' => '/admin/galleries/trash', 'description' => 'Foto dan video galeri yang menunggu dipulihkan atau dihapus permanen.'],
            ['label' => 'Testimoni', 'table' => 'testimonials', 'icon' => 'bi-chat-quote', 'url' => '/admin/testimonials/trash', 'description' => 'Konten testimoni yang sudah dipindahkan ke Sampah.'],
            ['label' => 'FAQ', 'table' => 'faqs', 'icon' => 'bi-question-circle', 'url' => '/admin/faqs/trash', 'description' => 'Pertanyaan dan jawaban yang sudah dihapus sementara.'],
            ['label' => 'Akun Admin', 'table' => 'users', 'icon' => 'bi-person-gear', 'url' => '/admin/admin-users/trash', 'description' => 'Akun admin nonaktif yang masih dapat dipulihkan.', 'roles' => ['admin', 'super_admin']],
            ['label' => 'Dokumen Lama', 'table' => 'pilgrim_documents', 'icon' => 'bi-archive', 'url' => '/admin/legacy-documents/trash', 'description' => 'Pembersihan dokumen lama dari alur pendaftaran sebelumnya.'],
        ];

        foreach ($items as &$item) {
            $item['count'] = 0;
            if (!$db->tableExists($item['table']) || !$db->fieldExists('deleted_at', $item['table'])) {
                continue;
            }

            $builder = $db->table($item['table'])
                ->where($item['table'] . '.deleted_at IS NOT NULL', null, false);

            if (!empty($item['roles'])) {
                $builder->whereIn('role', $item['roles']);
            }

            $item['count'] = $builder->countAllResults();
        }
        unset($item);

        return view('admin/recycle_bin/index', [
            'title' => 'Sampah Data',
            'items' => $items,
        ]);
    }
}

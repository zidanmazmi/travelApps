<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Admin\Concerns\BulkActionSupport;
use App\Models\FaqModel;

class FaqController extends BaseController
{
    use BulkActionSupport;
    protected FaqModel $faqModel;

    public function __construct()
    {
        $this->faqModel = new FaqModel();
    }

    public function index()
    {
        $faqs = $this->faqModel
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('admin/faqs/index', [
            'title' => 'Manajemen FAQ',
            'faqs'  => $faqs,
        ]);
    }

    public function create()
    {
        return view('admin/faqs/create', [
            'title' => 'Tambah FAQ',
        ]);
    }

    public function store()
    {
        $rules = [
            'question'   => 'required|min_length[5]',
            'answer'     => 'required|min_length[10]',
            'sort_order' => 'permit_empty|integer',
            'status'     => 'required|in_list[active,inactive]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->faqModel->insert([
            'question'   => $this->request->getPost('question'),
            'answer'     => $this->request->getPost('answer'),
            'sort_order' => $this->request->getPost('sort_order') ?: 0,
            'status'     => $this->request->getPost('status'),
        ]);

        if (function_exists('log_admin_activity')) {
            log_admin_activity(
                'FAQ Website',
                'Tambah FAQ',
                'Admin menambahkan FAQ: ' . $this->request->getPost('question')
            );
        }

        return redirect()
            ->to('/admin/faqs')
            ->with('success', 'FAQ berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $faq = $this->faqModel->find($id);

        if (!$faq) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('FAQ tidak ditemukan');
        }

        return view('admin/faqs/edit', [
            'title' => 'Edit FAQ',
            'faq'   => $faq,
        ]);
    }

    public function update($id)
    {
        $faq = $this->faqModel->find($id);

        if (!$faq) {
            return redirect()
                ->to('/admin/faqs')
                ->with('error', 'FAQ tidak ditemukan.');
        }

        $rules = [
            'question'   => 'required|min_length[5]',
            'answer'     => 'required|min_length[10]',
            'sort_order' => 'permit_empty|integer',
            'status'     => 'required|in_list[active,inactive]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->faqModel->update($id, [
            'question'   => $this->request->getPost('question'),
            'answer'     => $this->request->getPost('answer'),
            'sort_order' => $this->request->getPost('sort_order') ?: 0,
            'status'     => $this->request->getPost('status'),
        ]);

        if (function_exists('log_admin_activity')) {
            log_admin_activity(
                'FAQ Website',
                'Update FAQ',
                'Admin memperbarui FAQ ID #' . $id
            );
        }

        return redirect()
            ->to('/admin/faqs')
            ->with('success', 'FAQ berhasil diperbarui.');
    }

    public function delete($id)
    {
        $item = $this->faqModel->find($id);
        if (!$item) {
            return redirect()->to('/admin/faqs')->with('error', 'FAQ tidak ditemukan.');
        }

        $this->faqModel->delete($id);
        log_admin_activity('FAQ Website', 'Pindah ke Sampah', 'FAQ #' . $id . ' dipindahkan ke Sampah.');
        return redirect()->to('/admin/faqs')->with('success', 'FAQ berhasil dipindahkan ke Sampah.');
    }

    public function bulkDelete()
    {
        $ids = $this->selectedIds();
        if ($ids === []) {
            return redirect()->back()->with('error', 'Pilih minimal satu data.');
        }

        $deleted = 0;
        foreach ($ids as $id) {
            if (!$this->faqModel->find($id)) {
                continue;
            }
            $this->faqModel->delete($id);
            $deleted++;
        }

        if ($deleted > 0) {
            log_admin_activity('FAQ Website', 'Pindah Massal ke Sampah', $deleted . ' data dipindahkan ke Sampah.');
        }
        return redirect()->to('/admin/faqs')->with('success', $deleted . ' data berhasil dipindahkan ke Sampah.');
    }

    public function trash()
    {
        return view('admin/faqs/trash', [
            'title' => 'Sampah FAQ',
            'items' => $this->faqModel->onlyDeleted()->orderBy('deleted_at', 'DESC')->findAll(),
        ]);
    }

    public function restore($id)
    {
        $item = $this->faqModel->withDeleted()->find($id);
        if (!$item || empty($item['deleted_at'])) {
            return redirect()->back()->with('error', 'FAQ tidak ditemukan di Sampah.');
        }

        $this->faqModel->builder()->where('id', $id)->update(['deleted_at' => null, 'updated_at' => date('Y-m-d H:i:s')]);
        log_admin_activity('FAQ Website', 'Pulihkan Data', 'FAQ #' . $id . ' dipulihkan.');
        return redirect()->to('/admin/faqs/trash')->with('success', 'FAQ berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat menghapus data permanen.');
        }

        $item = $this->faqModel->withDeleted()->find($id);
        if (!$item || empty($item['deleted_at'])) {
            return redirect()->back()->with('error', 'FAQ harus berada di Sampah.');
        }

        $this->faqModel->delete($id, true);
        log_admin_activity('FAQ Website', 'Hapus Permanen', 'FAQ #' . $id . ' dihapus permanen.');
        return redirect()->to('/admin/faqs/trash')->with('success', 'FAQ berhasil dihapus permanen.');
    }

    public function trashBulkAction()
    {
        $ids = $this->selectedIds();
        $action = (string) $this->request->getPost('bulk_action');

        if ($ids === []) {
            return redirect()->back()->with('error', 'Pilih minimal satu data di Sampah.');
        }
        if (!in_array($action, ['restore', 'force_delete'], true)) {
            return redirect()->back()->with('error', 'Aksi massal tidak valid.');
        }
        if ($action === 'force_delete' && !$this->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat menghapus data permanen.');
        }

        $processed = 0;
        foreach ($ids as $id) {
            $item = $this->faqModel->withDeleted()->find($id);
            if (!$item || empty($item['deleted_at'])) {
                continue;
            }

            if ($action === 'restore') {
                $this->faqModel->builder()->where('id', $id)->update(['deleted_at' => null, 'updated_at' => date('Y-m-d H:i:s')]);
            } else {
                $this->faqModel->delete($id, true);
            }
            $processed++;
        }

        $label = $action === 'restore' ? 'dipulihkan' : 'dihapus permanen';
        log_admin_activity('FAQ Website', 'Aksi Massal Sampah', $processed . ' data ' . $label . '.');
        return redirect()->to('/admin/faqs/trash')->with('success', $processed . ' data berhasil ' . $label . '.');
    }

    public function emptyTrash()
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat mengosongkan Sampah.');
        }

        $rows = $this->faqModel->onlyDeleted()->findAll();
        $deleted = 0;
        foreach ($rows as $row) {
            $this->faqModel->delete((int) $row['id'], true);
            $deleted++;
        }

        if ($deleted > 0) {
            log_admin_activity('FAQ Website', 'Kosongkan Sampah', $deleted . ' data dihapus permanen.');
        }
        return redirect()->to('/admin/faqs/trash')->with('success', 'Sampah dikosongkan. ' . $deleted . ' data dihapus permanen.');
    }
}

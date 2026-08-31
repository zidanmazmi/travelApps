<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Admin\Concerns\BulkActionSupport;
use App\Models\TestimonialModel;

class TestimonialController extends BaseController
{
    use BulkActionSupport;
    protected TestimonialModel $testimonialModel;

    public function __construct()
    {
        $this->testimonialModel = new TestimonialModel();
    }

    public function index()
    {
        $testimonials = $this->testimonialModel
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('admin/testimonials/index', [
            'title'        => 'Manajemen Testimoni',
            'testimonials' => $testimonials,
        ]);
    }

    public function create()
    {
        return view('admin/testimonials/create', [
            'title' => 'Tambah Testimoni',
        ]);
    }

    public function store()
    {
        $rules = [
            'name'       => 'required|min_length[3]',
            'label'      => 'permit_empty|max_length[150]',
            'message'    => 'required|min_length[10]',
            'rating'     => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[5]',
            'sort_order' => 'permit_empty|integer',
            'status'     => 'required|in_list[active,inactive]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->testimonialModel->insert([
            'name'       => $this->request->getPost('name'),
            'label'      => $this->request->getPost('label'),
            'message'    => $this->request->getPost('message'),
            'rating'     => $this->request->getPost('rating') ?: 5,
            'sort_order' => $this->request->getPost('sort_order') ?: 0,
            'status'     => $this->request->getPost('status'),
        ]);

        log_admin_activity(
            'Testimoni Website',
            'Tambah Testimoni',
            'Admin menambahkan testimoni dari: ' . $this->request->getPost('name')
        );

        return redirect()
            ->to('/admin/testimonials')
            ->with('success', 'Testimoni berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $testimonial = $this->testimonialModel->find($id);

        if (!$testimonial) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Data testimoni tidak ditemukan');
        }

        return view('admin/testimonials/edit', [
            'title'       => 'Edit Testimoni',
            'testimonial' => $testimonial,
        ]);
    }

    public function update($id)
    {
        $testimonial = $this->testimonialModel->find($id);

        if (!$testimonial) {
            return redirect()
                ->to('/admin/testimonials')
                ->with('error', 'Data testimoni tidak ditemukan.');
        }

        $rules = [
            'name'       => 'required|min_length[3]',
            'label'      => 'permit_empty|max_length[150]',
            'message'    => 'required|min_length[10]',
            'rating'     => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[5]',
            'sort_order' => 'permit_empty|integer',
            'status'     => 'required|in_list[active,inactive]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->testimonialModel->update($id, [
            'name'       => $this->request->getPost('name'),
            'label'      => $this->request->getPost('label'),
            'message'    => $this->request->getPost('message'),
            'rating'     => $this->request->getPost('rating') ?: 5,
            'sort_order' => $this->request->getPost('sort_order') ?: 0,
            'status'     => $this->request->getPost('status'),
        ]);

        log_admin_activity(
            'Testimoni Website',
            'Update Testimoni',
            'Admin memperbarui testimoni ID #' . $id
        );

        return redirect()
            ->to('/admin/testimonials')
            ->with('success', 'Testimoni berhasil diperbarui.');
    }

    public function delete($id)
    {
        $item = $this->testimonialModel->find($id);
        if (!$item) {
            return redirect()->to('/admin/testimonials')->with('error', 'Testimoni tidak ditemukan.');
        }

        $this->testimonialModel->delete($id);
        log_admin_activity('Testimoni Website', 'Pindah ke Sampah', 'Testimoni #' . $id . ' dipindahkan ke Sampah.');
        return redirect()->to('/admin/testimonials')->with('success', 'Testimoni berhasil dipindahkan ke Sampah.');
    }

    public function bulkDelete()
    {
        $ids = $this->selectedIds();
        if ($ids === []) {
            return redirect()->back()->with('error', 'Pilih minimal satu data.');
        }

        $deleted = 0;
        foreach ($ids as $id) {
            if (!$this->testimonialModel->find($id)) {
                continue;
            }
            $this->testimonialModel->delete($id);
            $deleted++;
        }

        if ($deleted > 0) {
            log_admin_activity('Testimoni Website', 'Pindah Massal ke Sampah', $deleted . ' data dipindahkan ke Sampah.');
        }
        return redirect()->to('/admin/testimonials')->with('success', $deleted . ' data berhasil dipindahkan ke Sampah.');
    }

    public function trash()
    {
        return view('admin/testimonials/trash', [
            'title' => 'Sampah Testimoni',
            'items' => $this->testimonialModel->onlyDeleted()->orderBy('deleted_at', 'DESC')->findAll(),
        ]);
    }

    public function restore($id)
    {
        $item = $this->testimonialModel->withDeleted()->find($id);
        if (!$item || empty($item['deleted_at'])) {
            return redirect()->back()->with('error', 'Testimoni tidak ditemukan di Sampah.');
        }

        $this->testimonialModel->builder()->where('id', $id)->update(['deleted_at' => null, 'updated_at' => date('Y-m-d H:i:s')]);
        log_admin_activity('Testimoni Website', 'Pulihkan Data', 'Testimoni #' . $id . ' dipulihkan.');
        return redirect()->to('/admin/testimonials/trash')->with('success', 'Testimoni berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat menghapus data permanen.');
        }

        $item = $this->testimonialModel->withDeleted()->find($id);
        if (!$item || empty($item['deleted_at'])) {
            return redirect()->back()->with('error', 'Testimoni harus berada di Sampah.');
        }

        $this->testimonialModel->delete($id, true);
        log_admin_activity('Testimoni Website', 'Hapus Permanen', 'Testimoni #' . $id . ' dihapus permanen.');
        return redirect()->to('/admin/testimonials/trash')->with('success', 'Testimoni berhasil dihapus permanen.');
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
            $item = $this->testimonialModel->withDeleted()->find($id);
            if (!$item || empty($item['deleted_at'])) {
                continue;
            }

            if ($action === 'restore') {
                $this->testimonialModel->builder()->where('id', $id)->update(['deleted_at' => null, 'updated_at' => date('Y-m-d H:i:s')]);
            } else {
                $this->testimonialModel->delete($id, true);
            }
            $processed++;
        }

        $label = $action === 'restore' ? 'dipulihkan' : 'dihapus permanen';
        log_admin_activity('Testimoni Website', 'Aksi Massal Sampah', $processed . ' data ' . $label . '.');
        return redirect()->to('/admin/testimonials/trash')->with('success', $processed . ' data berhasil ' . $label . '.');
    }

    public function emptyTrash()
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat mengosongkan Sampah.');
        }

        $rows = $this->testimonialModel->onlyDeleted()->findAll();
        $deleted = 0;
        foreach ($rows as $row) {
            $this->testimonialModel->delete((int) $row['id'], true);
            $deleted++;
        }

        if ($deleted > 0) {
            log_admin_activity('Testimoni Website', 'Kosongkan Sampah', $deleted . ' data dihapus permanen.');
        }
        return redirect()->to('/admin/testimonials/trash')->with('success', 'Sampah dikosongkan. ' . $deleted . ' data dihapus permanen.');
    }
}

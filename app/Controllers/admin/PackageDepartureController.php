<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Admin\Concerns\BulkActionSupport;
use App\Models\PackageModel;
use App\Models\PackageDepartureModel;

class PackageDepartureController extends BaseController
{
    use BulkActionSupport;
    public function index($packageId)
    {
        $packageModel = new PackageModel();
        $departureModel = new PackageDepartureModel();

        $package = $packageModel->find($packageId);

        if (!$package) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Paket tidak ditemukan');
        }

        $departures = $departureModel->getByPackage($packageId);

        return view('admin/package_departures/index', [
            'title'      => 'Jadwal Keberangkatan',
            'package'    => $package,
            'departures' => $departures,
        ]);
    }

    public function create($packageId)
    {
        $packageModel = new PackageModel();

        $package = $packageModel->find($packageId);

        if (!$package) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Paket tidak ditemukan');
        }

        return view('admin/package_departures/create', [
            'title'   => 'Tambah Jadwal Keberangkatan',
            'package' => $package,
        ]);
    }

    public function store()
    {
        $rules = [
            'package_id'     => 'required|numeric',
            'departure_date' => 'required|valid_date',
            'return_date'    => 'permit_empty|valid_date',
            'quota'          => 'required|numeric',
            'booked'         => 'required|numeric',
            'status'         => 'required|in_list[available,full,closed]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $departureModel = new PackageDepartureModel();

        $packageId = $this->request->getPost('package_id');

        $departureModel->insert([
            'package_id'      => $packageId,
            'departure_date'  => $this->request->getPost('departure_date'),
            'return_date'     => $this->request->getPost('return_date') ?: null,
            'quota'           => $this->request->getPost('quota'),
            'booked'          => $this->request->getPost('booked'),
            'status'          => $this->request->getPost('status'),
        ]);

        return redirect()
            ->to('/admin/packages/departures/' . $packageId)
            ->with('success', 'Jadwal keberangkatan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $departureModel = new PackageDepartureModel();
        $packageModel = new PackageModel();

        $departure = $departureModel->find($id);

        if (!$departure) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Jadwal keberangkatan tidak ditemukan');
        }

        $package = $packageModel->find($departure['package_id']);

        if (!$package) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Paket tidak ditemukan');
        }

        return view('admin/package_departures/edit', [
            'title'     => 'Edit Jadwal Keberangkatan',
            'package'   => $package,
            'departure' => $departure,
        ]);
    }

    public function update($id)
    {
        $departureModel = new PackageDepartureModel();

        $departure = $departureModel->find($id);

        if (!$departure) {
            return redirect()
                ->to('/admin/packages')
                ->with('error', 'Jadwal keberangkatan tidak ditemukan.');
        }

        $rules = [
            'departure_date' => 'required|valid_date',
            'return_date'    => 'permit_empty|valid_date',
            'quota'          => 'required|numeric',
            'booked'         => 'required|numeric',
            'status'         => 'required|in_list[available,full,closed]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $departureModel->update($id, [
            'departure_date' => $this->request->getPost('departure_date'),
            'return_date'    => $this->request->getPost('return_date') ?: null,
            'quota'          => $this->request->getPost('quota'),
            'booked'         => $this->request->getPost('booked'),
            'status'         => $this->request->getPost('status'),
        ]);

        return redirect()
            ->to('/admin/packages/departures/' . $departure['package_id'])
            ->with('success', 'Jadwal keberangkatan berhasil diperbarui.');
    }

    public function delete($id)
    {
        $departureModel = new PackageDepartureModel();
        $departure = $departureModel->find($id);

        if (!$departure) {
            return redirect()
                ->to('/admin/packages')
                ->with('error', 'Jadwal keberangkatan tidak ditemukan.');
        }

        if ($this->hasHistoricalRelation((int) $id)) {
            return redirect()
                ->back()
                ->with('error', 'Jadwal tidak dapat dihapus karena sudah terhubung dengan lead atau data pendaftaran. Ubah status menjadi closed.');
        }

        $packageId = (int) $departure['package_id'];
        $departureModel->delete($id, true);

        log_admin_activity(
            'Jadwal Keberangkatan',
            'Hapus Jadwal',
            'Jadwal #' . $id . ' dihapus dari paket #' . $packageId . '.'
        );

        return redirect()
            ->to('/admin/packages/departures/' . $packageId)
            ->with('success', 'Jadwal keberangkatan berhasil dihapus.');
    }

    public function bulkDelete()
    {
        $ids = $this->selectedIds();
        $packageId = (int) $this->request->getPost('package_id');

        if ($ids === []) {
            return redirect()->back()->with('error', 'Pilih minimal satu jadwal keberangkatan.');
        }

        $departureModel = new PackageDepartureModel();
        $deleted = 0;
        $skipped = 0;

        foreach ($ids as $id) {
            $departure = $departureModel->find($id);

            if (
                !$departure
                || ($packageId > 0 && (int) $departure['package_id'] !== $packageId)
                || $this->hasHistoricalRelation((int) $id)
            ) {
                $skipped++;
                continue;
            }

            $departureModel->delete($id, true);
            $deleted++;
        }

        if ($deleted > 0) {
            log_admin_activity(
                'Jadwal Keberangkatan',
                'Hapus Massal',
                $deleted . ' jadwal dihapus; ' . $skipped . ' jadwal dilewati.'
            );
        }

        $message = $deleted . ' jadwal berhasil dihapus.';
        if ($skipped > 0) {
            $message .= ' ' . $skipped . ' jadwal dilewati karena terhubung dengan lead/pendaftaran atau tidak valid.';
        }

        return redirect()
            ->to('/admin/packages/departures/' . $packageId)
            ->with('success', $message);
    }

    private function hasHistoricalRelation(int $departureId): bool
    {
        $db = \Config\Database::connect();

        foreach ([
            ['table' => 'travel_leads', 'field' => 'departure_id'],
            ['table' => 'registrations', 'field' => 'departure_id'],
        ] as $relation) {
            if (
                $db->tableExists($relation['table'])
                && $db->fieldExists($relation['field'], $relation['table'])
                && $db->table($relation['table'])
                    ->where($relation['field'], $departureId)
                    ->countAllResults() > 0
            ) {
                return true;
            }
        }

        return false;
    }
}

<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\PackageDepartureModel;
use App\Models\PackageModel;

class PackageController extends BaseController
{
    public function index()
    {
        $packageModel = new PackageModel();

        return view('frontend/packages/index', [
            'title'    => 'Paket Umrah & Haji - ' . site_setting('site_name', 'Travel Umroh & Haji'),
            'packages' => $packageModel->getActivePackages(),
        ]);
    }

    public function detail($slug)
    {
        $packageModel   = new PackageModel();
        $departureModel = new PackageDepartureModel();

        $package = $packageModel->getPackageBySlug($slug);

        if (!$package) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Paket tidak ditemukan');
        }

        $package['slug'] = $slug;

        return view('frontend/packages/detail', [
            'title'           => ($package['name'] ?? 'Detail Paket') . ' - ' . site_setting('site_name', 'Travel Umroh & Haji'),
            'metaDescription' => mb_strimwidth(strip_tags((string) ($package['description'] ?? '')), 0, 155, '...'),
            'package'         => $package,
            'departures'      => $departureModel->getAvailableByPackage((int) $package['id']),
        ]);
    }
}

<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\GalleryModel;
use App\Models\TestimonialModel;
use App\Models\FaqModel;

class Home extends BaseController
{
    public function index()
    {
        $data = [
            'title'            => site_setting('site_name', 'Travel Umroh & Haji') . ' - Umroh dan Haji Terpercaya',
            'featuredPackages' => $this->getFeaturedPackages(),
            'testimonials'     => $this->getTestimonials(),
            'galleries'        => $this->getGalleries(),
            'faqs' => $this->getFaqs(),
        ];

        return view('frontend/home/index', $data);
    }

    private function getFeaturedPackages(): array
    {
        $db = \Config\Database::connect();

        $databasePackages = $db->table('packages')
            ->where('status', 'active')
            ->where('deleted_at', null)
            ->orderBy('id', 'DESC')
            ->limit(3)
            ->get()
            ->getResultArray();

        if (!empty($databasePackages)) {
            $packages = [];

            foreach ($databasePackages as $package) {
                $departure = $db->table('package_departures')
                    ->where('package_id', $package['id'])
                    ->where('status', 'available')
                    ->where('departure_date >=', date('Y-m-d'))
                    ->orderBy('departure_date', 'ASC')
                    ->get()
                    ->getRowArray();

                if (!$departure) {
                    $departure = $db->table('package_departures')
                        ->where('package_id', $package['id'])
                        ->where('status', 'available')
                        ->orderBy('departure_date', 'ASC')
                        ->get()
                        ->getRowArray();
                }

                $durationDays   = (int) ($package['duration_days'] ?? 0);
                $durationNights = (int) ($package['duration_nights'] ?? 0);

                $durationLabel = $durationDays > 0
                    ? $durationDays . ' Hari' . ($durationNights > 0 ? ' / ' . $durationNights . ' Malam' : '')
                    : 'Menyesuaikan program';

                $departureLabel = !empty($departure['departure_date'])
                    ? $this->formatIndonesianDate($departure['departure_date'])
                    : 'Konsultasikan jadwal';

                $quotaLabel = 'Kuota dikonfirmasi admin';

                if ($departure) {
                    $remainingQuota = max(
                        0,
                        (int) ($departure['quota'] ?? 0) - (int) ($departure['booked'] ?? 0)
                    );

                    $quotaLabel = $remainingQuota > 0
                        ? 'Sisa ' . $remainingQuota . ' kursi'
                        : 'Hubungi admin';
                }

                $packages[] = [
                    'name'           => $package['name'] ?? 'Paket Umroh',
                    'slug'           => $package['slug'] ?? '',
                    'badge'          => $package['badge'] ?? 'Paket Pilihan',
                    'program'        => $package['program'] ?? '',
                    'image'          => $this->resolvePackageImage($package['cover_image'] ?? null),
                    'priceLabel'     => $this->formatRupiah($package['price'] ?? 0),
                    'durationLabel'  => $durationLabel,
                    'departureLabel' => $departureLabel,
                    'quotaLabel'     => $quotaLabel,
                ];
            }

            return $packages;
        }

        return [
            [
                'name'           => 'Lorem Ipsum',
                'slug'           => '',
                'badge'          => 'Lorem',
                'program'        => 'lorem-ipsum',
                'image'          => '',
                'priceLabel'     => 'Lorem Ipsum',
                'durationLabel'  => 'Lorem ipsum dolor',
                'departureLabel' => 'Dolor sit amet',
                'quotaLabel'     => 'Consectetur',
            ],
            [
                'name'           => 'Dolor Sit Amet',
                'slug'           => '',
                'badge'          => 'Ipsum',
                'program'        => 'dolor-sit',
                'image'          => '',
                'priceLabel'     => 'Lorem Ipsum',
                'durationLabel'  => 'Lorem ipsum dolor',
                'departureLabel' => 'Dolor sit amet',
                'quotaLabel'     => 'Consectetur',
            ],
            [
                'name'           => 'Consectetur Adipiscing',
                'slug'           => '',
                'badge'          => 'Dolor',
                'program'        => 'consectetur',
                'image'          => '',
                'priceLabel'     => 'Lorem Ipsum',
                'durationLabel'  => 'Lorem ipsum dolor',
                'departureLabel' => 'Dolor sit amet',
                'quotaLabel'     => 'Consectetur',
            ],
        ];
    }

    private function formatIndonesianDate(string $date): string
    {
        $timestamp = strtotime($date);

        if (!$timestamp) {
            return 'Konsultasikan jadwal';
        }

        $months = [
            1  => 'Jan',
            2  => 'Feb',
            3  => 'Mar',
            4  => 'Apr',
            5  => 'Mei',
            6  => 'Jun',
            7  => 'Jul',
            8  => 'Agu',
            9  => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];

        return date('d', $timestamp)
            . ' '
            . $months[(int) date('n', $timestamp)]
            . ' '
            . date('Y', $timestamp);
    }

    private function getTestimonials(): array
    {
        $testimonialModel = new TestimonialModel();

        $databaseTestimonials = $testimonialModel
            ->where('status', 'active')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'DESC')
            ->findAll(6);

        if (!empty($databaseTestimonials)) {
            $testimonials = [];

            foreach ($databaseTestimonials as $testimonial) {
                $testimonials[] = [
                    'name'    => $testimonial['name'] ?? 'Jamaah',
                    'label'   => $testimonial['label'] ?? 'Jamaah',
                    'message' => $testimonial['message'] ?? '',
                    'rating'  => $testimonial['rating'] ?? 5,
                ];
            }

            return $testimonials;
        }

        return [
            [
                'name'    => 'H. Ahmad Suryadi',
                'label'   => 'Jamaah Umrah 2023',
                'message' => 'Alhamdulillah perjalanan ibadah sangat nyaman, rapi, dan pembimbingnya sabar.',
                'rating'  => 5,
            ],
            [
                'name'    => 'Hj. Siti Aminah',
                'label'   => 'Jamaah Umrah Plus 2024',
                'message' => 'Pelayanan dari awal pendaftaran sampai pulang sangat tertata dan jelas.',
                'rating'  => 5,
            ],
            [
                'name'    => 'Dr. Ridwan',
                'label'   => 'Jamaah VIP 2023',
                'message' => 'Travel yang amanah. Program jelas, jadwal rapi, dan pelayanan sangat membantu.',
                'rating'  => 5,
            ],
        ];
    }

    private function getGalleries(): array
    {
        $galleryModel = new GalleryModel();

        $databaseGalleries = $galleryModel
            ->where('status', 'active')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'DESC')
            ->findAll(24);

        if (!empty($databaseGalleries)) {
            $galleries = [];

            foreach ($databaseGalleries as $gallery) {
                $mediaType = strtolower((string) ($gallery['media_type'] ?? 'image')) === 'video'
                    ? 'video'
                    : 'image';

                $imagePath = '';
                $videoPath = '';
                $thumbnailPath = '';

                if ($mediaType === 'video' && !empty($gallery['video_file'])) {
                    $videoPath = 'uploads/gallery/videos/' . $gallery['video_file'];
                }

                if ($mediaType === 'image' && !empty($gallery['image'])) {
                    $newImagePath = FCPATH . 'uploads/gallery/images/' . $gallery['image'];
                    $imagePath = is_file($newImagePath)
                        ? 'uploads/gallery/images/' . $gallery['image']
                        : 'uploads/gallery/' . $gallery['image'];
                }

                if (!empty($gallery['thumbnail'])) {
                    $thumbnailPath = 'uploads/gallery/thumbnails/' . $gallery['thumbnail'];
                }

                $galleries[] = [
                    'media_type'      => $mediaType,
                    'image'           => $imagePath,
                    'video'           => $videoPath,
                    'thumbnail'       => $thumbnailPath,
                    'mime_type'       => $gallery['mime_type'] ?? ($mediaType === 'video' ? 'video/mp4' : null),
                    'aspect_ratio'    => $gallery['aspect_ratio'] ?? ($mediaType === 'video' ? 'portrait' : 'landscape'),
                    'duration_seconds'=> (int) ($gallery['duration_seconds'] ?? 0),
                    'alt'             => $gallery['title'] ?? 'Galeri perjalanan ibadah',
                    'title'           => $gallery['title'] ?? '',
                    'description'     => $gallery['description'] ?? '',
                ];
            }

            return $galleries;
        }

        return [
            [
                'media_type' => 'image',
                'image'       => 'assets/frontend/images/gallery-1.jpg',
                'video'       => '',
                'thumbnail'   => '',
                'alt'         => 'Galeri perjalanan ibadah 1',
                'title'       => 'Perjalanan Umrah Jamaah',
                'description' => 'Dokumentasi perjalanan ibadah bersama jamaah.',
            ],
            [
                'media_type' => 'image',
                'image'       => 'assets/frontend/images/gallery-2.jpg',
                'video'       => '',
                'thumbnail'   => '',
                'alt'         => 'Galeri perjalanan ibadah 2',
                'title'       => 'Bimbingan Ibadah',
                'description' => 'Pendampingan jamaah selama proses ibadah.',
            ],
            [
                'media_type' => 'image',
                'image'       => 'assets/frontend/images/gallery-3.jpg',
                'video'       => '',
                'thumbnail'   => '',
                'alt'         => 'Galeri perjalanan ibadah 3',
                'title'       => 'Kebersamaan Jamaah',
                'description' => 'Momen kebersamaan jamaah dalam perjalanan.',
            ],
            [
                'media_type' => 'image',
                'image'       => 'assets/frontend/images/gallery-4.jpg',
                'video'       => '',
                'thumbnail'   => '',
                'alt'         => 'Galeri perjalanan ibadah 4',
                'title'       => 'Akomodasi Nyaman',
                'description' => 'Fasilitas perjalanan yang mendukung kenyamanan jamaah.',
            ],
            [
                'media_type' => 'image',
                'image'       => 'assets/frontend/images/gallery-5.jpg',
                'video'       => '',
                'thumbnail'   => '',
                'alt'         => 'Galeri perjalanan ibadah 5',
                'title'       => 'Ziarah dan Kunjungan',
                'description' => 'Agenda ziarah dan kunjungan selama perjalanan.',
            ],
            [
                'media_type' => 'image',
                'image'       => 'assets/frontend/images/gallery-6.jpg',
                'video'       => '',
                'thumbnail'   => '',
                'alt'         => 'Galeri perjalanan ibadah 6',
                'title'       => 'Pelayanan Jamaah',
                'description' => 'Pelayanan sepenuh hati untuk perjalanan ibadah.',
            ],
        ];
    }

    private function getFaqs(): array
    {
        $faqModel = new FaqModel();

        $databaseFaqs = $faqModel
            ->where('status', 'active')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'DESC')
            ->findAll(8);

        if (!empty($databaseFaqs)) {
            return $databaseFaqs;
        }

        return [
            [
                'question' => 'Apakah bisa konsultasi sebelum mendaftar?',
                'answer'   => 'Bisa. Calon jamaah dapat berkonsultasi terlebih dahulu melalui WhatsApp untuk menanyakan paket, jadwal, dokumen, dan alur pendaftaran.',
            ],
            [
                'question' => 'Bagaimana cara mendaftar paket umrah atau haji?',
                'answer'   => 'Calon jamaah dapat memilih paket yang tersedia, membuat akun, melakukan verifikasi email, mengisi formulir pendaftaran, lalu melanjutkan proses pembayaran dan upload dokumen.',
            ],
            [
                'question' => 'Apakah status pendaftaran bisa dicek online?',
                'answer'   => 'Bisa. Jamaah dapat menggunakan fitur cek status untuk melihat informasi pendaftaran, pembayaran, dan dokumen.',
            ],
        ];
    }
    private function resolvePackageImage(?string $coverImage): string
    {
        if (empty($coverImage)) {
            return 'assets/frontend/images/paket-1.jpg';
        }

        if (str_contains($coverImage, 'assets/') || str_contains($coverImage, 'uploads/')) {
            return $coverImage;
        }

        return 'uploads/packages/' . $coverImage;
    }

    private function formatRupiah($amount): string
    {
        $amount = (float) $amount;

        if ($amount <= 0) {
            return 'Hubungi Admin';
        }

        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

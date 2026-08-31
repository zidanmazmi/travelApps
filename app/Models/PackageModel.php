<?php

namespace App\Models;

use CodeIgniter\Model;

class PackageModel extends Model
{
    protected $table            = 'packages';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;

    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    protected $allowedFields    = [
        'name',
        'slug',
        'badge',
        'program',
        'description',
        'airline',
        'hotel_makkah',
        'hotel_madinah',
        'facilities_text',
        'price',
        'duration_days',
        'duration_nights',
        'cover_image',
        'status',
    ];

    public static function getProgramOptions(): array
    {
        $default = "reguler:Reguler\nplus:Plus\nramadhan:Ramadhan\nhaji-khusus:Haji Khusus";
        $raw = site_setting('package_program_options', $default);
        $lines = preg_split('/[\r\n,;]+/u', $raw) ?: [];
        $options = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            [$key, $label] = array_pad(explode(':', $line, 2), 2, '');
            $key = mb_strtolower(trim($key));
            $key = preg_replace('/[^a-z0-9-]+/', '-', $key);
            $key = trim((string) $key, '-');
            $label = trim($label);

            if ($key === '' || mb_strlen($key) > 30) {
                continue;
            }

            $options[$key] = $label !== ''
                ? $label
                : ucwords(str_replace('-', ' ', $key));
        }

        return $options !== [] ? $options : [
            'reguler' => 'Reguler',
            'plus' => 'Plus',
        ];
    }

    public static function getProgramLabel(?string $program): string
    {
        $key = mb_strtolower(trim((string) $program));
        if ($key === '') {
            return 'Belum dipilih';
        }

        $options = self::getProgramOptions();

        return $options[$key] ?? ucwords(str_replace('-', ' ', $key));
    }

    public function getActivePackages()
    {
        return $this->where('status', 'active')
            ->where('deleted_at', null)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    public function getPackageBySlug($slug)
    {
        return $this->where('slug', $slug)
            ->where('status', 'active')
            ->first();
    }
}

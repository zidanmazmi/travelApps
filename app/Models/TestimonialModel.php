<?php

namespace App\Models;

use CodeIgniter\Model;

class TestimonialModel extends Model
{
    protected $table      = 'testimonials';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;

    protected $allowedFields = [
        'name',
        'label',
        'message',
        'rating',
        'sort_order',
        'status',
    ];
}
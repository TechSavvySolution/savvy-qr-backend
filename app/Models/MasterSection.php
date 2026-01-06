<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSection extends Model
{
    use HasFactory;
    protected $guarded = [];

    // AUTOMATIC JSON CONVERSION
    protected $casts = [
        'fields_schema' => 'array',
        'default_styles' => 'array'
    ];

    public function template()
    {
        return $this->belongsTo(MasterTemplate::class, 'master_template_id');
    }
}
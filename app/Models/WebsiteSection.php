<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSection extends Model
{
    // 🟢 1. This prevents "Mass Assignment" errors
    protected $fillable = ['website_id', 'section_id', 'values', 'style'];

    // 🟢 2. This handles the JSON data automatically
    protected $casts = [
        'values' => 'array',
        'style'  => 'array',
    ];
}
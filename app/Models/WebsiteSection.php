<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteSection extends Model
{
    use HasFactory;

    protected $fillable = ['website_id', 'section_type', 'order_index', 'content', 'styles'];

    protected $casts = [
        'content' => 'array', 
        'styles' => 'array',
    ];
    
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterTemplate extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    // This converts 1/0 to true/false automatically!
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationship: One Template has many Sections
    public function sections()
    {
        return $this->hasMany(MasterSection::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteSection extends Model
{
    use HasFactory;

    protected $fillable = ['website_id', 'section_id', 'values', 'style'];

    // 🟢 Relationship to the Master Rule
    public function section()
    {
        // This links 'section_id' in this table to 'id' in 'master_sections' table
        return $this->belongsTo(MasterSection::class, 'section_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// class Website extends Model
// {
//     use HasFactory;

//     protected $fillable = ['user_id', 'template_name', 'title', 'slug', 'is_published'];

//     // Link to Sections
//     public function sections()
//     {
//         return $this->hasMany(WebsiteSection::class)->orderBy('order_index');
//     }
// }


class Website extends Model
{
    use HasFactory;

    // 🟢 ADD THIS
    protected $fillable = [
        'user_id', 
        'template_id', 
        'title', 
        'url_slug',
        'active'
    ];

    public function sections()
    {
        return $this->hasMany(WebsiteSection::class);
    }
}
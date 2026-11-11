<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'page_id', 'product', 'name', 'phone', 'email',
        'source', 'medium', 'campaign', 'referrer', 'landing_page',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}

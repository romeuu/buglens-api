<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    protected $fillable = ['name', 'slug', 'current_version', 'latest_version'];

    public function scans()
    {
        return $this->hasMany(Scan::class);
    }
}
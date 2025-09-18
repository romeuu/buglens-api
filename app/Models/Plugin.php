<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Plugin extends Model
{
    protected $fillable = ['name', 'slug', 'current_version', 'latest_version'];

    public function scans()
    {
        return $this->hasMany(Scan::class);
    }

    public function vulnerabilities(): HasManyThrough
    {
        return $this->hasManyThrough(
            Vulnerability::class,
            Scan::class,
            'plugin_id',
            'scan_id',
            'id',       
            'id'         
        );
    }
}
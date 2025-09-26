<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Plugin extends Model
{
    protected $fillable = ['name', 'slug', 'current_version', 'latest_version', 'last_analyzed_at', 'last_analyzed_version'];

    protected $casts = [
        'last_analyzed_at' => 'datetime'
    ];
    
    public function needsAnalysis(): bool
    {
        return $this->last_analyzed_version !== $this->current_version 
            || $this->last_analyzed_at === null;
    }

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
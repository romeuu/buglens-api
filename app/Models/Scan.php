<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scan extends Model
{
    protected $fillable = ['plugin_id', 'scanner', 'result'];

    protected $casts = [
        'result' => 'array',
    ];

    public function plugin()
    {
        return $this->belongsTo(Plugin::class);
    }

    public function vulnerabilities()
    {
        return $this->hasMany(Vulnerability::class);
    }
}

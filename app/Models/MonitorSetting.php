<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitorSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'monitor_id',
        'required'
    ];

    protected $casts = [
        'required' => 'bool'
    ];


    public function monitor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Monitor::class);
    }
}

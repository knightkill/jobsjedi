<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Monitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'board_id',
        'filter',
        'active',
        'name',
        'slug',
        'description',
    ];

    protected $casts = [
        'filter' => 'json',
        'active' => 'bool'
    ];

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function board(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function monitorSettings(): HasMany
    {
        return $this->hasMany(MonitorSetting::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}

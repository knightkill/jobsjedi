<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Board extends Model
{

    use HasFactory;

    protected $guarded = [];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function monitors(): HasMany
    {
        return $this->hasMany(Monitor::class);
    }

    public function boardSettings(): HasMany
    {
        return $this->hasMany(BoardSetting::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'description',
        'required',
        'board_id'
    ];

    protected $casts = [
        'required' => 'bool'
    ];

    /**
     * @return BelongsTo
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function getBoardOrMonitorSetting(string $key, Monitor $monitor) : BoardSetting|MonitorSetting
    {
        $setting = $monitor->monitorSettings()->where('key', $key)->first();
        if($setting) {
            return $setting;
        } else {
            return $this->where('key', $key)->first();
        }
    }

    public static function getAllBoardOrMonitorSettings(Monitor $monitor) : array
    {
        $settings = $monitor->board->boardSettings()->get();
        $monitorSettings = $monitor->monitorSettings()->get();
        $allSettings = [];
        foreach($settings as $setting) {
            $allSettings[$setting->key] = $setting;
        }
        foreach($monitorSettings as $setting) {
            $allSettings[$setting->key] = $setting;
        }
        return $allSettings;
    }

}

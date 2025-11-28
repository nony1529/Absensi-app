<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'date', 'time_in', 'time_out', 'photo_in', 'photo_out',
        'lat_long_in', 'lat_long_out', 'status_masuk', 'status_pulang'
    ];

    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime:H:i:s',
        'time_out' => 'datetime:H:i:s',
    ];

    // Accessor untuk time_in dengan timezone lokal
    public function getTimeInLocalAttribute()
    {
        return $this->time_in ? Carbon::parse($this->time_in)->setTimezone('Asia/Jakarta')->format('H:i:s') : null;
    }

    // Accessor untuk time_out dengan timezone lokal
    public function getTimeOutLocalAttribute()
    {
        return $this->time_out ? Carbon::parse($this->time_out)->setTimezone('Asia/Jakarta')->format('H:i:s') : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
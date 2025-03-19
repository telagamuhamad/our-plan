<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meeting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'travelling_user_id',
        'meeting_date',
        'location',
        'is_departure_transport_ready',
        'is_return_transport_ready',
        'is_rest_place_ready',
        'note',
        'start_date',
        'end_date'
    ];

    protected $appends = [
        'formatted_start_date',
        'formatted_end_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'travelling_user_id');
    }

    public function travels()
    {
        return $this->hasMany(Travel::class, 'meeting_id');
    }

    public function getFormattedStartDateAttribute()
    {
        return Carbon::parse($this->start_date)->format('j F Y');
    }

    public function getFormattedEndDateAttribute()
    {
        return Carbon::parse($this->end_date)->format('j F Y');
    }
}

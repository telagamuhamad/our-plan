<?php

namespace App\Models;

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
        'note'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'travelling_user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelPhoto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'travel_photos';

    protected $fillable = [
        'travel_id',
        'uploaded_by',
        'photo_path',
        'caption',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Get the travel that owns the photo.
     */
    public function travel()
    {
        return $this->belongsTo(Travel::class);
    }

    /**
     * Get the user who uploaded the photo.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the URL for the photo.
     */
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->photo_path);
    }

    /**
     * Scope to get photos by travel
     */
    public function scopeByTravel($query, $travelId)
    {
        return $query->where('travel_id', $travelId);
    }

    /**
     * Scope to order by custom order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at');
    }
}

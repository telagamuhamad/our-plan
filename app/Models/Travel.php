<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Travel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'travels';

    protected $fillable = [
        'meeting_id',
        'destination',
        'visit_date',
        'completed',
    ];

    protected $appends =[
        'formatted_visit_date'
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function getFormattedVisitDateAttribute()
    {
        return Carbon::parse($this->visit_date)->format('j F Y');
    }
}

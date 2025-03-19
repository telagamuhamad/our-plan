<?php

namespace App\Models;

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

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }
}

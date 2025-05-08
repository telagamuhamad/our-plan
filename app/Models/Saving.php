<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Saving extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'savings';

    protected $fillable = [
        'user_id',
        'name',
        'target_amount',
        'current_amount',
        'is_shared',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(SavingTransaction::class);
    }

    public function getProgressAttribute()
    {
        if ($this->target_amount == 0) {
            return 0;
        }
        return $this->current_amount / $this->target_amount * 100;
    }
}

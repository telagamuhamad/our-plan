<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavingTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'saving_transactions';

    protected $fillable = [
        'saving_id',
        'type',
        'amount',
        'note',
        'actor_user_id',
    ];

    public function savingData()
    {
        return $this->belongsTo(Saving::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}

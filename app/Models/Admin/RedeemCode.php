<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class RedeemCode extends Model
{
    public $fillable = [
        'code',
        'user_id',
        'is_active',
        'is_taken',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

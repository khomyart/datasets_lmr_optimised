<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'ip_address',
        'last_used',
        'expired_at'
    ];

    protected $hidden = [
        'id',
        'user_id',
        'created_at',
        'updated_at',
        'ip_address'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}

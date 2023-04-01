<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id", "executive_authorities",
        "datasets", "resources", "debtors",
        "reminders", "inactives"
    ];

    protected $hidden = [
        'updated_at', 'id', 'user_id'
    ];
}

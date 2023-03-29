<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExecutiveAuthority extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['id', 'user_id', 'name', 'display_name'];
    protected $guarded = ['created_at', 'updated_at'];
}

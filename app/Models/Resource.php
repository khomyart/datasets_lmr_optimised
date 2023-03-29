<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['id', 'user_id', 'dataset_id', 'state', 'name', 'description', 'format', 'url', 'validation_status'];

}

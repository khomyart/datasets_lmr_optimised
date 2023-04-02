<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dataset extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'user_id',
        'executive_authority_name',
        'state', 'name', 'title',
        'description', 'tags',
        'purpose', 'formats',
        'last_updated_at',
        'update_frequency',
        'next_update_at',
        'days_to_update',
        'maintainer_name',
        'maintainer_email',
        'type'];

}

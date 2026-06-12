<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $fillable = [
        'number',
        'status'
    ];

    protected $casts = [
        'status' => 'integer'
    ];
}

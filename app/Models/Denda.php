<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Denda extends Model
{
    use HasFactory, \App\Traits\Auditable;
    protected $table = 'denda';
    protected $guarded = ['id'];
}

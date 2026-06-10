<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tamu extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $table = 'tamus';

    protected $guarded = ['id'];
}

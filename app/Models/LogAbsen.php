<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAbsen extends Model
{
    use HasFactory, \App\Traits\Auditable;
    protected $table = 'log_absen';
}

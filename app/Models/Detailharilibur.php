<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detailharilibur extends Model
{
    use HasFactory, \App\Traits\Auditable;
    protected $table = 'hari_libur_detail';
    protected $guarded = [];
}

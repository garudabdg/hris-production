<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $table = 'pengumuman';

    protected $fillable = [
        'judul',
        'isi',
        'lampiran',
    ];
}

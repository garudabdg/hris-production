<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $fillable = [
        'pengirim',
        'penerima',
        'pesan',
        'kategori',
        'status',
        'message_id',
        'error_message'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItTicketResponse extends Model
{
    protected $fillable = [
        'ticket_id', 'user_id', 'pesan', 'tipe', 'lampiran',
    ];

    public function ticket()
    {
        return $this->belongsTo(ItTicket::class, 'ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getTipeBadgeAttribute(): string
    {
        $map = [
            'response'      => ['label' => 'Balasan',   'color' => 'primary'],
            'status_change' => ['label' => 'Status',    'color' => 'warning'],
            'assignment'    => ['label' => 'Penugasan', 'color' => 'info'],
            'resolusi'      => ['label' => 'Resolusi',  'color' => 'success'],
        ];
        $t = $map[$this->tipe] ?? ['label' => $this->tipe, 'color' => 'secondary'];
        return "<span class=\"badge bg-label-{$t['color']}\">{$t['label']}</span>";
    }
}

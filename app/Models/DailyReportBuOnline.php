<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model: Daily Report BU Online
 * 
 * Menyimpan aktivitas online per platform sosial media.
 * Setiap report memiliki 5 row (facebook, instagram, whatsapp, tiktok, youtube).
 */
class DailyReportBuOnline extends Model
{
    use HasFactory;

    protected $table = 'daily_report_bu_online';

    protected $fillable = [
        'daily_report_bu_id',
        'platform',
        'posting',
        'share_group',
        'add_group',
        'add_friend',
        'inbox',
        'story',
        'broadcast',
        'fanspage',
        'link_postingan',
        'status_validasi',
    ];

    protected $casts = [
        'posting'          => 'integer',
        'share_group'      => 'integer',
        'add_group'        => 'integer',
        'add_friend'       => 'integer',
        'inbox'            => 'integer',
        'story'            => 'integer',
        'broadcast'        => 'integer',
        'fanspage'         => 'integer',
        'status_validasi'  => 'string',
    ];

    /**
     * Relasi ke header report
     */
    public function dailyReport()
    {
        return $this->belongsTo(DailyReportBu::class, 'daily_report_bu_id');
    }

    /**
     * Hitung subtotal per platform
     */
    public function getSubtotalAttribute()
    {
        return $this->posting + $this->share_group + $this->add_group
            + $this->add_friend + $this->inbox + $this->story
            + $this->broadcast + $this->fanspage;
    }
}

<?php

namespace App\Jobs;

use App\Models\Pengumuman;
use App\Models\User;
use App\Notifications\PengumumanNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class BroadcastPengumumanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pengumuman;

    /**
     * Create a new job instance.
     */
    public function __construct(Pengumuman $pengumuman)
    {
        $this->pengumuman = $pengumuman;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        User::whereNotNull('email')
            ->where('email', '!=', '')
            ->chunk(100, function ($users) {
                Notification::send($users, new PengumumanNotification($this->pengumuman));
            });
    }
}

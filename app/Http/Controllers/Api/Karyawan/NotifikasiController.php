<?php

namespace App\Http\Controllers\Api\Karyawan;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function notifikasi(Request $request)
    {
        $user = $request->user();

        $notifs = \Illuminate\Notifications\DatabaseNotification::where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get()
            ->map(function ($n) {
                return [
                    'id'            => $n->id,
                    'is_read'       => !is_null($n->read_at),
                    'type'          => $n->data['type']          ?? null,
                    'title'         => $n->data['title']         ?? null,
                    'message'       => $n->data['message']       ?? null,
                    'approval_type' => $n->data['approval_type'] ?? null,
                    'status'        => $n->data['status']        ?? null,
                    'approver_name' => $n->data['approver_name'] ?? null,
                    'created_at'    => Carbon::parse($n->created_at)->diffForHumans(),
                ];
            });

        $unread = \Illuminate\Notifications\DatabaseNotification::where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count'  => $unread,
                'notifications' => $notifs,
            ],
        ]);
    }

    public function readAllNotifikasi(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi telah dibaca.',
        ]);
    }
}

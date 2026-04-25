<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use App\Models\Userkaryawan;
use App\Models\Izinsakit;
use App\Models\Izinabsen;
use App\Models\Izincuti;
use App\Models\Izindinas;

class NotificationController extends Controller
{
    /**
     * Display all notifications for the authenticated user
     */
    public function index()
    {
        $user = auth()->user();
        $userkaryawan = null;
        
        // Get user's NIK if they are a karyawan
        if ($user->hasRole('karyawan')) {
            $userkaryawan = \App\Models\Userkaryawan::where('id_user', $user->id)->first();
        }

        // Get all notifications, but filter approval notifications to only show those from the user's own submissions
        $allNotifications = DatabaseNotification::where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $notifications = $allNotifications
            ->filter(function($notif) use ($user, $userkaryawan) {
                // If notification is NOT approval status, show it to everyone
                if (($notif->data['type'] ?? null) !== 'approval_status') {
                    return true;
                }

                // If notification IS approval status and user is karyawan, only show if it's their submission
                if ($user->hasRole('karyawan') && $userkaryawan) {
                    // Check if this approval is for an izin submitted by this karyawan
                    $approvalCode = $notif->data['approval_code'] ?? null;
                    $approvalType = $notif->data['approval_type'] ?? null;

                    if ($approvalType === 'IZIN_SAKIT') {
                        $izin = \App\Models\Izinsakit::where('kode_izin_sakit', $approvalCode)
                            ->where('nik', $userkaryawan->nik)
                            ->first();
                        return $izin ? true : false;
                    } elseif ($approvalType === 'IZIN_ABSEN') {
                        $izin = \App\Models\Izinabsen::where('kode_izin', $approvalCode)
                            ->where('nik', $userkaryawan->nik)
                            ->first();
                        return $izin ? true : false;
                    } elseif ($approvalType === 'IZIN_CUTI') {
                        $izin = \App\Models\Izincuti::where('kode_izin_cuti', $approvalCode)
                            ->where('nik', $userkaryawan->nik)
                            ->first();
                        return $izin ? true : false;
                    } elseif ($approvalType === 'IZIN_DINAS') {
                        $izin = \App\Models\Izindinas::where('kode_izin_dinas', $approvalCode)
                            ->where('nik', $userkaryawan->nik)
                            ->first();
                        return $izin ? true : false;
                    }
                    
                    return false;
                }

                // If user is admin/approver, show all approval notifications
                return true;
            })
            ->map(function($notif) {
                return [
                    'id' => $notif->id,
                    'title' => $notif->data['title'] ?? 'Notifikasi',
                    'message' => $notif->data['message'] ?? '',
                    'approval_type' => $notif->data['approval_type'] ?? '',
                    'approval_code' => $notif->data['approval_code'] ?? '',
                    'status' => $notif->data['status'] ?? null,
                    'approver_name' => $notif->data['approver_name'] ?? '',
                    'notes' => $notif->data['notes'] ?? null,
                    'created_at' => $notif->created_at,
                    'read_at' => $notif->read_at,
                    'is_read' => !is_null($notif->read_at)
                ];
            });

        $unreadCount = $notifications
            ->filter(function($notif) {
                return !$notif['is_read'];
            })
            ->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($notificationId)
    {
        $notification = DatabaseNotification::where('id', $notificationId)
            ->where('notifiable_id', auth()->user()->id)
            ->first();

        if ($notification) {
            $notification->update(['read_at' => now()]);
        }

        return redirect()->back()->with('success', 'Notifikasi ditandai sudah dibaca');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        DatabaseNotification::where('notifiable_id', auth()->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->back()->with('success', 'Semua notifikasi ditandai sudah dibaca');
    }

    /**
     * Delete a notification
     */
    public function delete($notificationId)
    {
        $notification = DatabaseNotification::where('id', $notificationId)
            ->where('notifiable_id', auth()->user()->id)
            ->first();

        if ($notification) {
            $notification->delete();
        }

        return redirect()->back()->with('success', 'Notifikasi dihapus');
    }

    /**
     * Get notifications as JSON (for AJAX/API)
     */
    public function getNotifications()
    {
        $user = auth()->user();
        $userkaryawan = null;
        
        // Get user's NIK if they are a karyawan
        if ($user->hasRole('karyawan')) {
            $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        }

        $allNotifications = DatabaseNotification::where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $notifications = $allNotifications
            ->filter(function($notif) use ($user, $userkaryawan) {
                // If notification is NOT approval status, show it to everyone
                if (($notif->data['type'] ?? null) !== 'approval_status') {
                    return true;
                }

                // If notification IS approval status and user is karyawan, only show if it's their submission
                if ($user->hasRole('karyawan') && $userkaryawan) {
                    $approvalCode = $notif->data['approval_code'] ?? null;
                    $approvalType = $notif->data['approval_type'] ?? null;

                    if ($approvalType === 'IZIN_SAKIT') {
                        $izin = Izinsakit::where('kode_izin_sakit', $approvalCode)
                            ->where('nik', $userkaryawan->nik)
                            ->first();
                        return $izin ? true : false;
                    } elseif ($approvalType === 'IZIN_ABSEN') {
                        $izin = Izinabsen::where('kode_izin', $approvalCode)
                            ->where('nik', $userkaryawan->nik)
                            ->first();
                        return $izin ? true : false;
                    } elseif ($approvalType === 'IZIN_CUTI') {
                        $izin = Izincuti::where('kode_izin_cuti', $approvalCode)
                            ->where('nik', $userkaryawan->nik)
                            ->first();
                        return $izin ? true : false;
                    } elseif ($approvalType === 'IZIN_DINAS') {
                        $izin = Izindinas::where('kode_izin_dinas', $approvalCode)
                            ->where('nik', $userkaryawan->nik)
                            ->first();
                        return $izin ? true : false;
                    }
                    
                    return false;
                }

                // If user is admin/approver, show all approval notifications
                return true;
            })
            ->map(function($notif) {
                return [
                    'id' => $notif->id,
                    'title' => $notif->data['title'] ?? 'Notifikasi',
                    'message' => $notif->data['message'] ?? '',
                    'approval_type' => $notif->data['approval_type'] ?? '',
                    'approval_code' => $notif->data['approval_code'] ?? '',
                    'status' => $notif->data['status'] ?? null,
                    'approver_name' => $notif->data['approver_name'] ?? '',
                    'created_at' => $notif->created_at->diffForHumans(),
                    'is_read' => !is_null($notif->read_at)
                ];
            });

        $unreadCount = $notifications
            ->filter(function($notif) {
                return !$notif['is_read'];
            })
            ->count();

        return response()->json([
            'notifications' => $notifications->values(),
            'unread_count' => $unreadCount,
            'total' => $notifications->count()
        ]);
    }
}

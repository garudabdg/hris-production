<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $approvalType;
    private $approvalCode;
    private $status;
    private $approverName;
    private $notes;

    /**
     * Create a new notification instance.
     * 
     * @param string $approvalType - Tipe approval: 'IZIN_SAKIT', 'IZIN_ABSEN', 'IZIN_CUTI', 'IZIN_DINAS'
     * @param string $approvalCode - Kode izin (kode_izin_sakit, kode_izin, etc)
     * @param int $status - Status approval: 0 (pending), 1 (approved), 2 (rejected)
     * @param string $approverName - Nama person yang approve/reject
     * @param string $notes - Catatan tambahan jika ada
     */
    public function __construct($approvalType, $approvalCode, $status, $approverName, $notes = null)
    {
        $this->approvalType = $approvalType;
        $this->approvalCode = $approvalCode;
        $this->status = $status;
        $this->approverName = $approverName;
        $this->notes = $notes;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Send via database and email if user has email
        $channels = ['database'];
        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusText = match($this->status) {
            1 => 'Disetujui ✅',
            2 => 'Ditolak ❌',
            default => 'Update Status'
        };

        $typeText = match($this->approvalType) {
            'IZIN_SAKIT' => 'Izin Sakit',
            'IZIN_ABSEN' => 'Izin Absen',
            'IZIN_CUTI' => 'Izin Cuti',
            'IZIN_DINAS' => 'Izin Dinas',
            default => 'Approval'
        };

        $subject = "📋 " . $typeText . " Anda " . $statusText;

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.approval-status', [
                'notifiable' => $notifiable,
                'approvalType' => $this->approvalType,
                'approvalCode' => $this->approvalCode,
                'status' => $this->status,
                'statusText' => $statusText,
                'typeText' => $typeText,
                'approverName' => $this->approverName,
                'notes' => $this->notes
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $typeText = match($this->approvalType) {
            'IZIN_SAKIT' => 'Izin Sakit',
            'IZIN_ABSEN' => 'Izin Absen',
            'IZIN_CUTI' => 'Izin Cuti',
            'IZIN_DINAS' => 'Izin Dinas',
            default => 'Approval'
        };

        $statusBadge = match($this->status) {
            1 => '✅ Disetujui',
            2 => '❌ Ditolak',
            default => '⏳ Pending'
        };

        return [
            'title' => $typeText . ' - ' . $statusBadge,
            'message' => 'Pengajuan ' . strtolower($typeText) . ' Anda telah ' . (match($this->status) { 1 => 'disetujui', 2 => 'ditolak', default => 'diupdate' }) . ' oleh ' . $this->approverName,
            'approval_type' => $this->approvalType,
            'approval_code' => $this->approvalCode,
            'status' => $this->status,
            'approver_name' => $this->approverName,
            'notes' => $this->notes,
            'type' => 'approval_status'
        ];
    }
}

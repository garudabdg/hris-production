<?php

// Helper function untuk get pending approval notifications
function getApprovalNotifications($userId, $limit = 5)
{
    return \Illuminate\Notifications\DatabaseNotification::where('notifiable_id', $userId)
        ->whereJsonContains('data->type', 'approval_status')
        ->orderBy('created_at', 'desc')
        ->limit($limit)
        ->get()
        ->map(function($notif) {
            return [
                'id' => $notif->id,
                'title' => $notif->data['title'] ?? 'Approval Update',
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
}

// Helper function untuk get count of unread approval notifications
function countUnreadApprovalNotifications($userId)
{
    return \Illuminate\Notifications\DatabaseNotification::where('notifiable_id', $userId)
        ->whereJsonContains('data->type', 'approval_status')
        ->whereNull('read_at')
        ->count();
}

// Helper function untuk get latest approval status
function getLatestApprovalNotification($userId)
{
    return \Illuminate\Notifications\DatabaseNotification::where('notifiable_id', $userId)
        ->whereJsonContains('data->type', 'approval_status')
        ->orderBy('created_at', 'desc')
        ->first();
}

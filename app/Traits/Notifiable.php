<?php

namespace App\Traits;

use App\Models\Notification;
use App\Models\User;

trait Notifiable
{
    /**
     * Create a notification
     */
    protected function createNotification($data)
    {
        return Notification::create($data);
    }

    /**
     * Notify a specific user
     */
    protected function notifyUser($userId, $title, $message, $type = 'info', $link = null)
    {
        return $this->createNotification([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'link' => $link,
            'is_read' => false
        ]);
    }

    /**
     * Notify all admins (role_id = 1)
     */
    protected function notifyAllAdmins($title, $message, $type = 'info', $link = null)
    {
        $adminIds = User::where('role_id', 1)->pluck('id');

        foreach ($adminIds as $userId) {
            $this->notifyUser($userId, $title, $message, $type, $link);
        }
    }

    /**
     * Notify all barangay captains (role_id = 2)
     */
    protected function notifyAllCaptains($title, $message, $type = 'info', $link = null)
    {
        $captainIds = User::where('role_id', 2)->pluck('id');

        foreach ($captainIds as $userId) {
            $this->notifyUser($userId, $title, $message, $type, $link);
        }
    }

    /**
     * Notify all secretaries (role_id = 3)
     */
    protected function notifyAllSecretaries($title, $message, $type = 'info', $link = null)
    {
        $secretaryIds = User::where('role_id', 3)->pluck('id');

        foreach ($secretaryIds as $userId) {
            $this->notifyUser($userId, $title, $message, $type, $link);
        }
    }

    /**
     * Notify all users
     */
    protected function notifyAllUsers($title, $message, $type = 'info', $link = null)
    {
        $userIds = User::pluck('id');

        foreach ($userIds as $userId) {
            $this->notifyUser($userId, $title, $message, $type, $link);
        }
    }

    /**
     * Notify users by role
     */
    protected function notifyByRole($roleId, $title, $message, $type = 'info', $link = null)
    {
        $userIds = User::where('role_id', $roleId)->pluck('id');

        foreach ($userIds as $userId) {
            $this->notifyUser($userId, $title, $message, $type, $link);
        }
    }
}

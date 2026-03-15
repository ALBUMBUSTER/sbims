<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\User;

class NotificationHelper
{
    /**
     * Send notification to a specific user
     */
    public static function toUser($userId, $title, $message, $type = 'info', $link = null)
    {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'link' => $link,
            'is_read' => false
        ]);
    }

    /**
     * Send notification to all users with a specific role
     */
    public static function toRole($roleId, $title, $message, $type = 'info', $link = null)
    {
        $users = User::where('role_id', $roleId)->where('is_active', true)->get();
        $notifications = [];

        foreach ($users as $user) {
            $notifications[] = Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'link' => $link,
                'is_read' => false
            ]);
        }

        return $notifications;
    }

    /**
     * Send notification to multiple roles
     */
    public static function toRoles($roleIds, $title, $message, $type = 'info', $link = null)
    {
        $users = User::whereIn('role_id', $roleIds)->where('is_active', true)->get();
        $notifications = [];

        foreach ($users as $user) {
            $notifications[] = Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'link' => $link,
                'is_read' => false
            ]);
        }

        return $notifications;
    }

    /**
     * Send notification to all admins (role_id = 1)
     */
    public static function toAdmins($title, $message, $type = 'info', $link = null)
    {
        return self::toRole(1, $title, $message, $type, $link);
    }

    /**
     * Send notification to all captains (role_id = 2)
     */
    public static function toCaptains($title, $message, $type = 'info', $link = null)
    {
        return self::toRole(2, $title, $message, $type, $link);
    }

    /**
     * Send notification to all secretaries (role_id = 3)
     */
    public static function toSecretaries($title, $message, $type = 'info', $link = null)
    {
        return self::toRole(3, $title, $message, $type, $link);
    }

    /**
     * Send notification to all clerks (role_id = 4)
     */
    public static function toClerks($title, $message, $type = 'info', $link = null)
    {
        return self::toRole(4, $title, $message, $type, $link);
    }

    /**
     * Send notification to everyone except current user
     */
    public static function toEveryoneExcept($excludeUserId, $title, $message, $type = 'info', $link = null)
    {
        $users = User::where('id', '!=', $excludeUserId)->where('is_active', true)->get();
        $notifications = [];

        foreach ($users as $user) {
            $notifications[] = Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'link' => $link,
                'is_read' => false
            ]);
        }

        return $notifications;
    }
}

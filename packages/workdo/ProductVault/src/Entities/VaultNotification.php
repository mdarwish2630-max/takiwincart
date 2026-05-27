<?php

namespace Workdo\ProductVault\Entities;

use Illuminate\Database\Eloquent\Model;

class VaultNotification extends Model
{
    protected $table = "vault_notifications";

    protected $fillable = [
        "user_id", "type", "title", "message",
        "link", "is_read", "icon", "icon_color"
    ];

    protected $casts = [
        "is_read" => "boolean",
    ];

    /**
     * Send notification to a user
     */
    public static function notify($userId, $type, $title, $message, $link = null, $icon = "bell", $iconColor = "primary")
    {
        return self::create([
            "user_id"     => $userId,
            "type"        => $type,       // admin / merchant
            "title"       => $title,
            "message"     => $message,
            "link"        => $link,
            "is_read"     => false,
            "icon"        => $icon,
            "icon_color"  => $iconColor,
        ]);
    }

    /**
     * Get unread count for a user
     */
    public static function unreadCount($userId)
    {
        return self::where("user_id", $userId)->where("is_read", false)->count();
    }

    /**
     * Mark all as read for a user
     */
    public static function markAllRead($userId)
    {
        return self::where("user_id", $userId)->where("is_read", false)->update(["is_read" => true]);
    }

    /**
     * Mark single as read
     */
    public static function markRead($id, $userId)
    {
        return self::where("id", $id)->where("user_id", $userId)->update(["is_read" => true]);
    }

    /**
     * Get notifications for a user
     */
    public static function getUserNotifications($userId, $limit = 20)
    {
        return self::where("user_id", $userId)
            ->orderBy("created_at", "desc")
            ->limit($limit)
            ->get();
    }
}

<?php

namespace Ubxty\SpatieLaravelBackupUtils\Notifications;

use Illuminate\Notifications\Notifiable as NotifiableTrait;
use Illuminate\Support\Facades\Log;
use Spatie\Backup\Notifications\Notifiable as SpatieNotifiable;

/**
 * Enhanced backup notifiable class with automatic logging capabilities.
 *
 * Extends Spatie's notifiable to automatically log all backup notifications
 * with structured metadata and system information.
 *
 * @package Ubxty\SpatieLaravelBackupUtils
 * @author  Ravdeep Singh <info@ubxty.com>
 * @author  UBXTY Unboxing Technology <info@ubxty.com>
 * @license MIT
 * @version 1.0.2
 */
class BackupNotifiable extends SpatieNotifiable
{
    use NotifiableTrait;

    public function getKey(): int
    {
        return 1; // Use a fixed ID for the backup system
    }

    public function routeNotificationForBackupLog()
    {
        return $this;
    }

    public function notify($notification)
    {
        // First call the parent notify method to maintain Spatie functionality
        parent::notify($notification);
        
        // Then add our custom logging
        $notificationData = [
            'class' => get_class($notification),
            'timestamp' => now()->toISOString(),
        ];

        // Try to get message if available
        if (method_exists($notification, 'getMessage')) {
            $notificationData['message'] = $notification->getMessage();
        }

        Log::channel('backup')->info('Backup Notification: ' . class_basename(get_class($notification)), $notificationData);

        // Use Laravel's notification system to send to backup_log channel
        $this->notifyNow($notification, ['backup_log']);
    }
} 
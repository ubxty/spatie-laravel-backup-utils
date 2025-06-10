<?php

namespace Ubxty\SpatieLaravelBackupUtils\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\BackupHasFailed;

/**
 * Custom notification channel for structured backup event logging.
 *
 * Handles logging of backup notifications with detailed metadata extraction,
 * system information, and formatted log entries for analysis.
 *
 * @package Ubxty\SpatieLaravelBackupUtils
 * @author  Ravdeep Singh <info@ubxty.com>
 * @author  UBXTY Unboxing Technology <info@ubxty.com>
 * @license MIT
 * @version 1.0.0
 */
class BackupLogChannel
{
    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification): void
    {
        $logData = [
            'timestamp' => Carbon::now()->toISOString(),
            'notification_type' => class_basename($notification),
            'notification_class' => get_class($notification),
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => method_exists($notifiable, 'getKey') ? $notifiable->getKey() : 'system',
            'metadata' => [
                'memory_usage' => memory_get_peak_usage(true),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
            ],
        ];

        // Extract basic information about the notification without complex property inspection
        // This provides essential logging information while avoiding errors from uninitialized properties
        try {
            // Add notification context
            $logData['notification_context'] = [
                'class_methods' => get_class_methods($notification),
                'class_parents' => array_keys(class_parents($notification) ?: []),
            ];

            // Try to extract message specifically for backup notifications
            if (method_exists($notification, 'toMail')) {
                try {
                    $mailMessage = $notification->toMail(null);
                    if (is_object($mailMessage) && method_exists($mailMessage, 'subject')) {
                        $logData['mail_subject'] = $mailMessage->subject;
                    }
                } catch (\Throwable $e) {
                    // Silently continue
                }
            }

            // For failed notifications, add general failure context
            if (str_contains(class_basename($notification), 'Failed')) {
                $logData['failure_context'] = [
                    'type' => 'backup_failure',
                    'notification_type' => class_basename($notification),
                    'timestamp' => now()->toISOString(),
                ];
            }
        } catch (\Throwable $e) {
            // If even the basic extraction fails, just note it
            $logData['extraction_note'] = 'Basic information extraction failed: ' . $e->getMessage();
        }

        // Try to get message from notification if available
        if (method_exists($notification, 'getMessage')) {
            $logData['message'] = $notification->getMessage();
        }

        // Determine log level and summary based on notification type
        $logLevel = $this->getLogLevel($notification);
        $summary = $this->getSummary($notification);
        
        // Log to backup-specific channel
        Log::channel('backup')->log($logLevel, $summary, $logData);
        
        // Also log to daily backup log for historical tracking
        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/backup.log'),
            'days' => 30,
        ])->log($logLevel, $summary, $logData);
    }

    /**
     * Determine the appropriate log level based on notification type.
     */
    private function getLogLevel(Notification $notification): string
    {
        $class = class_basename($notification);
        
        return match (true) {
            str_contains($class, 'Failed') || str_contains($class, 'Unhealthy') => 'error',
            str_contains($class, 'Successful') || str_contains($class, 'Healthy') => 'info',
            default => 'info',
        };
    }

    /**
     * Generate a summary message based on notification type.
     */
    private function getSummary(Notification $notification): string
    {
        $class = class_basename($notification);
        
        return match (true) {
            str_contains($class, 'BackupWasSuccessful') => 'Backup completed successfully',
            str_contains($class, 'BackupHasFailed') => 'Backup failed',
            str_contains($class, 'CleanupWasSuccessful') => 'Backup cleanup completed successfully',
            str_contains($class, 'CleanupHasFailed') => 'Backup cleanup failed',
            str_contains($class, 'HealthyBackupWasFound') => 'Healthy backup found',
            str_contains($class, 'UnhealthyBackupWasFound') => 'Unhealthy backup found',
            default => "Backup notification: {$class}",
        };
    }
} 
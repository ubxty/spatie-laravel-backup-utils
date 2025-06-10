<?php
/**
 * UBXTY Spatie Laravel Backup Utils Configuration
 *
 * Configuration file for the UBXTY Spatie Laravel Backup Utils package providing
 * enhanced backup management, logging, and notification capabilities.
 *
 * @package Ubxty\LaravelBackupUtils
 * @author  Ravdeep Singh <info@ubxty.com>
 * @author  UBXTY Unboxing Technology <info@ubxty.com>
 * @license MIT
 * @version 1.0.1
 */

return [
    /*
    |--------------------------------------------------------------------------
    | UBXTY Backup Utils Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for ubxty/laravel-backup-utils package
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Auto Configuration
    |--------------------------------------------------------------------------
    |
    | Whether to automatically configure backup components
    |
    */
    'auto_configure' => [
        'logging' => env('BACKUP_UTILS_AUTO_LOGGING', true),
        'filesystem' => env('BACKUP_UTILS_AUTO_FILESYSTEM', true),
        'notifications' => env('BACKUP_UTILS_AUTO_NOTIFICATIONS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for backup logging
    |
    */
    'logging' => [
        'channel' => env('BACKUP_LOG_CHANNEL', 'backup'),
        'level' => env('BACKUP_LOG_LEVEL', 'debug'),
        'days' => env('BACKUP_LOG_DAYS', 60),
        'path' => env('BACKUP_LOG_PATH', storage_path('logs/backup.log')),
    ],

    /*
    |--------------------------------------------------------------------------
    | S3 Backup Configuration
    |--------------------------------------------------------------------------
    |
    | Default S3 backup disk configuration
    |
    */
    's3_backup' => [
        'disk_name' => env('BACKUP_S3_DISK_NAME', 's3_backup'),
        'auto_configure' => env('BACKUP_S3_AUTO_CONFIGURE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Statistics Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for backup statistics and analytics
    |
    */
    'stats' => [
        'default_days' => env('BACKUP_STATS_DEFAULT_DAYS', 30),
        'max_days' => env('BACKUP_STATS_MAX_DAYS', 365),
        'cache_duration' => env('BACKUP_STATS_CACHE_DURATION', 300), // 5 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Enhancement
    |--------------------------------------------------------------------------
    |
    | Settings for enhanced notifications
    |
    */
    'notifications' => [
        'auto_enhance' => env('BACKUP_NOTIFICATIONS_AUTO_ENHANCE', true),
        'add_log_channel' => env('BACKUP_NOTIFICATIONS_ADD_LOG_CHANNEL', true),
        'notifiable_class' => env('BACKUP_NOTIFIABLE_CLASS', \Ubxty\LaravelBackupUtils\Notifications\BackupNotifiable::class),
    ],

    /*
    |--------------------------------------------------------------------------
    | Testing Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for testing and validation
    |
    */
    'testing' => [
        's3_test_file_prefix' => env('BACKUP_S3_TEST_PREFIX', 'backup-test-'),
        'connection_timeout' => env('BACKUP_CONNECTION_TIMEOUT', 30),
    ],
]; 
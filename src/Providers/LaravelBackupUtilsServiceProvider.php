<?php

namespace Ubxty\SpatieLaravelBackupUtils\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Notifications\ChannelManager;
use Ubxty\SpatieLaravelBackupUtils\Notifications\Channels\BackupLogChannel;
use Ubxty\SpatieLaravelBackupUtils\Commands\BackupUtilsCommand;
use Ubxty\SpatieLaravelBackupUtils\Commands\BackupStatsCommand;
use Ubxty\SpatieLaravelBackupUtils\Commands\BackupConfigS3Command;
use Ubxty\SpatieLaravelBackupUtils\Commands\BackupLogsCommand;
use Ubxty\SpatieLaravelBackupUtils\Commands\TestBackupNotificationCommand;

/**
 * Service provider for UBXTY Spatie Laravel Backup Utils package.
 *
 * Handles package bootstrapping, command registration, configuration publishing,
 * and automatic setup of backup logging, S3 filesystem, and enhanced notifications.
 *
 * @package Ubxty\SpatieLaravelBackupUtils
 * @author  Ravdeep Singh <info@ubxty.com>
 * @author  UBXTY Unboxing Technology <info@ubxty.com>
 * @license MIT
 * @version 1.0.0
 */
class LaravelBackupUtilsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge package config with app config
        $this->mergeConfigFrom(__DIR__ . '/../../config/backup-utils.php', 'backup-utils');
        
        // Register the backup log channel
        $this->app->resolving(ChannelManager::class, function (ChannelManager $manager, $app) {
            $manager->extend('backup_log', function () use ($app) {
                return new BackupLogChannel();
            });
        });

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                BackupUtilsCommand::class,
                BackupStatsCommand::class,
                BackupConfigS3Command::class,
                BackupLogsCommand::class,
                TestBackupNotificationCommand::class,
            ]);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config files
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/backup-utils.php' => config_path('backup-utils.php'),
            ], 'backup-utils-config');

            // Publish logging configuration
            $this->publishes([
                __DIR__ . '/../../config/logging-backup.php' => config_path('logging-backup.php'),
            ], 'backup-utils-logging');

            // Publish filesystem configuration
            $this->publishes([
                __DIR__ . '/../../config/filesystems-backup.php' => config_path('filesystems-backup.php'),
            ], 'backup-utils-filesystems');

            // Publish backup configuration updates
            $this->publishes([
                __DIR__ . '/../../config/backup-notifications.php' => config_path('backup-notifications.php'),
            ], 'backup-utils-notifications');

            // Publish environment template
            $this->publishes([
                __DIR__ . '/../../config/env-backup.txt' => base_path('env-backup.example'),
            ], 'backup-utils-env');
        }

        // Auto-configure logging if not already configured
        $this->configureLogging();
        
        // Auto-configure filesystems if not already configured
        $this->configureFilesystems();
        
        // Auto-configure backup notifications
        $this->configureBackupNotifications();
    }

    /**
     * Auto-configure logging for backups.
     */
    protected function configureLogging(): void
    {
        if (!config('logging.channels.backup')) {
            config([
                'logging.channels.backup' => [
                    'driver' => 'daily',
                    'path' => storage_path('logs/backup.log'),
                    'level' => env('LOG_BACKUP_LEVEL', 'debug'),
                    'days' => env('LOG_BACKUP_DAYS', 60),
                    'replace_placeholders' => true,
                ]
            ]);
        }
    }

    /**
     * Auto-configure s3_backup filesystem if not already configured.
     */
    protected function configureFilesystems(): void
    {
        if (!config('filesystems.disks.s3_backup')) {
            config([
                'filesystems.disks.s3_backup' => [
                    'driver' => 's3',
                    'key' => env('AWS_ACCESS_KEY_ID_BACKUP'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY_BACKUP'),
                    'region' => env('AWS_DEFAULT_REGION_BACKUP'),
                    'bucket' => env('AWS_BUCKET_BACKUP'),
                    'url' => env('AWS_URL_BACKUP'),
                    'endpoint' => env('AWS_ENDPOINT_BACKUP'),
                    'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT_BACKUP', false),
                    'throw' => false,
                    'report' => false,
                ]
            ]);
        }
    }

    /**
     * Auto-configure backup notifications.
     */
    protected function configureBackupNotifications(): void
    {
        // Only configure if backup config exists and notifications aren't already configured
        if (config('backup') && !config('backup.notifications.notifiable')) {
            config([
                'backup.notifications.notifiable' => \Ubxty\SpatieLaravelBackupUtils\Notifications\BackupNotifiable::class,
            ]);

            // Add backup_log channel to all notifications if not already present
            $notifications = config('backup.notifications.notifications', []);
            foreach ($notifications as $notification => $channels) {
                if (!in_array('backup_log', $channels)) {
                    $channels[] = 'backup_log';
                    config(["backup.notifications.notifications.{$notification}" => $channels]);
                }
            }
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            BackupUtilsCommand::class,
            BackupStatsCommand::class,
            BackupConfigS3Command::class,
            BackupLogsCommand::class,
            TestBackupNotificationCommand::class,
        ];
    }
} 
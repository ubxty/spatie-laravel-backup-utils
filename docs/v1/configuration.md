---
title: Configuration
order: 3
---

# Configuration

This page details all the configuration options available in Laravel Backup Utils.

## Configuration File

The package's configuration file is located at `config/backup-utils.php`. You can publish this file using:

```bash
php artisan vendor:publish --provider="Spatie\BackupUtils\BackupUtilsServiceProvider"
```

## Available Options

### Backup Settings

```php
'backup' => [
    // The disk where backups will be stored
    'disk' => env('BACKUP_UTILS_STORAGE_DISK', 'local'),
    
    // The path where backups will be stored on the disk
    'path' => 'backups',
    
    // The maximum number of backups to keep
    'keep_backups_for_days' => 7,
    
    // Whether to compress backups
    'compress' => true,
    
    // The compression level (1-9)
    'compression_level' => 9,
],
```

### Notification Settings

```php
'notifications' => [
    // Email notification settings
    'mail' => [
        'to' => env('BACKUP_UTILS_NOTIFICATION_EMAIL'),
        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'backups@example.com'),
            'name' => env('MAIL_FROM_NAME', 'Backup System'),
        ],
        'subject' => 'Backup Notification',
    ],
    
    // Slack notification settings
    'slack' => [
        'webhook_url' => env('BACKUP_UTILS_NOTIFICATION_SLACK_WEBHOOK_URL'),
        'channel' => '#backups',
        'username' => 'Backup Bot',
    ],
    
    // Notification events
    'notify_on' => [
        'backup_success' => true,
        'backup_failure' => true,
        'cleanup_success' => false,
        'cleanup_failure' => true,
        'monitoring_alert' => true,
    ],
],
```

### Monitoring Settings

```php
'monitoring' => [
    // Enable/disable monitoring
    'enabled' => true,
    
    // How often to check backup health
    'check_frequency' => 'daily', // Options: hourly, daily, weekly
    
    // Health check thresholds
    'thresholds' => [
        'max_backup_age_hours' => 24,
        'min_backup_size_mb' => 1,
        'max_backup_size_mb' => 1000,
    ],
    
    // Whether to notify on monitoring failures
    'notify_on_failure' => true,
    
    // Custom health checks
    'health_checks' => [
        // Add your custom health check classes here
    ],
],
```

### Database Settings

```php
'database' => [
    // Enable/disable database features
    'enabled' => true,
    
    // Database connection to use
    'connection' => env('DB_CONNECTION', 'mysql'),
    
    // Tables to track
    'tables' => [
        'backups' => 'backup_utils_backups',
        'backup_logs' => 'backup_utils_logs',
    ],
],
```

### Advanced Settings

```php
'advanced' => [
    // Custom backup strategies
    'strategies' => [
        // Add your custom strategy classes here
    ],
    
    // Backup process settings
    'process' => [
        'timeout' => 3600, // Maximum execution time in seconds
        'memory_limit' => '512M',
        'temporary_directory' => storage_path('app/temp'),
    ],
    
    // Logging settings
    'logging' => [
        'enabled' => true,
        'channel' => 'daily',
        'level' => 'info',
    ],
],
```

## Environment Variables

The following environment variables can be set in your `.env` file:

```env
BACKUP_UTILS_STORAGE_DISK=local
BACKUP_UTILS_NOTIFICATION_EMAIL=your-email@example.com
BACKUP_UTILS_NOTIFICATION_SLACK_WEBHOOK_URL=your-slack-webhook-url
BACKUP_UTILS_MONITORING_ENABLED=true
BACKUP_UTILS_DATABASE_ENABLED=true
```

## Custom Configuration

### Adding Custom Health Checks

You can add custom health checks by implementing the `HealthCheck` interface:

```php
use Spatie\BackupUtils\HealthChecks\HealthCheck;

class CustomHealthCheck implements HealthCheck
{
    public function check(): HealthCheckResult
    {
        // Your health check logic here
    }
}
```

Then add it to the configuration:

```php
'monitoring' => [
    'health_checks' => [
        CustomHealthCheck::class,
    ],
],
```

### Adding Custom Strategies

You can add custom backup strategies by implementing the `BackupStrategy` interface:

```php
use Spatie\BackupUtils\Strategies\BackupStrategy;

class CustomStrategy implements BackupStrategy
{
    public function execute(): void
    {
        // Your backup strategy logic here
    }
}
```

Then add it to the configuration:

```php
'advanced' => [
    'strategies' => [
        CustomStrategy::class,
    ],
],
```

## Validation

The configuration is validated when the application boots. If there are any issues with your configuration, you'll see an error message in your Laravel logs.

You can manually validate your configuration using:

```bash
php artisan backup-utils:validate-config
```

## Best Practices

1. Always use environment variables for sensitive information
2. Keep your backup path outside of the public directory
3. Regularly review and adjust your retention policies
4. Monitor your backup sizes and adjust compression settings accordingly
5. Set up notifications for critical events
6. Regularly test your backup restoration process

## Next Steps

- Learn about [Monitoring](/docs/v1/monitoring)
- Configure [Notifications](/docs/v1/notifications)
- Explore [Advanced Usage](/docs/v1/advanced-usage) 
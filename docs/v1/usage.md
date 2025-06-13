---
title: Usage
order: 4
permalink: /v1/usage/
---

# Usage

This guide covers the basic usage of Laravel Backup Utils, including commands, features, and common use cases.

## Basic Commands

### Creating Backups

To create a backup of your application:

```bash
php artisan backup-utils:run
```

This command will:
1. Create a backup of your database
2. Create a backup of your files
3. Compress the backup (if enabled)
4. Store it in the configured location
5. Send notifications (if configured)

### Listing Backups

To list all available backups:

```bash
php artisan backup-utils:list
```

This will show:
- Backup filename
- Size
- Creation date
- Status
- Type (database, files, or both)

### Cleaning Up Old Backups

To clean up old backups based on your retention policy:

```bash
php artisan backup-utils:cleanup
```

### Monitoring Backups

To check the health of your backups:

```bash
php artisan backup-utils:monitor
```

## Programmatic Usage

### Using the BackupUtils Facade

```php
use Spatie\BackupUtils\Facades\BackupUtils;

// Create a backup
BackupUtils::createBackup();

// Get backup statistics
$stats = BackupUtils::getBackupStats();

// Check backup health
$health = BackupUtils::checkBackupHealth();

// Clean up old backups
BackupUtils::cleanupOldBackups();
```

### Using the Backup Manager

```php
use Spatie\BackupUtils\BackupManager;

$manager = app(BackupManager::class);

// Create a backup with custom options
$manager->createBackup([
    'include_files' => true,
    'include_database' => true,
    'compress' => true,
]);

// Get backup information
$backup = $manager->getBackup('backup-2023-01-01.zip');

// Restore a backup
$manager->restoreBackup('backup-2023-01-01.zip');
```

## Advanced Features

### Custom Backup Strategies

You can implement custom backup strategies:

```php
use Spatie\BackupUtils\Strategies\BackupStrategy;

class CustomBackupStrategy implements BackupStrategy
{
    public function execute(): void
    {
        // Your custom backup logic
    }
}

// Register the strategy
BackupUtils::registerStrategy(CustomBackupStrategy::class);

// Use the strategy
BackupUtils::createBackupWithStrategy(CustomBackupStrategy::class);
```

### Custom Health Checks

Implement custom health checks:

```php
use Spatie\BackupUtils\HealthChecks\HealthCheck;

class CustomHealthCheck implements HealthCheck
{
    public function check(): HealthCheckResult
    {
        // Your custom health check logic
        return new HealthCheckResult(
            true,
            'Custom check passed'
        );
    }
}

// Register the health check
BackupUtils::registerHealthCheck(CustomHealthCheck::class);
```

### Custom Notifications

Create custom notification channels:

```php
use Spatie\BackupUtils\Notifications\NotificationChannel;

class CustomNotificationChannel implements NotificationChannel
{
    public function send(BackupEvent $event): void
    {
        // Your custom notification logic
    }
}

// Register the notification channel
BackupUtils::registerNotificationChannel(CustomNotificationChannel::class);
```

## Common Use Cases

### Scheduled Backups

Add to your `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Daily backup at 1 AM
    $schedule->command('backup-utils:run')
        ->dailyAt('01:00')
        ->appendOutputTo(storage_path('logs/backup.log'));
        
    // Weekly cleanup
    $schedule->command('backup-utils:cleanup')
        ->weekly()
        ->sundays()
        ->at('02:00');
        
    // Daily health check
    $schedule->command('backup-utils:monitor')
        ->dailyAt('03:00');
}
```

### Backup Rotation

Implement a custom rotation strategy:

```php
use Spatie\BackupUtils\Strategies\RotationStrategy;

class CustomRotationStrategy implements RotationStrategy
{
    public function shouldRotate(Backup $backup): bool
    {
        // Your rotation logic
        return $backup->age()->days > 7;
    }
}

// Use the rotation strategy
BackupUtils::setRotationStrategy(CustomRotationStrategy::class);
```

### Backup Encryption

Enable backup encryption:

```php
// In your config/backup-utils.php
'encryption' => [
    'enabled' => true,
    'key' => env('BACKUP_ENCRYPTION_KEY'),
    'cipher' => 'AES-256-CBC',
],

// In your .env file
BACKUP_ENCRYPTION_KEY=your-encryption-key
```

## Best Practices

1. **Regular Testing**
   - Test backup creation regularly
   - Verify backup integrity
   - Practice restore procedures

2. **Monitoring**
   - Set up health checks
   - Configure notifications
   - Monitor backup sizes

3. **Security**
   - Use encryption for sensitive data
   - Secure backup storage
   - Implement access controls

4. **Performance**
   - Schedule backups during low-traffic periods
   - Use compression appropriately
   - Implement proper cleanup strategies

## Troubleshooting

### Common Issues

1. **Backup Fails**
   - Check disk space
   - Verify permissions
   - Check database connection
   - Review logs

2. **Cleanup Not Working**
   - Verify retention policy
   - Check file permissions
   - Review cleanup logs

3. **Notifications Not Sending**
   - Check notification configuration
   - Verify email/Slack settings
   - Review notification logs

### Getting Help

If you encounter issues:
1. Check the [GitHub issues](https://github.com/yourusername/spatie-laravel-backup-utils/issues)
2. Review the logs in `storage/logs/laravel.log`
3. Run the verification command: `php artisan backup-utils:verify`

## Next Steps

- Review the [Configuration]({{ '/v1/configuration/' | relative_url }}) guide
- Check the [Installation]({{ '/v1/installation/' | relative_url }}) documentation
- Visit our [GitHub repository](https://github.com/ubxty/spatie-laravel-backup-utils) for more examples 
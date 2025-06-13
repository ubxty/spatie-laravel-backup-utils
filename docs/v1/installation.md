---
title: Installation
order: 2
permalink: /v1/installation/
---

# Installation

This guide will walk you through the installation and basic setup of Laravel Backup Utils.

## Prerequisites

Before installing Laravel Backup Utils, make sure you have:

1. A working Laravel application (8.0 or higher)
2. PHP 8.0 or higher
3. Composer installed
4. [spatie/laravel-backup](https://github.com/spatie/laravel-backup) package installed

## Installation Steps

### 1. Install via Composer

```bash
composer require spatie/laravel-backup-utils
```

### 2. Publish Configuration

Publish the configuration file to your application:

```bash
php artisan vendor:publish --provider="Spatie\BackupUtils\BackupUtilsServiceProvider"
```

This will create a `backup-utils.php` configuration file in your `config` directory.

### 3. Configure Environment Variables

Add the following variables to your `.env` file:

```env
BACKUP_UTILS_NOTIFICATION_EMAIL=your-email@example.com
BACKUP_UTILS_NOTIFICATION_SLACK_WEBHOOK_URL=your-slack-webhook-url
BACKUP_UTILS_STORAGE_DISK=local
```

### 4. Configure Backup Disk

Make sure you have configured a disk in your `config/filesystems.php` that will be used for storing backups:

```php
'disks' => [
    // ...
    'backups' => [
        'driver' => 'local',
        'root' => storage_path('app/backups'),
    ],
],
```

### 5. Run Migrations

If you're using the database features, run the migrations:

```bash
php artisan migrate
```

## Configuration

The package configuration file (`config/backup-utils.php`) contains several sections that you can customize:

### Notification Settings

```php
'notifications' => [
    'mail' => [
        'to' => env('BACKUP_UTILS_NOTIFICATION_EMAIL'),
    ],
    'slack' => [
        'webhook_url' => env('BACKUP_UTILS_NOTIFICATION_SLACK_WEBHOOK_URL'),
    ],
],
```

### Storage Settings

```php
'storage' => [
    'disk' => env('BACKUP_UTILS_STORAGE_DISK', 'local'),
    'path' => 'backups',
],
```

### Monitoring Settings

```php
'monitoring' => [
    'enabled' => true,
    'check_frequency' => 'daily',
    'notify_on_failure' => true,
],
```

## Verification

To verify that the package is installed correctly, you can run:

```bash
php artisan backup-utils:verify
```

This command will check:
- Configuration file presence
- Required environment variables
- Storage disk configuration
- Database connection (if using database features)

## Next Steps

After installation, you might want to:

1. Review the [Configuration]({{ '/v1/configuration/' | relative_url }}) documentation
2. Learn about [Usage]({{ '/v1/usage/' | relative_url }})
3. Explore the complete documentation for advanced features

## Troubleshooting

If you encounter any issues during installation:

1. Make sure all prerequisites are met
2. Check that the configuration file was published correctly
3. Verify your environment variables
4. Check the Laravel logs for any errors
5. Run the verification command for specific issues

If you need further assistance, please [open an issue](https://github.com/yourusername/spatie-laravel-backup-utils/issues) on GitHub. 
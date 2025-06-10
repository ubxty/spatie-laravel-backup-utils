# UBXTY Spatie Laravel Backup Utils - Documentation

**Author:** Ravdeep Singh • [UBXTY Unboxing Technology](https://ubxty.com)  
**Version:** 1.0.0  
**License:** MIT

A simple and elegant backup notification system that provides comprehensive logging for all backup events in your Laravel application.

## Overview

This system extends the Spatie Laravel Backup package with minimal code to provide:

- **BackupLogChannel**: Custom notification channel for structured backup logging
- **BackupNotifiable**: Simple notifiable class that extends SpatieNotifiable
- **Automatic Logging**: All backup events are automatically logged with detailed metadata
- **Console Commands**: Tools for viewing and analyzing backup logs

## Architecture

### Simple and Clean Design

The system uses a clean approach that extends the existing Spatie functionality:

1. **BackupNotifiable** (`app/Notifications/BackupNotifiable.php`)
   - Extends `SpatieNotifiable` for maximum compatibility
   - Overrides the `notify()` method to automatically log all backup notifications
   - Maintains all original Spatie functionality

2. **BackupLogChannel** (`app/Notifications/Channels/BackupLogChannel.php`)
   - Handles structured logging of any backup notification
   - Automatically extracts relevant information from notifications
   - No custom notification classes required

3. **NotificationServiceProvider** (`app/Providers/NotificationServiceProvider.php`)
   - Registers the custom backup log channel
   - Simple and minimal implementation

## Key Advantages

✅ **Minimal Code**: Only 3 small files needed
✅ **Future-Proof**: Works with any new Spatie notifications automatically
✅ **No Maintenance**: No custom notification classes to maintain
✅ **Full Compatibility**: Maintains all original Spatie functionality
✅ **Automatic**: All backup events are logged without configuration

## Configuration

### Logging Configuration

Add the backup logging channel in `config/logging.php`:

```php
'backup' => [
    'driver' => 'daily',
    'path' => storage_path('logs/backup.log'),
    'level' => env('LOG_BACKUP_LEVEL', 'debug'),
    'days' => env('LOG_BACKUP_DAYS', 60),
    'replace_placeholders' => true,
],
```

### Backup Configuration

Update `config/backup.php` to use the custom notifiable:

```php
'notifications' => [
    \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class => ['mail'],
    \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => ['mail'],
    \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class => ['mail'],
    \Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class => ['mail'],
    \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification::class => [],
    \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification::class => [],
],

'notifiable' => \App\Notifications\BackupNotifiable::class,
```

## The Magic: How It Works

The system works by overriding the `notify()` method in `BackupNotifiable`:

```php
public function notify($notification)
{
    // Log to the backup channel
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
```

This means:
- Every backup notification is automatically logged
- The BackupLogChannel extracts detailed information from the notification
- No need to modify individual notification classes
- Future Spatie notifications work automatically

## Log Structure

Each backup notification generates a structured log entry:

```json
{
    "timestamp": "2024-01-15T10:30:00.000000Z",
    "notification_type": "BackupWasSuccessfulNotification",
    "notification_class": "Spatie\\Backup\\Notifications\\Notifications\\BackupWasSuccessfulNotification",
    "notifiable_type": "App\\Notifications\\BackupNotifiable",
    "notifiable_id": "system",
    "metadata": {
        "memory_usage": 67108864,
        "php_version": "8.2.0",
        "laravel_version": "12.0"
    },
    "backup_info": {
        "backup_name": "laravel-backup",
        "disk_names": ["local", "s3_backup"],
        "file_size": 52428800,
        "file_path": "backup-2024-01-15.zip",
        "backup_date": "2024-01-15T10:30:00.000000Z",
        "total_storage_used": 367001600
    }
}
```

## Console Commands

### Configuring S3 Backup

The system includes an interactive S3 configuration tool:

```bash
# Configure S3 backup settings interactively
php artisan backup:config
```

**Features:**
- 🔍 **Show current configuration** with masked sensitive values
- ✏️ **Interactive updates** for all S3 environment variables
- 🧪 **Connection testing** with comprehensive validation
- 🔄 **Smart workflows** (configure → test, test only, etc.)
- 🛠️ **Troubleshooting** suggestions on failures

**S3 Environment Variables:**
```bash
AWS_ACCESS_KEY_ID_BACKUP=your_access_key
AWS_SECRET_ACCESS_KEY_BACKUP=your_secret_key
AWS_DEFAULT_REGION_BACKUP=us-east-1
AWS_BUCKET_BACKUP=your_bucket_name
AWS_URL_BACKUP=https://your-bucket.s3.region.amazonaws.com (optional)
AWS_ENDPOINT_BACKUP=https://s3.region.amazonaws.com (optional)
AWS_USE_PATH_STYLE_ENDPOINT_BACKUP=false (optional)
```

**Usage Examples:**

1. **First-time setup:**
   ```bash
   php artisan backup:config
   # Choose "Update configuration and then test"
   # Enter your AWS credentials when prompted
   # Command will test the connection automatically
   ```

2. **Test existing configuration:**
   ```bash
   php artisan backup:config
   # Choose "Test existing S3 connection"
   # Reviews current settings and runs connectivity tests
   ```

3. **Update specific values:**
   ```bash
   php artisan backup:config
   # Choose "Update S3 configuration"
   # Press Enter to keep current values, or enter new ones
   # Optionally test after updates
   ```

**Connection Test Includes:**
- ✅ S3 disk connectivity
- ✅ File upload/download operations
- ✅ File deletion capabilities  
- ✅ Directory access validation
- ✅ Optional backup run test

**Example Command Output:**
```
🔧 S3 Backup Configuration Tool

📋 Current S3 Backup Configuration:
+---------------------+---------------------------+---------------+
| Description         | Environment Variable      | Current Value |
+---------------------+---------------------------+---------------+
| AWS Access Key ID   | AWS_ACCESS_KEY_ID_BACKUP  | AKIA****FZ46  |
| AWS Secret Key      | AWS_SECRET_ACCESS_KEY_BACKUP | ****        |
| AWS Region          | AWS_DEFAULT_REGION_BACKUP | us-east-1     |
| AWS Bucket          | AWS_BUCKET_BACKUP         | ubxen         |
+---------------------+---------------------------+---------------+

🔍 Testing S3 Backup Connection...
📤 Uploading test file: backup-test-2024-01-15-10-30-15.txt
📥 Verifying file exists...
📄 Reading file content...
🗑️ Cleaning up test file...
📁 Testing backup directory access...

🎉 S3 Backup Connection Test: SUCCESS!
+-------------------+-----------+
| Test              | Status    |
+-------------------+-----------+
| S3 Disk Connection| ✅ Connected |
| File Upload       | ✅ Working   |
| File Download     | ✅ Working   |
| File Deletion     | ✅ Working   |
| Directory Access  | ✅ Working   |
+-------------------+-----------+
```

### Viewing Backup Logs

```bash
# View recent backup logs
php artisan backup:logs

# View logs from last 30 days
php artisan backup:logs --days=30

# Filter by log level
php artisan backup:logs --level=error

# Show only statistics
php artisan backup:logs --stats
```

### Testing the System

```bash
# Test successful backup notification
php artisan backup:test-notifications --type=success

# Test failed backup notification
php artisan backup:test-notifications --type=failed
```

### Analyzing Backup Statistics

The system includes a comprehensive `backup:stats` command to analyze backup logs:

#### Basic Usage

```bash
# View comprehensive statistics (default: last 30 days)
php artisan backup:stats

# View summary only
php artisan backup:stats --format=summary

# Analyze last 7 days
php artisan backup:stats --days=7

# JSON output
php artisan backup:stats --format=json

# Export to file
php artisan backup:stats --export=json
php artisan backup:stats --export=csv
```

#### Statistical Analysis Includes:

**📈 Overview**
- Total events count
- Success/failure breakdown
- Success and failure rates

**📋 Notification Types**
- Count and percentage of each notification type
- Last occurrence of each type
- Most common notification types

**⚡ Performance Metrics**
- Average and peak memory usage
- PHP and Laravel version info
- System performance insights

**❌ Failure Analysis**
- Total failure count
- Failure trend analysis (increasing/decreasing/stable)
- Recent failures with details
- Failure type breakdown

**🕐 Recent Activity**
- Last 10 backup events
- Event levels and timestamps
- Human-readable timeframes

#### Export Options

The command supports multiple export formats:

```bash
# Export comprehensive JSON
php artisan backup:stats --export=json
# Creates: storage/logs/backup-stats-YYYY-MM-DD-HH-MM-SS.json

# Export CSV summary  
php artisan backup:stats --export=csv
# Creates: storage/logs/backup-stats-YYYY-MM-DD-HH-MM-SS.csv
```

#### Command Options

```bash
--days=N          # Number of days to analyze (default: 30)
--format=FORMAT   # Output format: table, json, summary (default: table)
--export=FORMAT   # Export to file: csv, json
```

#### Example Output

**Summary Format:**
```
📊 Backup Statistics Summary

✅ Total Events: 14
🎯 Success Rate: 21.43%
❌ Failed Events: 5 (78.57%)
📈 Failure Trend: Increasing
💾 Average Memory: 29.86 MB
⚡ Peak Memory: 32.00 MB
📋 Most Common: BackupHasFailedNotification
```

**Table Format:**
Provides detailed tables for:
- Overview metrics
- Notification type breakdown
- Performance statistics
- Failure analysis
- Recent activity log

## Usage Examples

### Running a Backup

```bash
php artisan backup:run
```

The system automatically logs:
- Backup start/completion
- File details and metadata
- Performance metrics
- Any failures with exception details

### Monitoring Backup Health

```bash
php artisan backup:monitor
```

Automatically logs:
- Health check results
- Storage usage information
- Backup age validation

## Installation

1. **Copy the Files**:
   - `app/Notifications/BackupNotifiable.php`
   - `app/Notifications/Channels/BackupLogChannel.php`
   - `app/Providers/NotificationServiceProvider.php`

2. **Register the Provider** in `bootstrap/providers.php`:
   ```php
   App\Providers\NotificationServiceProvider::class,
   ```

3. **Add Logging Channel** to `config/logging.php`

4. **Update Backup Config** in `config/backup.php`:
   ```php
   'notifiable' => \App\Notifications\BackupNotifiable::class,
   ```

5. **Test the System**:
   ```bash
   php artisan backup:test-notifications
   ```

## Environment Variables

```env
# Backup logging configuration
LOG_BACKUP_LEVEL=debug
LOG_BACKUP_DAYS=60

# Email notifications
MAIL_FROM_ADDRESS=backup@yourdomain.com
MAIL_FROM_NAME="Backup System"
```
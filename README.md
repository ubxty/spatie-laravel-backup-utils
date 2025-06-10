# UBXTY Spatie Laravel Backup Utils

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ubxty/spatie-laravel-backup-utils.svg?style=flat-square)](https://packagist.org/packages/ubxty/spatie-laravel-backup-utils)
[![Total Downloads](https://img.shields.io/packagist/dt/ubxty/spatie-laravel-backup-utils.svg?style=flat-square)](https://packagist.org/packages/ubxty/spatie-laravel-backup-utils)

Enhanced utilities and notifications for [spatie/laravel-backup](https://github.com/spatie/laravel-backup) with comprehensive logging, S3 configuration, and statistical analysis.

## 🚀 Features

- **🔧 Unified Command Interface** - Single `backup:utils` command for all backup operations
- **📊 Advanced Analytics** - Comprehensive backup statistics and trend analysis
- **🔍 S3 Configuration & Testing** - Interactive S3 setup with connection validation
- **📝 Enhanced Logging** - Structured backup event logging with metadata
- **🔔 Smart Notifications** - Auto-enhanced notification system with detailed context
- **⚙️ Auto-Configuration** - Automatic setup of logging, filesystem, and notifications
- **🧪 Testing Tools** - Built-in connection testing and validation utilities

## 📋 Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- [spatie/laravel-backup](https://github.com/spatie/laravel-backup) 8.0 or higher

## 📦 Installation

Install the package via Composer:

```bash
composer require ubxty/spatie-laravel-backup-utils
```

The package will automatically register its service provider and commands.

### Quick Setup

Run the installation command for guided setup:

```bash
php artisan backup:utils install
```

This will:
- Publish configuration files
- Set up backup logging
- Configure S3 filesystem (if needed)
- Install spatie/laravel-backup (if not present)
- Guide you through S3 configuration

## 🎯 Usage

### Unified Command Interface

Access all backup utilities through a single command:

```bash
# Interactive menu with numbered options
php artisan backup:utils

# Direct actions
php artisan backup:utils stats          # View backup statistics
php artisan backup:utils config-s3      # Configure S3 settings
php artisan backup:utils logs           # View and analyze logs
php artisan backup:utils test           # Test notification system
php artisan backup:utils run            # Run backup with enhanced logging
php artisan backup:utils monitor        # Monitor backup health
php artisan backup:utils install        # Setup and configuration
```

**Interactive Menu Features:**
- 🎯 **Numbered Options (1-9)** - Simply type a number to select an action
- 🔄 **Smart Navigation** - Return to menu after each action completes
- 📋 **System Status** - Real-time status check of backup components
- 💡 **Contextual Tips** - Random helpful tips displayed with each menu
- ✨ **Visual Feedback** - Beautiful execution boxes with timing information
- 🧹 **Clean Interface** - Screen clearing and consistent styling

The interactive menu displays:
```
╭─────────────────────────────────────────────────────────────╮
│               🔧 UBXTY Backup Utils Dashboard              │
│              Enhanced Laravel Backup Management            │
│                     by Ubxty v1.0.0                        │
╰─────────────────────────────────────────────────────────────╯

🎯 Available Actions:

  [1] 📊 Backup Statistics & Analytics
      View comprehensive backup statistics, trends, and performance metrics

  [2] 🔧 S3 Configuration & Testing  
      Configure and test S3 backup settings interactively

  [3] 🚀 Run Backup
      Execute backup with enhanced logging and notifications

  [4] 🔍 Monitor Backup Health
      Check backup health and generate monitoring reports

  [5] 📋 List Backups
      Display all available backups with detailed information

  [6] 🧹 Clean Old Backups
      Remove old backups according to retention policies

  [7] 🧪 Test Notifications
      Test backup notification system and logging

  [8] 📜 View Backup Logs
      View and analyze backup notification logs

  [9] ⚙️ Install & Configure
      Set up backup utilities with guided configuration

  [0] 🚪 Exit

💡 Tip: Start with "Install & Configure" if this is your first time

👉 Please enter your choice (0-9) [1]:
```

### Backup Statistics & Analytics

View comprehensive backup statistics:

```bash
# Default view (last 30 days)
php artisan backup:stats

# Custom time period
php artisan backup:stats --days=7

# Different output formats
php artisan backup:stats --format=summary
php artisan backup:stats --format=json

# Export reports
php artisan backup:stats --export=json
php artisan backup:stats --export=csv
```

**Statistics Include:**
- Success/failure rates and trends
- Performance metrics (memory usage, execution time)
- Notification type breakdown
- Recent activity timeline
- Failure analysis with troubleshooting suggestions

### S3 Configuration & Testing

Interactive S3 backup configuration:

```bash
php artisan backup:config
```

**Features:**
- 🔍 Show current configuration with masked sensitive values
- ✏️ Interactive prompts for all S3 settings
- 🧪 Comprehensive connection testing
- 🛠️ Troubleshooting suggestions
- 🔄 Smart workflows (configure → test)

**Connection Testing:**
- S3 disk connectivity
- File upload/download operations
- Directory access validation
- Optional backup run test

### Backup Log Analysis

View and analyze backup logs with detailed filtering:

```bash
# View recent backup logs (default: last 7 days)
php artisan backup:logs

# Filter by time period
php artisan backup:logs --days=30

# Filter by log level
php artisan backup:logs --level=error

# Show only statistics
php artisan backup:logs --stats

# Filter by backup event type
php artisan backup:logs --event=BackupWasSuccessful

# Limit number of entries
php artisan backup:logs --tail=100
```

**Log Analysis Features:**
- 📜 Automatic log file detection (daily rotation support)
- 🔍 Advanced filtering by level, event type, and time period
- 📊 Statistical analysis of backup events
- 📋 Formatted table output with color-coded log levels
- 🕐 Human-readable timestamps and event summaries

## ⚙️ Configuration

### Environment Variables

The package uses these environment variables:

```bash
# S3 Backup Configuration (Required)
AWS_ACCESS_KEY_ID_BACKUP=your_access_key
AWS_SECRET_ACCESS_KEY_BACKUP=your_secret_key
AWS_DEFAULT_REGION_BACKUP=us-east-1
AWS_BUCKET_BACKUP=your_bucket_name

# Optional S3 Settings
AWS_URL_BACKUP=https://your-bucket.s3.region.amazonaws.com
AWS_ENDPOINT_BACKUP=https://s3.region.amazonaws.com
AWS_USE_PATH_STYLE_ENDPOINT_BACKUP=false

# Backup Archive Encryption
BACKUP_ARCHIVE_PASSWORD=your_secure_password

# UBXTY Backup Utils Settings
BACKUP_LOG_LEVEL=debug
BACKUP_LOG_DAYS=60
BACKUP_STATS_DEFAULT_DAYS=30
```

### Auto-Configuration

The package automatically configures:

1. **Backup Logging Channel** - Creates `backup` log channel
2. **S3 Filesystem Disk** - Sets up `s3_backup` disk
3. **Enhanced Notifications** - Adds logging to all backup notifications
4. **Backup Notifiable** - Uses enhanced notifiable class

To disable auto-configuration:

```bash
BACKUP_UTILS_AUTO_LOGGING=false
BACKUP_UTILS_AUTO_FILESYSTEM=false
BACKUP_UTILS_AUTO_NOTIFICATIONS=false
```

### Manual Configuration

Publish configuration files for manual setup:

```bash
# Publish main config
php artisan vendor:publish --tag=backup-utils-config

# Publish environment template
php artisan vendor:publish --tag=backup-utils-env
```

## 🔐 Backup Encryption

The package supports password-based encryption for backup archives:

### Setting Up Encryption

1. **Environment Variable Method** (Recommended):
```bash
BACKUP_ARCHIVE_PASSWORD=your_secure_password_here
```

2. **Interactive Configuration**:
```bash
php artisan backup:config
# Select "Update S3 configuration" and set the archive password
```

3. **Direct Configuration**:
```php
// config/backup.php
'backup' => [
    'destination' => [
        'password' => env('BACKUP_ARCHIVE_PASSWORD'),
        'encryption' => 'default', // Uses AES-256 when available
    ],
],
```

### Encryption Features

- **AES-256 Encryption**: Uses strong encryption when available on your system
- **Password Protection**: All backup archives are encrypted with your password
- **Secure Input**: Password is entered securely (hidden input) during configuration
- **Optional**: Encryption can be disabled by leaving password empty
- **Cross-Platform**: Works with standard ZIP encryption

### Security Best Practices

- Use a strong, unique password for backup encryption
- Store the password securely (e.g., password manager)
- Consider rotating encryption passwords periodically
- Test backup restoration with encrypted archives
- Keep password separate from backup storage location

### Testing Encrypted Backups

```bash
# Test backup creation with encryption
php artisan backup:run

# Verify encrypted backup can be accessed
php artisan backup:list

# Test S3 upload of encrypted backups
php artisan backup:config
# Choose "Update configuration and then test"
```

## 📝 Enhanced Logging

The package provides structured logging for all backup events:

```json
{
  "timestamp": "2024-01-15T10:30:00.000000Z",
  "notification_type": "BackupWasSuccessfulNotification",
  "notifiable_type": "BackupNotifiable",
  "metadata": {
    "memory_usage": 67108864,
    "php_version": "8.2.0",
    "laravel_version": "12.0"
  },
  "backup_info": {
    "backup_name": "laravel-backup",
    "disk_names": ["local", "s3_backup"],
    "file_size": 52428800,
    "backup_date": "2024-01-15T10:30:00.000000Z"
  }
}
```

Logs are automatically written to `storage/logs/backup.log` with daily rotation.

## 🔔 Enhanced Notifications

The package enhances all Spatie backup notifications with:

- **Structured logging** of notification events
- **Metadata extraction** (memory usage, system info)
- **Backup information** (file sizes, disk names, dates)
- **Exception details** for failed operations
- **Performance metrics** for monitoring

## 📊 Statistics Dashboard

The `backup:stats` command provides comprehensive analytics:

### Overview Metrics
- Total backup events
- Success/failure rates
- Performance trends

### Detailed Analysis
- Notification type breakdown
- Time-based patterns (daily/hourly)
- Failure analysis with trends
- Recent activity timeline

### Export Options
- JSON format for APIs
- CSV format for spreadsheets
- Timestamped files for history

## 🧪 Testing

The package includes comprehensive testing utilities:

### S3 Connection Testing
```bash
php artisan backup:config
# Choose "Test existing S3 connection"
```

### Notification Testing
```bash
php artisan backup:test-notifications --type=success
php artisan backup:test-notifications --type=failed
```

## 🔧 Advanced Usage

### Custom Notifiable Class

To use a custom notifiable class:

```php
// config/backup-utils.php
'notifications' => [
    'notifiable_class' => App\Notifications\CustomBackupNotifiable::class,
],
```

### Custom Log Channel

Configure a custom backup log channel:

```php
// config/logging.php
'channels' => [
    'backup' => [
        'driver' => 'daily',
        'path' => storage_path('logs/backup.log'),
        'level' => 'debug',
        'days' => 60,
    ],
],
```

### Statistics Caching

Configure statistics caching:

```bash
BACKUP_STATS_CACHE_DURATION=300  # 5 minutes
```

## 🛠️ Troubleshooting

### Common Issues

**S3 Connection Failures:**
- Verify AWS credentials are correct
- Check bucket exists and region matches
- Ensure proper S3 permissions
- Validate bucket naming conventions

**Missing Logs:**
- Check `storage/logs/` directory permissions
- Verify backup log channel configuration
- Ensure notifications are properly configured

**Command Not Found:**
- Clear configuration cache: `php artisan config:clear`
- Verify package is properly installed
- Check service provider registration

### Debug Mode

Enable verbose output for troubleshooting:

```bash
php artisan backup:utils stats -vvv
php artisan backup:config -vvv
```

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 🤝 Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## 🔒 Security

If you discover any security related issues, please email security@ubxty.com instead of using the issue tracker.

## 📞 Support

- 📧 Email: info@ubxty.com  
- 🐛 Issues: [GitHub Issues](https://github.com/ubxty/spatie-laravel-backup-utils/issues)
- 📖 Documentation: [GitHub Wiki](https://github.com/ubxty/spatie-laravel-backup-utils/wiki)
- 🌐 Website: [UBXTY Unboxing Technology](https://ubxty.com)

---

**Built with ❤️ by [Ravdeep Singh](https://www.linkedin.com/in/ravdeep-singh-a4544abb/) • [UBXTY Unboxing Technology](https://ubxty.com)**

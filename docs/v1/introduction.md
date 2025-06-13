---
title: Introduction
order: 1
permalink: /v1/introduction/
---

# Introduction

Laravel Backup Utils is a powerful command-line utility package that extends and enhances the functionality of [spatie/laravel-backup](https://github.com/spatie/laravel-backup). This package provides additional CLI commands, utilities, and monitoring tools to make backup management more powerful, flexible, and developer-friendly.

## About

This package is developed and maintained by [Ravdeep Singh](https://www.linkedin.com/in/ravdeep-singh-a4544abb/) at [Ubxty](https://ubxty.com/home), a leading development company specializing in Laravel applications and custom software solutions.

## What is Laravel Backup Utils?

Laravel Backup Utils builds upon the excellent foundation provided by [Spatie's Laravel Backup package](https://github.com/spatie/laravel-backup) by adding:

- **Enhanced CLI Commands**: Additional artisan commands for backup management
- **Advanced Monitoring Tools**: Command-line utilities for backup health monitoring  
- **Backup Analytics**: CLI tools to analyze and report on your backup system
- **Configuration Management**: Command-line helpers for backup configuration
- **Extended Notifications**: Enhanced notification commands and channels

## Core Features

### CLI-First Approach
- **Artisan Commands**: Comprehensive set of backup management commands
- **Interactive Setup**: Command-line wizards for configuration
- **Batch Operations**: Bulk backup operations via CLI
- **Monitoring Commands**: Health check and status commands
- **Reporting Tools**: Generate backup reports from command line

### Enhanced Backup Management
- **Custom Backup Strategies**: Implement and manage custom backup strategies via CLI
- **Advanced Scheduling**: Enhanced scheduling options for backup commands
- **Multi-Environment Support**: CLI tools for managing backups across environments

### Monitoring & Analytics
- **Health Monitoring**: Command-line backup health checks
- **Performance Analytics**: CLI tools for backup performance analysis
- **Storage Analytics**: Disk usage and storage optimization commands
- **Alert Management**: Configure and manage backup alerts via CLI

## Requirements

- PHP 8.0 or higher
- Laravel 8.0 or higher
- [spatie/laravel-backup](https://github.com/spatie/laravel-backup) package installed and configured

## Installation

You can install the package via composer:

```bash
composer require ubxty/spatie-laravel-backup-utils
```

After installing the package, you can publish the configuration file:

```bash
php artisan vendor:publish --provider="Ubxty\LaravelBackupUtils\LaravelBackupUtilsServiceProvider"
```

## Quick Start - CLI Commands

Once installed, you can start using the enhanced CLI commands. Here are some examples:

### Backup Statistics
```bash
# Get comprehensive backup statistics
php artisan backup:stats

# Get backup statistics for specific disk
php artisan backup:stats --disk=s3

# Get statistics in JSON format
php artisan backup:stats --format=json
```

### Health Monitoring
```bash
# Check backup health status
php artisan backup:health

# Run detailed health checks
php artisan backup:health --detailed

# Monitor specific backup destination
php artisan backup:health --destination=s3
```

### Configuration Management
```bash
# Validate backup configuration
php artisan backup:config --validate

# Show current backup configuration
php artisan backup:config --show

# Test backup destinations
php artisan backup:config --test-destinations
```

### Analytics and Reporting
```bash
# Generate backup analytics report
php artisan backup:analytics

# Export backup logs
php artisan backup:logs --export

# Cleanup old backup files
php artisan backup:cleanup --interactive
```

## Integration with Spatie Laravel Backup

This package is designed to work seamlessly with [spatie/laravel-backup](https://github.com/spatie/laravel-backup). It does not replace the original package but rather extends its functionality with additional CLI tools and utilities.

**Prerequisites**: You must have [Spatie's Laravel Backup](https://spatie.be/docs/laravel-backup) installed and configured before using Laravel Backup Utils.

**Documentation**: For basic backup functionality, refer to the [official Spatie Laravel Backup documentation](https://spatie.be/docs/laravel-backup).

## Support

If you find any bugs or have any questions, please open an issue on our [GitHub repository](https://github.com/ubxty/spatie-laravel-backup-utils).

For professional support and custom development services, please visit [Ubxty](https://ubxty.com/home).

## Contributing

We welcome contributions! Please see our [contributing guide](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email security@ubxty.com instead of using the issue tracker.

## Credits

- [Ubxty](https://ubxty.com/home)
- [Ravdeep Singh](https://www.linkedin.com/in/ravdeep-singh-a4544abb/)
- [Spatie Laravel Backup](https://github.com/spatie/laravel-backup) - The foundation this package builds upon
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information. 
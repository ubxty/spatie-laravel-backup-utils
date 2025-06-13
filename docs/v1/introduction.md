---
title: Introduction
order: 1
permalink: /v1/introduction/
---

# Introduction

Laravel Backup Utils is a powerful package that provides additional utilities and tools for working with [spatie/laravel-backup](https://github.com/spatie/laravel-backup). This package extends the functionality of the base backup package with additional features and utilities to make backup management even more powerful and flexible.

## About

This package is developed and maintained by [Ravdeep Singh](https://www.linkedin.com/in/ravdeep-singh-a4544abb/) at [Ubxty](https://ubxty.com/home), a leading development company specializing in Laravel applications and custom software solutions.

## Features

- **Enhanced Backup Management**: Additional commands and utilities for managing your Laravel backups
- **Advanced Monitoring**: Extended monitoring capabilities for your backup system
- **Custom Backup Strategies**: Tools to implement custom backup strategies
- **Improved Notifications**: Enhanced notification system for backup events
- **Backup Analytics**: Tools to analyze and report on your backup system

## Requirements

- PHP 8.0 or higher
- Laravel 8.0 or higher
- [spatie/laravel-backup](https://github.com/spatie/laravel-backup) package installed

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-backup-utils
```

After installing the package, you can publish the configuration file:

```bash
php artisan vendor:publish --provider="Spatie\BackupUtils\BackupUtilsServiceProvider"
```

## Quick Start

Once installed, you can start using the package's features. Here's a quick example of how to use some of the basic features:

```php
use Spatie\BackupUtils\BackupUtils;

// Get backup statistics
$stats = BackupUtils::getBackupStats();

// Monitor backup health
$health = BackupUtils::checkBackupHealth();

// Get backup analytics
$analytics = BackupUtils::getBackupAnalytics();
```

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
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information. 
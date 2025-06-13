---
layout: default
title: Laravel Backup Utils Documentation
description: A modern backup solution for Laravel applications
permalink: /
---

# Welcome to Laravel Backup Utils Documentation

Laravel Backup Utils is a powerful package that enhances the functionality of [spatie/laravel-backup](https://github.com/spatie/laravel-backup) by providing additional utilities and features for managing your Laravel application backups.

## Features

- Enhanced backup notifications
- Backup statistics and monitoring
- Backup logs management
- S3 configuration management
- Customizable backup channels
- And much more!

## Quick Links

- [Introduction]({{ '/v1/introduction/' | relative_url }})
- [Installation]({{ '/v1/installation/' | relative_url }})
- [Configuration]({{ '/v1/configuration/' | relative_url }})
- [Usage]({{ '/v1/usage/' | relative_url }})

## Getting Started

To get started with Laravel Backup Utils, you can install it via Composer:

```bash
composer require ubxty/spatie-laravel-backup-utils
```

Then, publish the configuration file:

```bash
php artisan vendor:publish --provider="Ubxty\LaravelBackupUtils\LaravelBackupUtilsServiceProvider"
```

## About

Laravel Backup Utils is developed and maintained by [Ravdeep Singh](https://www.linkedin.com/in/ravdeep-singh-ubxty/) at [Ubxty](https://ubxty.com).

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). 
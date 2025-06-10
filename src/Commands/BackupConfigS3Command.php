<?php

namespace Ubxty\SpatieLaravelBackupUtils\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Exception;

/**
 * Interactive S3 backup configuration and testing command.
 *
 * Provides comprehensive S3 setup including credential configuration,
 * connection testing, and backup archive password management.
 *
 * @package Ubxty\SpatieLaravelBackupUtils
 * @author  Ravdeep Singh <info@ubxty.com>
 * @author  UBXTY Unboxing Technology <info@ubxty.com>
 * @license MIT
 * @version 1.0.1
 */
class BackupConfigS3Command extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backup:config';

    /**
     * The console command description.
     */
    protected $description = 'Configure backup settings (S3, encryption) and test connections interactively';

    /**
     * S3 backup environment variables mapping.
     */
    private array $s3BackupVars = [
        'AWS_ACCESS_KEY_ID_BACKUP' => 'AWS Access Key ID',
        'AWS_SECRET_ACCESS_KEY_BACKUP' => 'AWS Secret Access Key',
        'AWS_DEFAULT_REGION_BACKUP' => 'AWS Default Region',
        'AWS_BUCKET_BACKUP' => 'AWS Bucket Name',
        'AWS_URL_BACKUP' => 'AWS URL (optional)',
        'AWS_ENDPOINT_BACKUP' => 'AWS Endpoint (optional)',
        'AWS_USE_PATH_STYLE_ENDPOINT_BACKUP' => 'Use Path Style Endpoint (true/false, optional)',
        'BACKUP_ARCHIVE_PASSWORD' => 'Backup Archive Password (optional, for encryption)',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 S3 Backup Configuration Tool');
        $this->line('');

        // Show current configuration
        $this->showCurrentConfig();

        // Ask user what they want to do
        $choice = $this->choice(
            'What would you like to do?',
            [
                'test' => 'Test existing S3 connection',
                'update' => 'Update S3 configuration',
                'both' => 'Update configuration and then test',
                'debug' => 'Show detailed configuration debugging',
                'exit' => 'Exit without changes'
            ],
            'both'
        );

        switch ($choice) {
            case 'test':
                return $this->testS3Connection();
                
            case 'update':
                $this->updateS3Config();
                if ($this->confirm('Would you like to test the connection now?', true)) {
                    return $this->testS3Connection();
                }
                break;
                
            case 'both':
                $this->updateS3Config();
                return $this->testS3Connection();

            case 'debug':
                return $this->showConfigDebug();
                
            case 'exit':
                $this->info('👋 Exiting without changes.');
                return 0;
        }

        return 0;
    }

    /**
     * Show current S3 backup configuration.
     */
    private function showCurrentConfig(): void
    {
        $this->info('📋 Current S3 Backup Configuration:');
        $this->line('');

        $envPath = base_path('.env');
        $envContent = File::exists($envPath) ? File::get($envPath) : '';

        $table = [];
        foreach ($this->s3BackupVars as $key => $description) {
            $value = $this->getEnvValue($key, $envContent);
            $displayValue = $this->maskSensitiveValue($key, $value);
            
            $table[] = [
                $description,
                $key,
                $displayValue ?: '<not set>',
            ];
        }

        $this->table(['Description', 'Environment Variable', 'Current Value'], $table);
        $this->line('');
    }

    /**
     * Update S3 configuration interactively.
     */
    private function updateS3Config(): void
    {
        $this->info('✏️ Updating S3 Backup Configuration');
        $this->line('');
        $this->comment('Enter new values (press Enter to keep current value):');
        $this->line('');

        $envPath = base_path('.env');
        $envContent = File::exists($envPath) ? File::get($envPath) : '';
        $updates = [];

        foreach ($this->s3BackupVars as $key => $description) {
            $currentValue = $this->getEnvValue($key, $envContent);
            $displayCurrent = $this->maskSensitiveValue($key, $currentValue);
            
            $prompt = "🔹 {$description}";
            if ($currentValue) {
                $prompt .= " (current: {$displayCurrent})";
            }
            
            // Handle boolean values specially
            if ($key === 'AWS_USE_PATH_STYLE_ENDPOINT_BACKUP') {
                if ($currentValue) {
                    $newValue = $this->choice($prompt, ['true', 'false', 'keep current'], 'keep current');
                    if ($newValue !== 'keep current') {
                        $updates[$key] = $newValue;
                    }
                } else {
                    $newValue = $this->choice($prompt, ['true', 'false', 'skip'], 'skip');
                    if ($newValue !== 'skip') {
                        $updates[$key] = $newValue;
                    }
                }
            } elseif ($key === 'BACKUP_ARCHIVE_PASSWORD') {
                // Handle backup password specially with secret input
                if (!$currentValue) {
                    $prompt .= " (optional - press Enter to skip)";
                }
                
                $newValue = $this->secret($prompt);
                
                if ($newValue !== null && $newValue !== '') {
                    $updates[$key] = $newValue;
                } elseif ($newValue === '' && $currentValue) {
                    // Allow clearing the password by entering empty value
                    if ($this->confirm('Remove the current backup archive password?', false)) {
                        $updates[$key] = '';
                    }
                }
            } else {
                // Handle optional fields
                $isOptional = in_array($key, ['AWS_URL_BACKUP', 'AWS_ENDPOINT_BACKUP', 'BACKUP_ARCHIVE_PASSWORD']);
                
                if ($isOptional && !$currentValue) {
                    $prompt .= " (optional - press Enter to skip)";
                }
                
                $newValue = $this->ask($prompt);
                
                if ($newValue !== null && $newValue !== '') {
                    $updates[$key] = $newValue;
                }
            }
        }

        if (empty($updates)) {
            $this->warn('⚠️ No changes made to configuration.');
            return;
        }

        // Show summary of changes
        $this->line('');
        $this->info('📝 Summary of changes:');
        foreach ($updates as $key => $value) {
            $maskedValue = $this->maskSensitiveValue($key, $value);
            $this->line("  • {$key} = {$maskedValue}");
        }

        if (!$this->confirm('💾 Apply these changes to .env file?', true)) {
            $this->warn('⚠️ Changes cancelled.');
            return;
        }

        // Apply updates
        $this->updateEnvFile($updates);
        $this->info('✅ S3 backup configuration updated successfully!');
        $this->line('');
    }

    /**
     * Test S3 connection.
     */
    private function testS3Connection(): int
    {
        $this->info('🔍 Testing S3 Backup Connection...');
        $this->line('');

        try {
            // Clear config cache to ensure we use updated values
            $this->comment('Clearing configuration cache...');
            Artisan::call('config:clear');
            
            $this->line('');
            $this->info('Testing S3 backup disk connectivity...');

            // Test basic connectivity
            $disk = Storage::disk('s3_backup');
            
            // Create a test file
            $testFileName = 'backup-test-' . now()->format('Y-m-d-H-i-s') . '.txt';
            $testContent = 'S3 Backup Test - ' . now()->toDateTimeString();
            
            $this->comment("📤 Uploading test file: {$testFileName}");
            $disk->put($testFileName, $testContent);
            
            $this->comment("📥 Verifying file exists...");
            if (!$disk->exists($testFileName)) {
                throw new Exception('Test file was not found after upload');
            }
            
            $this->comment("📄 Reading file content...");
            $retrievedContent = $disk->get($testFileName);
            if ($retrievedContent !== $testContent) {
                throw new Exception('Retrieved content does not match uploaded content');
            }
            
            $this->comment("🗑️ Cleaning up test file...");
            $disk->delete($testFileName);
            
            // Test backup directory structure
            $this->comment("📁 Testing backup directory access...");
            $backupPath = config('backup.backup.destination.disks', []);
            if (in_array('s3_backup', $backupPath)) {
                // Try to list files in root
                $files = $disk->files();
                $this->comment("✅ Can access backup directory (found " . count($files) . " files)");
            }

            $this->line('');
            $this->info('🎉 S3 Backup Connection Test: SUCCESS!');
            $this->line('');
            $this->table(['Test', 'Status'], [
                ['S3 Disk Connection', '✅ Connected'],
                ['File Upload', '✅ Working'],
                ['File Download', '✅ Working'],
                ['File Deletion', '✅ Working'],
                ['Directory Access', '✅ Working'],
            ]);
            
            // Suggest running a backup test
            $this->line('');
            if ($this->confirm('🚀 Would you like to run a backup test to S3?', false)) {
                $this->info('Running backup test...');
                $this->line('');
                
                try {
                    // Clear any cached config first
                    Artisan::call('config:clear');
                    
                    // Run the backup with verbose output
                    $exitCode = Artisan::call('backup:run', [
                        '--only-to-disk' => 's3_backup',
                        '--disable-notifications' => true,
                    ]);
                    
                    // Get the output from the backup command
                    $output = Artisan::output();
                    
                    if ($exitCode === 0) {
                        $this->info('✅ Backup test completed successfully!');
                        $this->line('');
                        $this->comment('Backup output:');
                        $this->line($output);
                    } else {
                        $this->error('❌ Backup test failed. Exit code: ' . $exitCode);
                        $this->line('');
                        $this->comment('Backup output:');
                        $this->line($output);
                        
                        // Additional debugging
                        $this->line('');
                        $this->warn('🔍 Debugging information:');
                        $this->line('  • Check storage/logs/laravel.log for detailed error messages');
                        $this->line('  • Verify S3 backup disk is properly configured in config/backup.php');
                        $this->line('  • Ensure s3_backup disk exists in config/filesystems.php');
                        
                        return 1;
                    }
                } catch (\Exception $e) {
                    $this->error('❌ Backup test failed with exception: ' . $e->getMessage());
                    $this->line('');
                    $this->warn('🔍 Debugging information:');
                    $this->line('  • Exception: ' . get_class($e));
                    $this->line('  • File: ' . $e->getFile() . ':' . $e->getLine());
                    $this->line('  • Check storage/logs/laravel.log for detailed error messages');
                    
                    return 1;
                }
            }

            return 0;

        } catch (Exception $e) {
            $this->line('');
            $this->error('❌ S3 Connection Test FAILED!');
            $this->line('');
            $this->error('Error: ' . $e->getMessage());
            
            // Provide troubleshooting suggestions
            $this->line('');
            $this->warn('🛠️ Troubleshooting suggestions:');
            $this->line('  • Verify your AWS credentials are correct');
            $this->line('  • Check that the bucket exists and is in the correct region');
            $this->line('  • Ensure your AWS user has proper S3 permissions');
            $this->line('  • Verify the region matches your bucket location');
            $this->line('  • Check if bucket name follows AWS naming conventions');
            
            // Offer to update config
            $this->line('');
            if ($this->confirm('🔧 Would you like to update the S3 configuration?', true)) {
                $this->updateS3Config();
                if ($this->confirm('🔍 Test connection again?', true)) {
                    return $this->testS3Connection();
                }
            }

            return 1;
        }
    }

    /**
     * Get environment variable value from .env content.
     */
    private function getEnvValue(string $key, string $envContent): ?string
    {
        if (preg_match("/^{$key}=(.*)$/m", $envContent, $matches)) {
            $value = trim($matches[1]);
            // Remove quotes if present
            return trim($value, '"\'');
        }
        
        return env($key);
    }

    /**
     * Mask sensitive values for display.
     */
    private function maskSensitiveValue(string $key, ?string $value): string
    {
        if (!$value) {
            return '';
        }

        if (in_array($key, ['AWS_ACCESS_KEY_ID_BACKUP', 'AWS_SECRET_ACCESS_KEY_BACKUP', 'BACKUP_ARCHIVE_PASSWORD'])) {
            if (strlen($value) <= 8) {
                return str_repeat('*', strlen($value));
            }
            return substr($value, 0, 4) . str_repeat('*', strlen($value) - 8) . substr($value, -4);
        }

        return $value;
    }

    /**
     * Update .env file with new values.
     */
    private function updateEnvFile(array $updates): void
    {
        $envPath = base_path('.env');
        $envContent = File::exists($envPath) ? File::get($envPath) : '';

        foreach ($updates as $key => $value) {
            // Escape value if it contains spaces or special characters
            $escapedValue = $this->escapeEnvValue($value);
            
            if (preg_match("/^{$key}=.*$/m", $envContent)) {
                // Update existing value
                $envContent = preg_replace("/^{$key}=.*$/m", "{$key}={$escapedValue}", $envContent);
            } else {
                // Add new value at the end
                $envContent = rtrim($envContent) . "\n{$key}={$escapedValue}\n";
            }
        }

        File::put($envPath, $envContent);
    }

    /**
     * Escape environment variable value.
     */
    private function escapeEnvValue(string $value): string
    {
        // If value contains spaces, special characters, or is empty, wrap in quotes
        if (empty($value) || preg_match('/[\s#"\'\\\\]/', $value)) {
            return '"' . addslashes($value) . '"';
        }
        
        return $value;
    }

    /**
     * Show detailed configuration debugging information.
     */
    private function showConfigDebug(): int
    {
        $this->info('🔍 Configuration Debugging Information');
        $this->line('');

        // Check if s3_backup disk exists in filesystems config
        $this->comment('📁 Checking filesystem configuration...');
        $s3BackupDisk = config('filesystems.disks.s3_backup');
        if ($s3BackupDisk) {
            $this->info('✅ s3_backup disk found in config/filesystems.php');
            $this->table(['Key', 'Value'], [
                ['Driver', $s3BackupDisk['driver'] ?? 'not set'],
                ['Key', isset($s3BackupDisk['key']) ? $this->maskSensitiveValue('AWS_ACCESS_KEY_ID_BACKUP', $s3BackupDisk['key']) : 'not set'],
                ['Secret', isset($s3BackupDisk['secret']) ? '***masked***' : 'not set'],
                ['Region', $s3BackupDisk['region'] ?? 'not set'],
                ['Bucket', $s3BackupDisk['bucket'] ?? 'not set'],
            ]);
        } else {
            $this->error('❌ s3_backup disk not found in config/filesystems.php');
        }

        $this->line('');

        // Check backup configuration
        $this->comment('📋 Checking backup configuration...');
        $backupDisks = config('backup.backup.destination.disks', []);
        if (in_array('s3_backup', $backupDisks)) {
            $this->info('✅ s3_backup disk found in backup destination disks');
        } else {
            $this->error('❌ s3_backup disk not found in backup destination disks');
            $this->comment('Current backup disks: ' . implode(', ', $backupDisks));
        }

        $this->line('');

        // Check notification configuration
        $this->comment('🔔 Checking notification configuration...');
        $notifiable = config('backup.notifications.notifiable');
        if ($notifiable === '\Ubxty\SpatieLaravelBackupUtils\Notifications\BackupNotifiable') {
            $this->info('✅ Enhanced BackupNotifiable class is configured');
        } else {
            $this->warn('⚠️ Default notifiable class in use: ' . ($notifiable ?? 'not set'));
        }

        // Check if backup_log channel exists
        $backupLogChannel = config('logging.channels.backup');
        if ($backupLogChannel) {
            $this->info('✅ backup logging channel is configured');
        } else {
            $this->warn('⚠️ backup logging channel not found');
        }

        $this->line('');

        // Environment variables check
        $this->comment('🔧 Environment variables check...');
        $envErrors = [];
        $requiredVars = ['AWS_ACCESS_KEY_ID_BACKUP', 'AWS_SECRET_ACCESS_KEY_BACKUP', 'AWS_DEFAULT_REGION_BACKUP', 'AWS_BUCKET_BACKUP'];
        
        foreach ($requiredVars as $var) {
            $value = env($var);
            if (!$value) {
                $envErrors[] = $var;
            }
        }

        if (empty($envErrors)) {
            $this->info('✅ All required environment variables are set');
        } else {
            $this->error('❌ Missing required environment variables:');
            foreach ($envErrors as $var) {
                $this->line("   • {$var}");
            }
        }

        $this->line('');

        // Try to test S3 connection
        if (empty($envErrors) && $s3BackupDisk) {
            if ($this->confirm('🧪 Run S3 connection test?', true)) {
                return $this->testS3Connection();
            }
        } else {
            $this->warn('⚠️ Cannot test S3 connection due to configuration issues above');
            if ($this->confirm('🔧 Would you like to update the configuration?', true)) {
                $this->updateS3Config();
                return $this->showConfigDebug();
            }
        }

        return 0;
    }
} 
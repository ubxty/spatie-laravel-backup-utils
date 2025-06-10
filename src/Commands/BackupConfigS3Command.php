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
 * @version 1.0.0
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
                
                $exitCode = Artisan::call('backup:run', [
                    '--only-to-disk' => 's3_backup',
                    '--disable-notifications' => true,
                ]);
                
                if ($exitCode === 0) {
                    $this->info('✅ Backup test completed successfully!');
                } else {
                    $this->error('❌ Backup test failed. Check the logs for details.');
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
} 
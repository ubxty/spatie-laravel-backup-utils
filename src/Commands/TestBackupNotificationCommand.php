<?php

namespace Ubxty\SpatieLaravelBackupUtils\Commands;

use Illuminate\Console\Command;
use Ubxty\LaravelBackupUtils\Notifications\BackupNotifiable;
use Illuminate\Notifications\Notification;
use Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification;
use Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification;

/**
 * Command for testing backup notification system functionality.
 *
 * Allows testing of successful and failed backup notifications
 * to verify the notification and logging system works correctly.
 *
 * @package Ubxty\SpatieLaravelBackupUtils
 * @author  Ravdeep Singh <info@ubxty.com>
 * @author  UBXTY Unboxing Technology <info@ubxty.com>
 * @license MIT
 * @version 1.0.1
 */
class TestBackupNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backup:test-notifications 
                            {--type=success : Type of notification to test (success, failed)}';

    /**
     * The console command description.
     */
    protected $description = 'Test the backup notification system';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');

        $this->info("Testing backup notification system...");
        $this->info("Type: {$type}");
        $this->line('');

        try {
            // Test container resolution first
            $this->info('Testing container resolution...');
            $notifiable = app(\Ubxty\LaravelBackupUtils\Notifications\BackupNotifiable::class);
            $this->info('Container resolution successful! Class: ' . get_class($notifiable));
            
            switch ($type) {
                case 'success':
                    $this->testSuccessNotification($notifiable);
                    break;
                    
                case 'failed':
                    $this->testFailedNotification($notifiable);
                    break;
                    
                default:
                    $this->error("Invalid notification type: {$type}");
                    return 1;
            }

            $this->info('✅ Test notification sent successfully!');
            $this->line('');
            $this->info('Check the following to verify:');
            $this->line('- Backup log file: storage/logs/backup.log');
            $this->line('- Email inbox for configured recipient (if mail is configured)');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Test successful backup notification.
     */
    private function testSuccessNotification(BackupNotifiable $notifiable): void
    {
        $this->info('Creating test successful backup notification...');
        
        // Create a simple test notification
        $notification = new class extends Notification {
            public function via($notifiable) {
                return ['backup_log'];
            }
            
            public function getMessage(): string {
                return 'Test backup completed successfully';
            }
        };
        
        // Send the notification
        $notifiable->notify($notification);
        
        $this->info('✓ Successful backup notification sent');
    }

    /**
     * Test failed backup notification.
     */
    private function testFailedNotification(BackupNotifiable $notifiable): void
    {
        $this->info('Creating test failed backup notification...');
        
        // Create a simple test notification with mock event
        $notification = new class extends Notification {
            public $event;
            
            public function __construct() {
                $this->event = new class {
                    public $exception;
                    
                    public function __construct() {
                        $this->exception = new \Exception('Test backup failure for notification system');
                    }
                };
            }
            
            public function via($notifiable) {
                return ['backup_log'];
            }
            
            public function getMessage(): string {
                return 'Test backup failed';
            }
        };
        
        // Send the notification
        $notifiable->notify($notification);
        
        $this->info('✓ Failed backup notification sent');
    }
} 
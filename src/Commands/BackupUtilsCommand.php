<?php

namespace Ubxty\SpatieLaravelBackupUtils\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Unified backup utilities command providing comprehensive backup management and monitoring.
 *
 * This command serves as the central hub for all backup-related operations including
 * statistics analysis, S3 configuration, notification testing, and more.
 *
 * @package Ubxty\SpatieLaravelBackupUtils
 * @author  Ravdeep Singh <info@ubxty.com>
 * @author  UBXTY Unboxing Technology <info@ubxty.com>
 * @license MIT
 * @version 1.0.0
 */
class BackupUtilsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backup:utils {action?}';

    /**
     * The console command description.
     */
    protected $description = 'Unified backup utilities - comprehensive backup management and monitoring';

    /**
     * Available utilities mapping.
     */
    private array $utilities = [
        'stats' => [
            'name' => 'Backup Statistics & Analytics',
            'description' => 'View comprehensive backup statistics, trends, and performance metrics',
            'command' => 'backup:stats',
            'icon' => '📊'
        ],
        'config' => [
            'name' => 'Backup Configuration', 
            'description' => 'Configure S3 settings, archive encryption password, and test connection',
            'command' => 'backup:config',
            'icon' => '🔧'
        ],
        'run' => [
            'name' => 'Run Backup',
            'description' => 'Execute backup with enhanced logging and notifications',
            'command' => 'backup:run',
            'icon' => '🚀'
        ],
        'monitor' => [
            'name' => 'Monitor Backup Health',
            'description' => 'Check backup health and generate monitoring reports',
            'command' => 'backup:monitor',
            'icon' => '🔍'
        ],
        'list' => [
            'name' => 'List Backups',
            'description' => 'Display all available backups with detailed information',
            'command' => 'backup:list',
            'icon' => '📋'
        ],
        'clean' => [
            'name' => 'Clean Old Backups',
            'description' => 'Remove old backups according to retention policies',
            'command' => 'backup:clean',
            'icon' => '🧹'
        ],
        'test' => [
            'name' => 'Test Notifications',
            'description' => 'Test backup notification system and logging',
            'command' => 'backup:test-notifications',
            'icon' => '🧪'
        ],
        'logs' => [
            'name' => 'View Backup Logs',
            'description' => 'View and analyze backup notification logs',
            'command' => 'backup:logs',
            'icon' => '📜'
        ],
        'install' => [
            'name' => 'Install & Configure',
            'description' => 'Set up backup utilities with guided configuration',
            'command' => null,
            'icon' => '⚙️'
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        if ($action) {
            return $this->runDirectAction($action);
        }

        return $this->showInteractiveMenu();
    }

    /**
     * Run a direct action if specified.
     */
    private function runDirectAction(string $action): int
    {
        if (!isset($this->utilities[$action])) {
            $this->error("❌ Unknown action: {$action}");
            $this->line('');
            $this->showAvailableActions();
            return 1;
        }

        $utility = $this->utilities[$action];

        if ($action === 'install') {
            return $this->runInstallation();
        }

        if (!$utility['command']) {
            $this->error("❌ Action '{$action}' is not available as a direct command.");
            return 1;
        }

        $this->info("╭─────────────────────────────────────────────────────────────╮");
        $this->info("│ {$utility['icon']} Running: " . str_pad($utility['name'], 48) . " │");
        $this->info("╰─────────────────────────────────────────────────────────────╯");
        $this->line('');

        $startTime = microtime(true);
        
        // For interactive commands, we need to call them properly to handle user input
        $result = $this->call($utility['command']);
        
        $endTime = microtime(true);
        
        $this->line('');
        $executionTime = round(($endTime - $startTime) * 1000, 2);
        
        if ($result === 0) {
            $this->info("✅ Command completed successfully in {$executionTime}ms");
        } else {
            $this->error("❌ Command failed with exit code: {$result}");
        }

        return $result;
    }

    /**
     * Show interactive menu.
     */
    private function showInteractiveMenu(): int
    {
        while (true) {
            $this->displayHeader();
            $this->displaySystemStatus();
            
            $result = $this->showArrowKeyMenu();
            
            if ($result !== null) {
                return $result;
            }
        }
    }

    /**
     * Show arrow key interactive menu.
     */
    private function showArrowKeyMenu(): ?int
    {
        $options = [];
        foreach ($this->utilities as $key => $utility) {
            $options[] = [
                'key' => $key,
                'label' => "{$utility['icon']} {$utility['name']}",
                'description' => $utility['description']
            ];
        }
        $options[] = [
            'key' => 'exit',
            'label' => "🚪 Exit",
            'description' => "Close the backup utilities dashboard"
        ];

        $selected = 0;
        $maxOptions = count($options) - 1;

        $this->comment('🎯 Available Actions:');
        $this->line('');
        $this->info('⌨️  Navigate: ↑↓ Arrow Keys | Select: Enter | Exit: Ctrl+C');
        $this->line('');

        // Initial menu display
        $this->displayMenuOptions($options, $selected);

        while (true) {

            // Read single keypress
            $key = $this->readSingleKey();

            switch ($key) {
                case "\033[A": // Up arrow
                    $selected = $selected > 0 ? $selected - 1 : $maxOptions;
                    $this->redrawMenu($options, $selected);
                    break;
                case "\033[B": // Down arrow
                    $selected = $selected < $maxOptions ? $selected + 1 : 0;
                    $this->redrawMenu($options, $selected);
                    break;
                case "\n": // Enter
                case "\r": // Carriage return
                    $selectedOption = $options[$selected];
                    
                    if ($selectedOption['key'] === 'exit') {
                        $this->line('');
                        $this->info('👋 Goodbye!');
                        return 0;
                    }

                    $this->line('');
                    $result = $this->runDirectAction($selectedOption['key']);
                    
                    $this->line('');
                    if (!$this->confirm('Would you like to return to the main menu?', true)) {
                        return $result;
                    }
                    
                    return null; // Continue main loop
                case "\003": // Ctrl+C
                    $this->line('');
                    $this->info('👋 Goodbye!');
                    return 0;
                default:
                    // Handle number key selection
                    if (is_numeric($key) && $key >= '0' && $key <= (string)$maxOptions) {
                        $selected = (int)$key;
                    }
                    break;
            }
        }
    }

    /**
     * Display menu options.
     */
    private function displayMenuOptions(array $options, int $selected): void
    {
        foreach ($options as $index => $option) {
            $prefix = $index === $selected ? '❯ ' : '  ';
            
            if ($index === $selected) {
                $this->info($prefix . $option['label']);
                $this->comment('  ' . $option['description']);
            } else {
                $this->line($prefix . $option['label']);
                $this->comment('  ' . $option['description']);
            }
        }

        $this->line('');
        $this->displayQuickTips();
    }

    /**
     * Redraw menu with updated selection.
     */
    private function redrawMenu(array $options, int $selected): void
    {
        // Calculate lines to clear (2 lines per option + tip line + empty line)
        $linesToClear = (count($options) * 2) + 3;
        
        // Move cursor up and clear
        $this->output->write("\033[{$linesToClear}A"); // Move cursor up
        $this->output->write("\033[0J"); // Clear from cursor to end of screen
        
        $this->displayMenuOptions($options, $selected);
    }

    /**
     * Read a single keypress from stdin.
     */
    private function readSingleKey(): string
    {
        // Check if we're in a TTY environment
        if (!posix_isatty(STDIN)) {
            // Fallback to regular input for non-TTY environments
            return trim(fgets(STDIN));
        }

        // Save current terminal settings
        $settings = trim(shell_exec('stty -g'));
        
        // Set terminal to raw mode for single character input
        shell_exec('stty -icanon -echo');
        
        $key = '';
        $char = fgetc(STDIN);
        
        if ($char === "\033") {
            // Handle escape sequences (arrow keys)
            $key = $char;
            $key .= fgetc(STDIN); // [
            $key .= fgetc(STDIN); // A, B, C, or D
        } else {
            $key = $char;
        }
        
        // Restore terminal settings
        shell_exec("stty $settings");
        
        return $key;
    }

    /**
     * Fallback numbered menu for terminals that don't support arrow keys.
     */
    private function showNumberedMenu(): int
    {
        while (true) {
            $this->displayHeader();
            $this->displaySystemStatus();
            
            $this->comment('🎯 Available Actions:');
            $this->line('');

            $options = [];
            $index = 1;
            
            foreach ($this->utilities as $key => $utility) {
                $this->line("  [{$index}] {$utility['icon']} {$utility['name']}");
                $this->line("      {$utility['description']}");
                $this->line('');
                $options[$index] = $key;
                $index++;
            }
            
            $this->line("  [0] 🚪 Exit");
            $this->line('');
            
            $this->displayQuickTips();

            $choice = $this->ask('👉 Please enter your choice (0-' . (count($this->utilities)) . ')', '1');
            
            // Validate input
            if (!ctype_digit($choice) || $choice < 0 || $choice > count($this->utilities)) {
                $this->error('❌ Invalid choice. Please enter a number between 0 and ' . count($this->utilities));
                $this->line('');
                $this->ask('Press Enter to continue...');
                continue;
            }

            $choice = (int) $choice;

            if ($choice === 0) {
                $this->info('👋 Goodbye!');
                return 0;
            }

            $action = $options[$choice];
            $this->line('');
            
            $result = $this->runDirectAction($action);
            
            // Ask if user wants to continue after action completes
            $this->line('');
            if (!$this->confirm('Would you like to return to the main menu?', true)) {
                return $result;
            }
            
            $this->line('');
        }
    }

    /**
     * Display header information.
     */
    private function displayHeader(): void
    {
        // Clear screen for better UX
        $this->output->write("\033[2J\033[H");
        
        $this->info('╭─────────────────────────────────────────────────────────────╮');
        $this->info('│               🔧 UBXTY Backup Utils Dashboard              │');
        $this->info('│              Enhanced Laravel Backup Management            │');
        $this->info('│            by Ravdeep Singh • UBXTY v1.0.0                 │');
        $this->info('╰─────────────────────────────────────────────────────────────╯');
        $this->line('');
    }

    /**
     * Display system status overview.
     */
    private function displaySystemStatus(): void
    {
        $this->comment('📋 System Overview:');
        
        // Check if spatie/laravel-backup is installed
        $spatieInstalled = class_exists('\Spatie\Backup\BackupServiceProvider');
        $spatieStatus = $spatieInstalled ? '✅ Installed' : '❌ Not Found';
        
        // Check backup configuration
        $backupConfigured = config('backup') ? '✅ Configured' : '❌ Not Configured';
        
        // Check S3 configuration
        $s3Configured = (
            config('filesystems.disks.s3_backup') && 
            env('AWS_ACCESS_KEY_ID_BACKUP') && 
            env('AWS_BUCKET_BACKUP')
        ) ? '✅ Configured' : '⚠️ Needs Setup';
        
        // Check logging configuration
        $loggingConfigured = config('logging.channels.backup') ? '✅ Configured' : '⚠️ Auto-configured';

        $this->table(['Component', 'Status'], [
            ['Spatie Laravel Backup', $spatieStatus],
            ['Backup Configuration', $backupConfigured],
            ['S3 Backup Setup', $s3Configured],
            ['Backup Logging', $loggingConfigured],
            ['Notification System', '✅ Enhanced'],
        ]);
        
        $this->line('');
    }

    /**
     * Run installation and configuration process.
     */
    private function runInstallation(): int
    {
        $this->info('⚙️ Setting up UBXTY Backup Utils...');
        $this->line('');

        // Publish configuration files
        $this->comment('📝 Publishing configuration files...');
        
        if ($this->confirm('Publish UBXTY backup utils configuration?', true)) {
            Artisan::call('vendor:publish', [
                '--tag' => 'backup-utils-config',
                '--force' => true
            ]);
            $this->info('✅ Published backup-utils.php configuration');
        }

        if ($this->confirm('Publish environment template?', true)) {
            Artisan::call('vendor:publish', [
                '--tag' => 'backup-utils-env',
                '--force' => true
            ]);
            $this->info('✅ Published env-backup.example template');
        }

        // Check if spatie/laravel-backup is installed
        if (!class_exists('\Spatie\Backup\BackupServiceProvider')) {
            $this->warn('⚠️ Spatie Laravel Backup not found!');
            if ($this->confirm('Install spatie/laravel-backup now?', true)) {
                $this->comment('Installing spatie/laravel-backup...');
                shell_exec('composer require spatie/laravel-backup');
                $this->info('✅ Spatie Laravel Backup installed');
            }
        }

        // Publish spatie backup config if needed
        if (!config('backup') && $this->confirm('Publish spatie backup configuration?', true)) {
            try {
                Artisan::call('vendor:publish', [
                    '--provider' => 'Spatie\Backup\BackupServiceProvider',
                    '--tag' => 'backup-config'
                ]);
                $this->info('✅ Published spatie backup configuration');
            } catch (\Exception $e) {
                $this->error('❌ Failed to publish spatie backup config: ' . $e->getMessage());
                $this->comment('💡 Try running manually: php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider" --tag=backup-config');
            }
        }

        $this->line('');
        $this->info('🎉 Installation completed!');
        $this->line('');

        // Offer to configure S3
        if ($this->confirm('Configure S3 backup settings now?', true)) {
            $this->line('');
            $result = $this->call('backup:config');
            $this->line('');
            
            if ($result === 0) {
                $this->info('🎉 S3 configuration completed successfully!');
            } else {
                $this->warn('⚠️ S3 configuration was skipped or failed.');
            }
        }

        $this->line('');
        $this->info('╭─────────────────────────────────────────────────────────────╮');
        $this->info('│                    🎉 Setup Complete!                      │');
        $this->info('╰─────────────────────────────────────────────────────────────╯');
        $this->line('');

        // Show next steps
        $this->comment('📋 Quick Start Guide:');
        $this->line('  1️⃣  Run "php artisan backup:utils stats" to view analytics');
        $this->line('  2️⃣  Run "php artisan backup:run" to test your first backup');
        $this->line('  3️⃣  Run "php artisan backup:utils" for the interactive menu');
        $this->line('');
        
        if ($this->confirm('Would you like to view backup statistics now?', false)) {
            $this->line('');
            return $this->call('backup:stats');
        }

        return 0;
    }

    /**
     * Show available actions for direct access.
     */
    private function showAvailableActions(): void
    {
        $this->comment('Available actions:');
        foreach ($this->utilities as $key => $utility) {
            $this->line("  • php artisan backup:utils {$key} - {$utility['description']}");
        }
    }

    /**
     * Display quick tips for users.
     */
    private function displayQuickTips(): void
    {
        $tips = [
            '💡 Tip: Start with "Install & Configure" if this is your first time',
            '📊 Tip: Use "Backup Statistics" to monitor your backup health',
            '🔧 Tip: "Backup Configuration" includes S3 setup and connection testing',
            '🚀 Tip: All commands provide detailed output and error messages',
            '⌨️  Tip: Use ↑↓ arrows to navigate, Enter to select, number keys for quick access',
            '🎯 Tip: You can also use direct commands like "php artisan backup:utils stats"',
        ];
        
        $randomTip = $tips[array_rand($tips)];
        $this->comment($randomTip);
        $this->line('');
    }
} 
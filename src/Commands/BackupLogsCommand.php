<?php

namespace Ubxty\SpatieLaravelBackupUtils\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

/**
 * Command for viewing and analyzing backup notification logs.
 *
 * Provides comprehensive log analysis with filtering, statistics,
 * and detailed backup event information.
 *
 * @package Ubxty\SpatieLaravelBackupUtils
 * @author  Ravdeep Singh <info@ubxty.com>
 * @author  UBXTY Unboxing Technology <info@ubxty.com>
 * @license MIT
 * @version 1.0.2
 */
class BackupLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backup:logs 
                            {--days=7 : Number of days to show logs for}
                            {--level= : Filter by log level (info, warning, error)}
                            {--event= : Filter by backup event type}
                            {--stats : Show statistics only}
                            {--tail=50 : Number of recent entries to show}';

    /**
     * The console command description.
     */
    protected $description = 'View and analyze backup notification logs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = $this->option('days');
        $level = $this->option('level');
        $event = $this->option('event');
        $showStats = $this->option('stats');
        $tail = $this->option('tail');

        // Look for daily log files first, fallback to backup.log
        $logPath = $this->findLogFile();
        
        if (!$logPath) {
            $this->error('No backup log files found. Checked:');
            $this->line('- storage/logs/backup-' . date('Y-m-d') . '.log');
            $this->line('- storage/logs/backup.log');
            return 1;
        }

        $this->info('Reading log file: ' . basename($logPath));

        $logs = $this->parseLogFile($logPath, $days);
        
        if ($level) {
            $logs = $this->filterByLevel($logs, $level);
        }
        
        if ($event) {
            $logs = $this->filterByEvent($logs, $event);
        }

        if ($showStats) {
            $this->displayStats($logs);
        } else {
            $this->displayLogs($logs, $tail);
        }

        return 0;
    }

    /**
     * Find the most recent backup log file.
     */
    private function findLogFile(): ?string
    {
        // Check for today's log file first
        $todayLog = storage_path('logs/backup-' . date('Y-m-d') . '.log');
        if (File::exists($todayLog)) {
            return $todayLog;
        }

        // Check for yesterday's log file
        $yesterdayLog = storage_path('logs/backup-' . date('Y-m-d', strtotime('-1 day')) . '.log');
        if (File::exists($yesterdayLog)) {
            return $yesterdayLog;
        }

        // Check for generic backup.log
        $genericLog = storage_path('logs/backup.log');
        if (File::exists($genericLog)) {
            return $genericLog;
        }

        // Find any backup log file
        $logFiles = glob(storage_path('logs/backup-*.log'));
        if (!empty($logFiles)) {
            // Return the most recent one
            usort($logFiles, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            return $logFiles[0];
        }

        return null;
    }

    /**
     * Parse the log file and extract backup-related entries.
     */
    private function parseLogFile(string $logPath, int $days): array
    {
        $content = File::get($logPath);
        $lines = explode("\n", $content);
        $logs = [];
        $cutoffDate = Carbon::now()->subDays($days);

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            $parsed = $this->parseLogLine($line);
            if ($parsed && Carbon::parse($parsed['timestamp'])->gte($cutoffDate)) {
                $logs[] = $parsed;
            }
        }

        return array_reverse($logs); // Most recent first
    }

    /**
     * Parse a single log line.
     */
    private function parseLogLine(string $line): ?array
    {
        // Match Laravel log format: [timestamp] level: message context
        $pattern = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.+)/';
        
        if (!preg_match($pattern, $line, $matches)) {
            return null;
        }

        $timestamp = $matches[1];
        $level = $matches[2];
        $messageAndContext = $matches[3];

        // Try to extract JSON context
        $jsonStart = strpos($messageAndContext, '{');
        if ($jsonStart !== false) {
            $message = trim(substr($messageAndContext, 0, $jsonStart));
            $contextJson = substr($messageAndContext, $jsonStart);
            $context = json_decode($contextJson, true) ?? [];
        } else {
            $message = $messageAndContext;
            $context = [];
        }

        return [
            'timestamp' => $timestamp,
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'raw' => $line
        ];
    }

    /**
     * Filter logs by level.
     */
    private function filterByLevel(array $logs, string $level): array
    {
        return array_filter($logs, function ($log) use ($level) {
            return strtolower($log['level']) === strtolower($level);
        });
    }

    /**
     * Filter logs by backup event.
     */
    private function filterByEvent(array $logs, string $event): array
    {
        return array_filter($logs, function ($log) use ($event) {
            return isset($log['context']['backup_event']) && 
                   strpos($log['context']['backup_event'], $event) !== false;
        });
    }

    /**
     * Display logs in a formatted table.
     */
    private function displayLogs(array $logs, int $tail): void
    {
        $logs = array_slice($logs, 0, $tail);

        if (empty($logs)) {
            $this->info('No backup logs found matching the criteria.');
            return;
        }

        $this->info('Recent Backup Logs:');
        $this->line('');

        $headers = ['Time', 'Level', 'Event', 'Status', 'Message'];
        $rows = [];

        foreach ($logs as $log) {
            $rows[] = [
                Carbon::parse($log['timestamp'])->format('m-d H:i:s'),
                $this->colorizeLevel($log['level']),
                $log['context']['backup_event'] ?? 'unknown',
                $log['context']['status'] ?? 'unknown',
                $this->truncate($log['message'], 50)
            ];
        }

        $this->table($headers, $rows);

        if (count($logs) >= $tail) {
            $this->info("\nShowing {$tail} most recent entries. Use --tail=N to show more.");
        }
    }

    /**
     * Display backup statistics.
     */
    private function displayStats(array $logs): void
    {
        if (empty($logs)) {
            $this->info('No backup logs found for statistics.');
            return;
        }

        $stats = [
            'total_entries' => count($logs),
            'levels' => [],
            'events' => [],
            'statuses' => [],
            'recent_successful' => null,
            'recent_failed' => null,
        ];

        foreach ($logs as $log) {
            // Count by level
            $level = $log['level'];
            $stats['levels'][$level] = ($stats['levels'][$level] ?? 0) + 1;

            // Count by event
            $event = $log['context']['backup_event'] ?? 'unknown';
            $stats['events'][$event] = ($stats['events'][$event] ?? 0) + 1;

            // Count by status
            $status = $log['context']['status'] ?? 'unknown';
            $stats['statuses'][$status] = ($stats['statuses'][$status] ?? 0) + 1;

            // Track recent events
            if ($status === 'success' && !$stats['recent_successful']) {
                $stats['recent_successful'] = $log['timestamp'];
            }
            if (in_array($status, ['error', 'failed']) && !$stats['recent_failed']) {
                $stats['recent_failed'] = $log['timestamp'];
            }
        }

        $this->info('Backup Log Statistics:');
        $this->line('');

        $this->info("Total entries: {$stats['total_entries']}");
        $this->line('');

        $this->info('By Level:');
        foreach ($stats['levels'] as $level => $count) {
            $this->line("  {$this->colorizeLevel($level)}: {$count}");
        }
        $this->line('');

        $this->info('By Event:');
        foreach ($stats['events'] as $event => $count) {
            $this->line("  {$event}: {$count}");
        }
        $this->line('');

        $this->info('By Status:');
        foreach ($stats['statuses'] as $status => $count) {
            $color = $status === 'success' ? 'green' : ($status === 'error' ? 'red' : 'yellow');
            $this->line("  <fg={$color}>{$status}</>: {$count}");
        }
        $this->line('');

        if ($stats['recent_successful']) {
            $this->info('Last successful backup: ' . Carbon::parse($stats['recent_successful'])->diffForHumans());
        }
        if ($stats['recent_failed']) {
            $this->error('Last failed backup: ' . Carbon::parse($stats['recent_failed'])->diffForHumans());
        }
    }

    /**
     * Colorize log level for display.
     */
    private function colorizeLevel(string $level): string
    {
        return match (strtolower($level)) {
            'error', 'critical' => "<fg=red>{$level}</>",
            'warning' => "<fg=yellow>{$level}</>",
            'info' => "<fg=green>{$level}</>",
            default => $level,
        };
    }

    /**
     * Truncate text to specified length.
     */
    private function truncate(string $text, int $length): string
    {
        return strlen($text) > $length ? substr($text, 0, $length - 3) . '...' : $text;
    }
} 
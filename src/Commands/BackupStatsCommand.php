<?php

namespace Ubxty\SpatieLaravelBackupUtils\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

/**
 * Command for analyzing backup statistics and generating comprehensive reports.
 *
 * Provides detailed analytics on backup performance, success rates,
 * failure trends, and system metrics with export capabilities.
 *
 * @package Ubxty\SpatieLaravelBackupUtils
 * @author  Ravdeep Singh <info@ubxty.com>
 * @author  UBXTY Unboxing Technology <info@ubxty.com>
 * @license MIT
 * @version 1.0.2
 */
class BackupStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backup:stats 
                            {--days=30 : Number of days to analyze}
                            {--format=table : Output format (table, json, summary)}
                            {--export= : Export to file (csv, json)}';

    /**
     * The console command description.
     */
    protected $description = 'Analyze backup notification logs and display comprehensive statistics';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = $this->option('days');
        $format = $this->option('format');
        $export = $this->option('export');

        $this->info("📊 Analyzing backup logs for the last {$days} days...");
        $this->line('');

        // Find and parse log files
        $logData = $this->parseLogFiles($days);
        
        if (empty($logData)) {
            $this->warn('No backup log entries found in the specified time period.');
            return 1;
        }

        // Generate statistics
        $stats = $this->generateStatistics($logData);
        
        // Display results based on format
        switch ($format) {
            case 'json':
                $this->displayJsonStats($stats);
                break;
            case 'summary':
                $this->displaySummaryStats($stats);
                break;
            default:
                $this->displayTableStats($stats);
                break;
        }

        // Export if requested
        if ($export) {
            $this->exportStats($stats, $export);
        }

        return 0;
    }

    /**
     * Parse log files for the specified number of days.
     */
    private function parseLogFiles(int $days): array
    {
        $logData = [];
        $startDate = Carbon::now()->subDays($days);

        // Check for daily log files
        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::now()->subDays($i);
            $logFile = storage_path("logs/backup-{$date->format('Y-m-d')}.log");
            
            if (File::exists($logFile)) {
                $logData = array_merge($logData, $this->parseLogFile($logFile, $startDate));
            }
        }

        // Also check the main backup.log file
        $mainLogFile = storage_path('logs/backup.log');
        if (File::exists($mainLogFile)) {
            $logData = array_merge($logData, $this->parseLogFile($mainLogFile, $startDate));
        }

        // Remove duplicates and sort by timestamp
        $logData = collect($logData)
            ->unique(fn($item) => $item['timestamp'] . $item['notification_type'])
            ->sortBy('timestamp')
            ->values()
            ->toArray();

        return $logData;
    }

    /**
     * Parse a single log file.
     */
    private function parseLogFile(string $filePath, Carbon $startDate): array
    {
        $entries = [];
        $content = File::get($filePath);
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            // Parse Laravel log format: [timestamp] level: message {json}
            if (preg_match('/\[(.+?)\] \w+\.(\w+): (.+?) (\{.+\})/', $line, $matches)) {
                try {
                    $timestamp = Carbon::parse($matches[1]);
                    if ($timestamp->lt($startDate)) continue;

                    $level = $matches[2];
                    $message = $matches[3];
                    $jsonData = json_decode($matches[4], true);

                    if ($jsonData && isset($jsonData['notification_type'])) {
                        $entries[] = [
                            'timestamp' => $timestamp->toISOString(),
                            'level' => $level,
                            'message' => $message,
                            'notification_type' => $jsonData['notification_type'],
                            'notification_class' => $jsonData['notification_class'] ?? 'Unknown',
                            'data' => $jsonData,
                            'parsed_timestamp' => $timestamp,
                        ];
                    }
                } catch (\Exception $e) {
                    // Skip invalid entries
                    continue;
                }
            }
        }

        return $entries;
    }

    /**
     * Generate comprehensive statistics from log data.
     */
    private function generateStatistics(array $logData): array
    {
        $stats = [
            'overview' => $this->generateOverviewStats($logData),
            'notification_types' => $this->generateNotificationTypeStats($logData),
            'time_analysis' => $this->generateTimeAnalysis($logData),
            'failure_analysis' => $this->generateFailureAnalysis($logData),
            'performance' => $this->generatePerformanceStats($logData),
            'recent_activity' => $this->generateRecentActivity($logData),
        ];

        return $stats;
    }

    /**
     * Generate overview statistics.
     */
    private function generateOverviewStats(array $logData): array
    {
        $total = count($logData);
        $successes = collect($logData)->filter(fn($item) => 
            str_contains($item['notification_type'], 'Successful') || 
            str_contains($item['notification_type'], 'Healthy')
        )->count();
        
        $failures = collect($logData)->filter(fn($item) => 
            str_contains($item['notification_type'], 'Failed') || 
            str_contains($item['notification_type'], 'Unhealthy')
        )->count();

        $successRate = $total > 0 ? round(($successes / $total) * 100, 2) : 0;

        return [
            'total_events' => $total,
            'successful_events' => $successes,
            'failed_events' => $failures,
            'success_rate' => $successRate,
            'failure_rate' => round(100 - $successRate, 2),
        ];
    }

    /**
     * Generate notification type breakdown.
     */
    private function generateNotificationTypeStats(array $logData): array
    {
        return collect($logData)
            ->groupBy('notification_type')
            ->map(fn($group) => [
                'count' => $group->count(),
                'percentage' => round(($group->count() / count($logData)) * 100, 2),
                'last_occurrence' => $group->max('timestamp'),
            ])
            ->sortByDesc('count')
            ->toArray();
    }

    /**
     * Generate time-based analysis.
     */
    private function generateTimeAnalysis(array $logData): array
    {
        $byDay = collect($logData)
            ->groupBy(fn($item) => Carbon::parse($item['timestamp'])->format('Y-m-d'))
            ->map(fn($group) => [
                'total' => $group->count(),
                'successes' => $group->filter(fn($item) => 
                    str_contains($item['notification_type'], 'Successful') || 
                    str_contains($item['notification_type'], 'Healthy')
                )->count(),
                'failures' => $group->filter(fn($item) => 
                    str_contains($item['notification_type'], 'Failed') || 
                    str_contains($item['notification_type'], 'Unhealthy')
                )->count(),
            ])
            ->sortKeys();

        $byHour = collect($logData)
            ->groupBy(fn($item) => Carbon::parse($item['timestamp'])->format('H'))
            ->map(fn($group) => $group->count())
            ->sortKeys();

        return [
            'daily_breakdown' => $byDay->toArray(),
            'hourly_distribution' => $byHour->toArray(),
            'busiest_day' => $byDay->sortByDesc('total')->keys()->first(),
            'busiest_hour' => $byHour->sortDesc()->keys()->first(),
        ];
    }

    /**
     * Generate failure analysis.
     */
    private function generateFailureAnalysis(array $logData): array
    {
        $failures = collect($logData)->filter(fn($item) => 
            str_contains($item['notification_type'], 'Failed') || 
            str_contains($item['notification_type'], 'Unhealthy')
        );

        $failureTypes = $failures
            ->groupBy('notification_type')
            ->map(fn($group) => $group->count())
            ->sortDesc()
            ->toArray();

        $recentFailures = $failures
            ->sortByDesc('timestamp')
            ->take(5)
            ->map(fn($item) => [
                'type' => $item['notification_type'],
                'timestamp' => $item['timestamp'],
                'message' => $item['message'],
                'mail_subject' => $item['data']['mail_subject'] ?? 'N/A',
            ])
            ->values()
            ->toArray();

        return [
            'failure_types' => $failureTypes,
            'total_failures' => $failures->count(),
            'recent_failures' => $recentFailures,
            'failure_trend' => $this->calculateTrend($failures->toArray()),
        ];
    }

    /**
     * Generate performance statistics.
     */
    private function generatePerformanceStats(array $logData): array
    {
        $eventsWithMemory = collect($logData)
            ->filter(fn($item) => isset($item['data']['metadata']['memory_usage']));

        $avgMemory = $eventsWithMemory->avg('data.metadata.memory_usage');
        $maxMemory = $eventsWithMemory->max('data.metadata.memory_usage');

        return [
            'average_memory_usage' => $avgMemory ? $this->formatBytes($avgMemory) : 'N/A',
            'peak_memory_usage' => $maxMemory ? $this->formatBytes($maxMemory) : 'N/A',
            'php_version' => $logData[0]['data']['metadata']['php_version'] ?? 'Unknown',
            'laravel_version' => $logData[0]['data']['metadata']['laravel_version'] ?? 'Unknown',
        ];
    }

    /**
     * Generate recent activity summary.
     */
    private function generateRecentActivity(array $logData): array
    {
        $recent = collect($logData)
            ->sortByDesc('timestamp')
            ->take(10)
            ->map(fn($item) => [
                'type' => $item['notification_type'],
                'level' => $item['level'],
                'timestamp' => Carbon::parse($item['timestamp'])->diffForHumans(),
                'message' => $item['message'],
            ])
            ->values()
            ->toArray();

        return $recent;
    }

    /**
     * Calculate trend (increasing/decreasing/stable).
     */
    private function calculateTrend(array $data): string
    {
        if (count($data) < 2) return 'insufficient_data';

        $recent = collect($data)
            ->sortByDesc('timestamp')
            ->take(7)
            ->count();

        $previous = collect($data)
            ->sortByDesc('timestamp')
            ->slice(7, 7)
            ->count();

        if ($recent > $previous) return 'increasing';
        if ($recent < $previous) return 'decreasing';
        return 'stable';
    }

    /**
     * Format bytes into human readable format.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }

    /**
     * Display statistics in table format.
     */
    private function displayTableStats(array $stats): void
    {
        // Overview
        $this->info('📈 Overview');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Events', $stats['overview']['total_events']],
                ['Successful Events', $stats['overview']['successful_events']],
                ['Failed Events', $stats['overview']['failed_events']],
                ['Success Rate', $stats['overview']['success_rate'] . '%'],
                ['Failure Rate', $stats['overview']['failure_rate'] . '%'],
            ]
        );

        // Notification Types
        $this->line('');
        $this->info('📋 Notification Types');
        $notificationData = [];
        foreach ($stats['notification_types'] as $type => $data) {
            $notificationData[] = [
                $type,
                $data['count'],
                $data['percentage'] . '%',
                Carbon::parse($data['last_occurrence'])->diffForHumans(),
            ];
        }
        $this->table(['Type', 'Count', 'Percentage', 'Last Seen'], $notificationData);

        // Performance
        $this->line('');
        $this->info('⚡ Performance');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Average Memory Usage', $stats['performance']['average_memory_usage']],
                ['Peak Memory Usage', $stats['performance']['peak_memory_usage']],
                ['PHP Version', $stats['performance']['php_version']],
                ['Laravel Version', $stats['performance']['laravel_version']],
            ]
        );

        // Failure Analysis
        if ($stats['failure_analysis']['total_failures'] > 0) {
            $this->line('');
            $this->info('❌ Failure Analysis');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Failures', $stats['failure_analysis']['total_failures']],
                    ['Failure Trend', ucfirst(str_replace('_', ' ', $stats['failure_analysis']['failure_trend']))],
                ]
            );

            if (!empty($stats['failure_analysis']['recent_failures'])) {
                $this->line('');
                $this->info('🔍 Recent Failures');
                $failureData = [];
                foreach ($stats['failure_analysis']['recent_failures'] as $failure) {
                    $failureData[] = [
                        $failure['type'],
                        Carbon::parse($failure['timestamp'])->diffForHumans(),
                        $failure['mail_subject'],
                    ];
                }
                $this->table(['Type', 'When', 'Details'], $failureData);
            }
        }

        // Recent Activity
        $this->line('');
        $this->info('🕐 Recent Activity');
        $activityData = [];
        foreach ($stats['recent_activity'] as $activity) {
            $activityData[] = [
                $activity['type'],
                strtoupper($activity['level']),
                $activity['timestamp'],
                $activity['message'],
            ];
        }
        $this->table(['Type', 'Level', 'When', 'Message'], $activityData);
    }

    /**
     * Display statistics in JSON format.
     */
    private function displayJsonStats(array $stats): void
    {
        $this->line(json_encode($stats, JSON_PRETTY_PRINT));
    }

    /**
     * Display summary statistics.
     */
    private function displaySummaryStats(array $stats): void
    {
        $this->info('📊 Backup Statistics Summary');
        $this->line('');
        
        $this->line("✅ <info>Total Events:</info> {$stats['overview']['total_events']}");
        $this->line("🎯 <info>Success Rate:</info> {$stats['overview']['success_rate']}%");
        
        if ($stats['overview']['failed_events'] > 0) {
            $this->line("❌ <error>Failed Events:</error> {$stats['overview']['failed_events']} ({$stats['overview']['failure_rate']}%)");
            $this->line("📈 <comment>Failure Trend:</comment> " . ucfirst(str_replace('_', ' ', $stats['failure_analysis']['failure_trend'])));
        }

        $this->line("💾 <info>Average Memory:</info> {$stats['performance']['average_memory_usage']}");
        $this->line("⚡ <info>Peak Memory:</info> {$stats['performance']['peak_memory_usage']}");
        
        $mostCommon = array_keys($stats['notification_types'])[0] ?? 'None';
        $this->line("📋 <info>Most Common:</info> {$mostCommon}");
    }

    /**
     * Export statistics to file.
     */
    private function exportStats(array $stats, string $format): void
    {
        $filename = storage_path("logs/backup-stats-" . date('Y-m-d-H-i-s') . ".{$format}");
        
        if ($format === 'json') {
            File::put($filename, json_encode($stats, JSON_PRETTY_PRINT));
        } elseif ($format === 'csv') {
            $csv = $this->convertToCsv($stats);
            File::put($filename, $csv);
        }

        $this->info("📁 Statistics exported to: {$filename}");
    }

    /**
     * Convert statistics to CSV format.
     */
    private function convertToCsv(array $stats): string
    {
        $csv = "Metric,Value\n";
        
        // Overview
        $csv .= "Total Events,{$stats['overview']['total_events']}\n";
        $csv .= "Successful Events,{$stats['overview']['successful_events']}\n";
        $csv .= "Failed Events,{$stats['overview']['failed_events']}\n";
        $csv .= "Success Rate,{$stats['overview']['success_rate']}%\n";
        
        // Add notification types
        $csv .= "\nNotification Type,Count,Percentage\n";
        foreach ($stats['notification_types'] as $type => $data) {
            $csv .= "{$type},{$data['count']},{$data['percentage']}%\n";
        }

        return $csv;
    }
} 
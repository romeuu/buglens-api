<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Plugin;
use App\Jobs\AnalyzePluginJob;
use Illuminate\Support\Facades\Redis;

class LoadJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:load-jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to load the jobs into the Redis queue';
    
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Current Configuration ===');
        $this->info('Queue Driver: ' . config('queue.default'));
        $this->info('Redis Client: ' . config('database.redis.client'));
        $this->info('Redis Host: ' . config('database.redis.default.host'));
        $this->info('Redis Port: ' . config('database.redis.default.port'));
        $this->info('Redis Prefix: ' . config('database.redis.options.prefix', 'none'));
        $this->line('========================');

        try {
            $redis = Redis::connection();
            $testKey = 'test_connection';
            $redis->set($testKey, 'ok');
            $result = $redis->get($testKey);
            $this->info('Redis connection OK: ' . $result);
        } catch (\Exception $e) {
            $this->error('Error conectando con Redis: ' . $e->getMessage());
            return 1;
        }

        $plugins = Plugin::all();
        $pluginsToAnalyze = $plugins->filter(function($plugin) {
            return $plugin->needsAnalysis();
        });

        $count = $pluginsToAnalyze->count();
        $this->info("Found {$count} plugins to process");
        
        if ($count === 0) {
            $this->warn("No plugins found in the database!");
            return 1;
        }

        foreach ($pluginsToAnalyze as $plugin) {
            $this->info("Loading job for: {$plugin->slug}");
            try {
                $job = new AnalyzePluginJob($plugin->slug);
                $connection = 'redis';
                $queue = 'default';
                
                dispatch($job)->onConnection($connection)->onQueue($queue);
                
                $queueKey = 'queues:' . $queue;
                $queueLength = Redis::connection()->llen($queueKey);
                $this->info("Job enqueued for {$plugin->slug} - Current queue length: {$queueLength}");
            } catch (\Exception $e) {
                $this->error("Error enqueuing job for {$plugin->slug}:");
                $this->error($e->getMessage());
                $this->error($e->getTraceAsString());
            }
        }

        $finalLength = Redis::connection()->llen('queues:default');
        $this->info("Total final of jobs in queue: {$finalLength}");

        return 0;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Plugin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckVersions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-versions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the versions of the plugins';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $plugins = Plugin::all();

        foreach ($plugins as $plugin) {
            $this->info("Checking version of: " . $plugin->slug);

            $path = base_path(getenv('PLUGINS_PATH')) . '/' . $plugin->slug;

            try {
                $currentVersion = $this->getCurrentVersion($path);
                $plugin->current_version = $currentVersion;
                $plugin->save();

                $this->info("Current version: " . $currentVersion);

                $latestVersion = $this->getLatestVersion($plugin->slug);
                
                if ($latestVersion) {
                    $plugin->latest_version = $latestVersion;
                    $plugin->save();
                    $this->info("Latest version: " . $latestVersion);
                } else {
                    $this->warn("Could not fetch latest version for: " . $plugin->slug);
                }

                $this->info("Plugin: " . $plugin->slug . " - Current: " . $plugin->current_version . " - Latest: " . $plugin->latest_version);
            } catch (\Exception $e) {
                $this->error("Error processing plugin {$plugin->slug}: " . $e->getMessage());
            }
        }
    }

    private function getCurrentVersion(string $path): string
    {
        if (!file_exists($path . '/readme.txt')) {
            return '';
        }

        $text = file_get_contents($path . '/readme.txt');
        
        if (preg_match('/^Stable tag:\s*(.*)$/m', $text, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    private function getLatestVersion(string $slug): string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Accept' => 'text/plain,text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Connection' => 'keep-alive',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache',
            ])->get('https://plugins.svn.wordpress.org/' . $slug . '/trunk/readme.txt');

            if (!$response->successful()) {
                $this->warn("HTTP request failed with status: " . $response->status());
                return '';
            }

            $text = $response->body();
            
            if (preg_match('/^Stable tag:\s*(.*)$/m', $text, $matches)) {
                return trim($matches[1]);
            }

            return '';
        } catch (\Exception $e) {
            $this->error("Error fetching remote version for {$slug}: " . $e->getMessage());
            return '';
        }
    }
}

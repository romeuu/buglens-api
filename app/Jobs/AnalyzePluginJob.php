<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\ScannersPrivate\ScannerA;

use App\Models\Plugin;
use App\Models\Scan;

class AnalyzePluginJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $pluginSlug;

    public function __construct(string $pluginSlug)
    {
        $this->pluginSlug = $pluginSlug;
        $this->timeout = 3600;
        $this->tries = 1;
    }

    public function handle(): void
    {
        $scanner = new ScannerA();

        $plugin = Plugin::where('slug', $this->pluginSlug)->first();
        if (!$plugin) {
            throw new \Exception("Plugin not found: {$this->pluginSlug}");
        }

        $pluginPath = base_path(getenv('PLUGINS_PATH') . $this->pluginSlug);

        $scan = Scan::create([
            'plugin_id' => $plugin->id,
            'scanner' => 'scanner-a',
            'result' => [],
        ]);

        $scanner->run($pluginPath, $scan);

        $scan->result = $scan->vulnerabilities()->get()->toArray();
        $scan->save();

        Log::info("Scan completed for plugin {$this->pluginSlug}", ['scan_id' => $scan->id]);
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Plugin;
use App\Scanners\DemoScanner;
use App\Services\ScannerService;
use Illuminate\Console\Command;

class ExampleScanner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:example-scanner {slug : El slug del plugin a escanear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes the example scanner';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $slug = $this->argument('slug');
        $plugin = Plugin::where('slug', $slug)->first();

        if (!$plugin) {
            $this->error("Plugin {$slug} not found.");
            return;
        }

        // Use the demo scanner
        $scanner = new DemoScanner();
        $service = new ScannerService();

        $scan = $service->scanPlugin($plugin, $scanner);

        $this->info("Scan completed. Result:");
        $this->line(json_encode($scan->result, JSON_PRETTY_PRINT));
    }
}

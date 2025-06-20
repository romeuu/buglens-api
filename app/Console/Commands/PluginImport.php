<?php

namespace App\Console\Commands;

use App\Models\Plugin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PluginImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:plugin-import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import plugins to the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = base_path(getenv('PLUGINS_PATH'));

        if (!is_dir($path)) {
            $this->error("The plugins path {$path} does not exist.");
            return;
        }

        $directories = File::directories($path);

        foreach ($directories as $directory) {
            $slug = basename($directory);
            $name = Str::title(str_replace(['-', '_'], ' ', $slug));

            $this->info("Importing: {$slug}");

            $plugin = Plugin::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );

            $this->info("Imported: {$slug} as {$name}");
        }

        $this->info("Import completed.");
        return 0;
    }
}

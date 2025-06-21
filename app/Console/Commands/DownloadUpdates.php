<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Plugin;
use Illuminate\Support\Facades\Http;

class DownloadUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:download-updates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download the latest versions of the plugins';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $plugins = Plugin::whereNotNull('current_version')
                        ->whereNotNull('latest_version')
                        ->get()
                        ->filter(function($plugin) {
                            return version_compare($plugin->current_version, $plugin->latest_version, '<');
                        });

        foreach ($plugins as $plugin) {
            $this->info("Downloading update for {$plugin->name}, current version: {$plugin->current_version}, latest version: {$plugin->latest_version}");

            $path = base_path(getenv('PLUGINS_PATH')) . '/' . $plugin->slug;

            try {
                // Crear directorio si no existe
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                    $this->info("Created directory: {$path}");
                }

                // Intentar descargar con versión específica primero
                $downloadUrl = "https://downloads.wordpress.org/plugin/{$plugin->slug}.{$plugin->latest_version}.zip";
                $downloadUrlWithoutVersion = "https://downloads.wordpress.org/plugin/{$plugin->slug}.zip";
                
                $zipPath = $path . '.zip';
                $this->info("Attempting download from: {$downloadUrl}");
                
                $response = Http::timeout(300)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                        'Accept-Language' => 'en-US,en;q=0.5',
                    ])
                    ->get($downloadUrl);
                
                // Si falla con 404, intentar sin versión
                if ($response->status() === 404) {
                    $this->warn("Version-specific download failed (404), trying without version: {$downloadUrlWithoutVersion}");
                    
                    $response = Http::timeout(300)
                        ->withHeaders([
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                            'Accept-Language' => 'en-US,en;q=0.5',
                        ])
                        ->get($downloadUrlWithoutVersion);
                }
                
                if ($response->successful()) {
                    file_put_contents($zipPath, $response->body());
                    $this->info("Downloaded to: {$zipPath}");
                } else {
                    throw new \Exception("Failed to download plugin from both URLs. HTTP Status: " . $response->status());
                }

                // Extraer el contenido
                $zip = new \ZipArchive();
                if ($zip->open($zipPath) === TRUE) {
                    // Limpiar directorio existente
                    if (file_exists($path)) {
                        $this->rrmdir($path);
                        mkdir($path, 0755, true);
                    }
                    
                    // Extraer a un directorio temporal
                    $tempPath = $path . '_temp';
                    $zip->extractTo($tempPath);
                    $zip->close();
                    
                    // Mover archivos de la carpeta interna al directorio final
                    $this->moveFromSubdirectory($tempPath, $path, $plugin->slug);
                    
                    // Limpiar directorio temporal
                    $this->rrmdir($tempPath);
                    
                    $this->info("Extracted to: {$path}");
                    
                    // Limpiar archivo ZIP
                    unlink($zipPath);
                    
                    // Actualizar la versión en la base de datos
                    $plugin->current_version = $plugin->latest_version;
                    $plugin->save();
                    $this->info("Updated plugin version in database");
                    
                } else {
                    throw new \Exception("Failed to open ZIP file");
                }

            } catch (\Exception $e) {
                $this->error("Error processing plugin {$plugin->slug}: " . $e->getMessage());
            }
        }
    }

    /**
     * Move files from subdirectory to main directory
     */
    private function moveFromSubdirectory($tempPath, $finalPath, $pluginSlug)
    {
        $subdirectory = $tempPath . '/' . $pluginSlug;
        
        if (is_dir($subdirectory)) {
            // Mover todos los archivos y carpetas del subdirectorio al directorio final
            $this->copyDirectory($subdirectory, $finalPath);
        } else {
            // Si no hay subdirectorio, copiar directamente
            $this->copyDirectory($tempPath, $finalPath);
        }
    }

    /**
     * Copy directory recursively
     */
    private function copyDirectory($source, $destination)
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $sourcePath = $source . '/' . $file;
                $destPath = $destination . '/' . $file;
                
                if (is_dir($sourcePath)) {
                    $this->copyDirectory($sourcePath, $destPath);
                } else {
                    copy($sourcePath, $destPath);
                }
            }
        }
        closedir($dir);
    }

    /**
     * Recursively remove directory
     */
    private function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        $this->rrmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
}

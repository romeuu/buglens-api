<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class StartWorkers extends Command
{
    protected $signature = 'app:start-workers {--workers=3 : Number of worker processes to start}';
    
    protected $description = 'Start multiple queue worker processes for Redis';
    
    private array $processes = [];

    public function handle()
    {
        $numWorkers = $this->option('workers');
        
        $this->info("Starting {$numWorkers} queue workers...");
        
        // Registrar señal para limpieza al cerrar
        pcntl_signal(SIGTERM, [$this, 'cleanup']);
        pcntl_signal(SIGINT, [$this, 'cleanup']);
        
        for ($i = 0; $i < $numWorkers; $i++) {
            $this->startWorker($i);
        }
        
        // Mantener el comando ejecutándose y monitorear los workers
        while (true) {
            $this->monitorWorkers();
            pcntl_signal_dispatch();
            sleep(5);
        }
    }
    
    private function startWorker(int $id): void
    {
        $command = 'php artisan queue:work redis --queue=default --tries=1 --timeout=10800';
        
        $process = Process::start($command);
        
        $this->processes[$id] = [
            'process' => $process,
            'started_at' => now()
        ];
        
        $this->info("Started worker #{$id} with PID: {$process->id()}");
        Log::info("Queue worker started", [
            'worker_id' => $id,
            'pid' => $process->id()
        ]);
    }
    
    private function monitorWorkers(): void
    {
        foreach ($this->processes as $id => $data) {
            $process = $data['process'];
            
            // Verificar si el proceso sigue vivo
            if (!$process->running()) {
                $this->warn("Worker #{$id} (PID: {$process->id()}) died. Restarting...");
                Log::warning("Queue worker died", [
                    'worker_id' => $id,
                    'pid' => $process->id(),
                    'exit_code' => $process->exitCode()
                ]);
                
                // Reiniciar el worker
                $this->startWorker($id);
            }
        }
    }
    
    public function cleanup(): void
    {
        $this->info("\nStopping all workers...");
        
        foreach ($this->processes as $id => $data) {
            $process = $data['process'];
            if ($process->running()) {
                $process->signal(SIGTERM);
                $this->info("Sent SIGTERM to worker #{$id} (PID: {$process->id()})");
            }
        }
        
        // Esperar a que todos los procesos terminen
        foreach ($this->processes as $id => $data) {
            $process = $data['process'];
            $process->wait();
        }
        
        $this->info("All workers stopped.");
        exit(0);
    }
}

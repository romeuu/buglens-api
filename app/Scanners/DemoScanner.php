<?php

namespace App\Scanners;

class DemoScanner implements ScannerInterface
{
    public function name(): string
    {
        return 'demo-scanner';
    }

    public function run(): array
    {
        // Simular algunos resultados de ejemplo
        return [
            [
                'title' => 'Ejemplo de Vulnerabilidad',
                'severity' => 'MEDIUM',
                'description' => 'Esta es una vulnerabilidad de ejemplo para demostración',
                'file' => 'ejemplo.php',
                'line' => 42
            ],
            [
                'title' => 'Otro Ejemplo',
                'severity' => 'LOW',
                'description' => 'Otra vulnerabilidad de ejemplo',
                'file' => 'otro-archivo.php',
                'line' => 123
            ]
        ];
    }
} 
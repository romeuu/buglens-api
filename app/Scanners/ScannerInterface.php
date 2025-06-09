<?php

namespace App\Scanners;

use App\Models\Scan;

interface ScannerInterface
{
    public function name(): string;
    public function run(string $path, Scan $scan): void;
}

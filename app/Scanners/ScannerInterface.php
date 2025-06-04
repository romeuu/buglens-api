<?php

namespace App\Scanners;

interface ScannerInterface
{
    public function name(): string;
    public function run(): array;
}

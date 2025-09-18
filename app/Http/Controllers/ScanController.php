<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scan;

class ScanController extends Controller
{
    public function index(): array {
        return Scan::paginate(10)->toArray();
    }

    public function show(int $id): array {
        return Scan::where('id', $id)->first()->toArray();
    }

    public function showByPluginId(int $pluginId): array {
        return Scan::where('plugin_id', $pluginId)->paginate(10)->toArray();
    }
    
    public function showByPluginSlug(string $pluginSlug): array {
        return Scan::where('plugin_slug', $pluginSlug)->paginate(10)->toArray();
    }
}

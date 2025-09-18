<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plugin;

class PluginController extends Controller
{
    public function index(): array
    {
        return Plugin::paginate(10)->toArray();
    }

    public function showBySlug(string $slug): array
    {
        return Plugin::where('slug', $slug)->first()->toArray();
    }

    public function showByPartialSlug(string $partialSlug): array
    {
        return Plugin::whereRaw('slug LIKE ?', ['%' . addcslashes($partialSlug, '%_') . '%'])
            ->paginate(10)
            ->toArray();
    }

    public function show(int $id): array
    {
        return Plugin::where('id', $id)->first()->toArray();
    }
}
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

    public function findBySlug(string $slug): array
    {
        return Plugin::where('slug', $slug)->first()->toArray();
    }

    public function findById(int $id): array
    {
        return Plugin::where('id', $id)->first()->toArray();
    }
}
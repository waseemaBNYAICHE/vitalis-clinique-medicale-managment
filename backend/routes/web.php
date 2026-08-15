<?php

use Illuminate\Support\Facades\Route;

// API-only backend: the Vue app is served separately by the Vite dev server.
Route::get('/', fn () => response()->json([
    'name' => config('app.name'),
    'environment' => config('app.env'),
    'api' => url('/api'),
]));

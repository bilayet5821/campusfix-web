<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('public.home');
})->name('home');

if (app()->environment('local')) {
    require __DIR__.'/frontend-preview.php';
}

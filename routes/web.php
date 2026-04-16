<?php

declare(strict_types=1);

use App\Http\Controllers\SeededController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/seeded', [SeededController::class, 'index'])->name('seeded.index');
Route::delete('/seeded', [SeededController::class, 'destroyAll'])->name('seeded.destroyAll');
Route::delete('/seeded/city/{city_id}', [SeededController::class, 'destroyByCity'])->name('seeded.destroyByCity');

Route::get('/storage/avatars/{path}', [SeededController::class, 'showAvatar'])
    ->where('path', '.*');

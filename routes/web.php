<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\Backend\Chat\ChatController;
use App\Http\Controllers\Web\Backend\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/chat', [ChatController::class, 'show'])->name('chat');
    Route::get('/messages/{user}', [ChatController::class, 'getMessages']);
    Route::post('/messages/{user}', [ChatController::class, 'sendMessage']);


});

require __DIR__.'/auth.php';

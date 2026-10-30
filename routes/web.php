<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\TicketController;

Route::get('/', [TicketController::class, 'index'])->name('home');
Route::post('/ticket/register', [TicketController::class, 'store'])->name('ticket.store');
Route::get('/ticket/check', [TicketController::class, 'check'])->name('ticket.check');
Route::get('/ticket/{bookingCode}', [TicketController::class, 'show'])->name('ticket.show');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Filament\Resources\TicketResource;
use App\Http\Controllers\Api\TicketController;

Route::get('/tickets', [TicketController::class, 'index'])
    ->name('api.tickets.index');

Route::get('/active', [TicketController::class, 'active'])
    ->name('api.tickets.active');

Route::get('/history', [TicketController::class, 'history'])
    ->name('api.tickets.history');
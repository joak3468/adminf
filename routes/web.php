<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\HomeController;
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }
    return redirect()->route('login');
});

Auth::routes();
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::prefix('clients')->name('clients.')->group(function () {
    Route::get('/', [ClientController::class, 'index'])->name('index');
    Route::get('/create', [ClientController::class, 'create'])->name('create');
    Route::post('/', [ClientController::class, 'store'])->name('store');
    Route::get('/prueba', [ClientController::class, 'prueba'])->name('prueba');
    Route::get('/{client}/edit', [ClientController::class, 'edit'])->name('edit');
    Route::post('/{client}/update', [ClientController::class, 'update'])->name('update');
    Route::delete('/{client}/delete', [ClientController::class, 'destroy'])->name('destroy');
});

Route::prefix('invoices')->name('invoices.')->group(function () {
    Route::get('/', [InvoiceController::class, 'index'])->name('index');
    Route::get('/{client}/confirm', [InvoiceController::class, 'create'])->name('create');
    Route::post('/{client}/create', [InvoiceController::class, 'store'])->name('store');
    Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
    Route::put('/{invoice}/update', [InvoiceController::class, 'update'])->name('update');
    Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');
});
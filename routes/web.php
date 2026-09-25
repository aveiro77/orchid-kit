<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Public\MemberDirectoryController;
use App\Http\Controllers\Public\BusinessDirectoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/anggota', [MemberDirectoryController::class, 'index'])->name('members.index');
Route::get('/anggota/{user}', [MemberDirectoryController::class, 'show'])->name('members.show');

Route::get('/usaha', [BusinessDirectoryController::class, 'index'])->name('businesses.index');
Route::get('/usaha/{business}', [BusinessDirectoryController::class, 'show'])->name('businesses.show');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

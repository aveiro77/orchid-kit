<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Public\MemberDirectoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/anggota', [MemberDirectoryController::class, 'index'])->name('members.index');
Route::get('/anggota/{user}', [MemberDirectoryController::class, 'show'])->name('members.show');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

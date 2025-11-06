<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\TransactionController;

Route::get('/status', function () {
    return response()->json(['status' => 'ok']);
});


Route::get('/balance', [UserController::class, 'balance'])->name('balance');
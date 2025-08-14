<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountDeletionController;

Route::get('/delete-account', [AccountDeletionController::class, 'showPhoneForm'])->name('account.delete.phone.form');
Route::post('/delete-account/send-otp', [AccountDeletionController::class, 'sendOtp'])->name('account.delete.send.otp');
Route::get('/delete-account/verify', [AccountDeletionController::class, 'showOtpForm'])->name('account.delete.otp.form');
Route::post('/delete-account/verify', [AccountDeletionController::class, 'verifyOtp'])->name('account.delete.verify.otp');

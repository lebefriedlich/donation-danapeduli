<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/midtrans/notification', [App\Http\Controllers\DonationController::class, 'paymentStatus']);

<?php

use App\Http\Controllers\PublicCampaignController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicCampaignController::class, 'index'])->name('campaigns.index');
Route::get('/{slug}', [PublicCampaignController::class, 'show'])->name('campaigns.show');
Route::get('/d/{slug}', [PublicCampaignController::class, 'showDonateForm'])->name('campaigns.showForm');

Route::post('/donate/{id}', [PublicCampaignController::class, 'createDonation'])->name('donations.create');

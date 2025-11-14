<?php

use App\Http\Controllers\Api\RfidCheckinController;
use Illuminate\Support\Facades\Route;

Route::middleware('rfid.api.key')->group(function (): void {
    Route::post('/rfid/checkin', [RfidCheckinController::class, 'checkinByReader'])->name('api.rfid.checkin.reader');
    Route::post('/rfid/lane/{lane}/checkin', [RfidCheckinController::class, 'checkinByLane'])->name('api.rfid.checkin.lane');
});


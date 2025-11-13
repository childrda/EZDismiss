<?php

use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AuthorizedPickupController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\HomeroomController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\RfidReaderController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\CallStatusController;
use App\Http\Controllers\ClassroomDisplayController;
use App\Http\Controllers\DistrictAdmin\DashboardController as DistrictDashboardController;
use App\Http\Controllers\DistrictAdmin\LogController as DistrictLogController;
use App\Http\Controllers\DistrictAdmin\PowerSchoolController;
use App\Http\Controllers\DistrictAdmin\SchoolController as DistrictSchoolController;
use App\Http\Controllers\DistrictAdmin\SettingsController;
use App\Http\Controllers\DistrictAdmin\UserController as DistrictUserController;
use App\Http\Controllers\GymDisplayController;
use App\Http\Controllers\MobileEntryController;
use App\Http\Controllers\QueueCheckinController;
use App\Http\Controllers\QueueController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/queue');

Route::middleware(['auth'])->group(function (): void {
    Route::middleware('staff')->group(function (): void {
        Route::get('/queue', [QueueController::class, 'index'])->name('queue.index');
        Route::get('/gym-display', GymDisplayController::class)->name('gym.display');
        Route::get('/mobile-entry', [MobileEntryController::class, 'index'])->name('mobile.entry');
        Route::post('/mobile-entry/search', [MobileEntryController::class, 'search'])->name('mobile.entry.search');
        Route::post('/mobile-entry/checkins', [MobileEntryController::class, 'store'])->name('mobile.entry.store');

        Route::patch('/queue/calls/{call}', [CallStatusController::class, 'update'])->name('queue.calls.update');
        Route::delete('/queue/checkins/{checkin}', [QueueCheckinController::class, 'destroy'])->name('queue.checkins.destroy');
    });

    Route::middleware('teacher')->group(function (): void {
        Route::get('/classroom/{homeroom}', [ClassroomDisplayController::class, 'show'])->name('classroom.show');
    });

    Route::middleware('school.admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::resource('students', StudentController::class)->except(['show']);
        Route::post('students/{student}/link-parent', [StudentController::class, 'linkParent'])->name('students.link-parent');
        Route::delete('students/{student}/unlink-parent/{driver}', [StudentController::class, 'unlinkParent'])->name('students.unlink-parent');

        Route::resource('drivers', DriverController::class)->except(['show']);
        Route::resource('homerooms', HomeroomController::class)->except(['show']);
        Route::resource('authorized-pickups', AuthorizedPickupController::class)->only(['index', 'create', 'store', 'destroy']);
        Route::resource('rfid-readers', RfidReaderController::class)->except(['show']);

        Route::get('/logs', AdminActivityLogController::class)->name('logs.index');
        Route::get('/import', [ImportController::class, 'index'])->name('import.index');
        Route::post('/import/{type}/preview', [ImportController::class, 'preview'])->name('import.preview');
        Route::post('/import/{type}', [ImportController::class, 'store'])->name('import.store');
    });

    Route::middleware('district.admin')->prefix('district-admin')->name('district.')->group(function (): void {
        Route::get('/', DistrictDashboardController::class)->name('dashboard');
        Route::resource('schools', DistrictSchoolController::class);
        Route::resource('users', DistrictUserController::class)->except(['show']);
        Route::get('settings', SettingsController::class)->name('settings');
        Route::get('logs', DistrictLogController::class)->name('logs');
        Route::get('powerschool-import', PowerSchoolController::class)->name('powerschool');
    });
});

require __DIR__.'/auth.php';

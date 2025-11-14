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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/queue');

// WebSocket test page (no auth required)
Route::get('/test-websocket', function () {
    return response()->file(public_path('test-websocket.html'));
});

// RFID API test page (no auth required)
Route::get('/test-rfid-api', function () {
    return response()->file(public_path('test-rfid-api.html'));
});

// CSV Sample files (no auth required)
Route::get('/samples/{type}.csv', function (string $type) {
    $allowedTypes = ['students', 'parents', 'teachers', 'homerooms', 'authorized_pickups'];
    
    if (!in_array($type, $allowedTypes)) {
        abort(404);
    }
    
    $filePath = public_path("samples/{$type}.csv");
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    return response()->download($filePath, "{$type}_sample.csv", [
        'Content-Type' => 'text/csv',
    ]);
})->name('import.sample');

Route::middleware(['auth'])->group(function (): void {
    // District admin school selection
    Route::middleware('district.admin')->post('/district/select-school', function (Request $request) {
        $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
        ]);
        
        session(['district_admin_school_id' => $request->integer('school_id')]);
        
        // Try to redirect back, but fallback to queue if invalid
        try {
            $referrer = $request->header('referer');
            if ($referrer) {
                $referrerPath = parse_url($referrer, PHP_URL_PATH);
                // Only redirect back if it's a valid web route (not an API or POST endpoint)
                if ($referrerPath && !str_starts_with($referrerPath, '/api/') && !str_contains($referrerPath, '/district/select-school')) {
                    return redirect($referrerPath);
                }
            }
        } catch (\Exception $e) {
            // Fall through to default redirect
        }
        
        return redirect()->route('queue.index');
    })->name('district.select-school');

    Route::middleware('staff')->group(function (): void {
        Route::get('/queue', [QueueController::class, 'index'])->name('queue.index');
        Route::get('/gym-display', GymDisplayController::class)->name('gym.display');
        Route::get('/mobile-entry', [MobileEntryController::class, 'index'])->name('mobile.entry');
        Route::post('/mobile-entry/search', [MobileEntryController::class, 'search'])->name('mobile.entry.search');
        Route::post('/mobile-entry/checkins', [MobileEntryController::class, 'store'])->name('mobile.entry.store');

        Route::patch('/queue/calls/{call}', [CallStatusController::class, 'update'])->name('queue.calls.update');
        Route::delete('/queue/checkins/{checkin}', [QueueCheckinController::class, 'destroy'])->name('queue.checkins.destroy');
        Route::post('/queue/checkins/{checkin}/mark-picked-up', [QueueCheckinController::class, 'markPickedUp'])->name('queue.checkins.mark-picked-up');
    });

    Route::middleware('teacher')->group(function (): void {
        Route::get('/classroom/{homeroom}', [ClassroomDisplayController::class, 'show'])->name('classroom.show');
        Route::get('/gym-display', GymDisplayController::class)->name('gym.display');
        Route::patch('/queue/calls/{call}', [CallStatusController::class, 'update'])->name('queue.calls.update');
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
        Route::get('schools/{school}/manage', [DistrictSchoolController::class, 'manage'])->name('schools.manage');
        Route::resource('users', DistrictUserController::class)->except(['show']);
        Route::get('settings', SettingsController::class)->name('settings');
        Route::get('logs', DistrictLogController::class)->name('logs');
        Route::get('powerschool-import', PowerSchoolController::class)->name('powerschool');
    });

    // District admin access to school admin routes with school context
    Route::middleware(['district.admin', 'school.context'])->prefix('district-admin/schools/{school}')->name('district.schools.data.')->group(function (): void {
        Route::get('/students', [\App\Http\Controllers\Admin\StudentController::class, 'index'])->name('students.index');
        Route::get('/students/create', [\App\Http\Controllers\Admin\StudentController::class, 'create'])->name('students.create');
        Route::post('/students', [\App\Http\Controllers\Admin\StudentController::class, 'store'])->name('students.store');
        Route::get('/students/{student}/edit', [\App\Http\Controllers\Admin\StudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [\App\Http\Controllers\Admin\StudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{student}', [\App\Http\Controllers\Admin\StudentController::class, 'destroy'])->name('students.destroy');
        Route::post('/students/{student}/link-parent', [\App\Http\Controllers\Admin\StudentController::class, 'linkParent'])->name('students.link-parent');
        Route::delete('/students/{student}/unlink-parent/{driver}', [\App\Http\Controllers\Admin\StudentController::class, 'unlinkParent'])->name('students.unlink-parent');

        Route::get('/drivers', [\App\Http\Controllers\Admin\DriverController::class, 'index'])->name('drivers.index');
        Route::get('/drivers/create', [\App\Http\Controllers\Admin\DriverController::class, 'create'])->name('drivers.create');
        Route::post('/drivers', [\App\Http\Controllers\Admin\DriverController::class, 'store'])->name('drivers.store');
        Route::get('/drivers/{driver}/edit', [\App\Http\Controllers\Admin\DriverController::class, 'edit'])->name('drivers.edit');
        Route::put('/drivers/{driver}', [\App\Http\Controllers\Admin\DriverController::class, 'update'])->name('drivers.update');
        Route::delete('/drivers/{driver}', [\App\Http\Controllers\Admin\DriverController::class, 'destroy'])->name('drivers.destroy');

        Route::get('/homerooms', [\App\Http\Controllers\Admin\HomeroomController::class, 'index'])->name('homerooms.index');
        Route::get('/homerooms/create', [\App\Http\Controllers\Admin\HomeroomController::class, 'create'])->name('homerooms.create');
        Route::post('/homerooms', [\App\Http\Controllers\Admin\HomeroomController::class, 'store'])->name('homerooms.store');
        Route::get('/homerooms/{homeroom}/edit', [\App\Http\Controllers\Admin\HomeroomController::class, 'edit'])->name('homerooms.edit');
        Route::put('/homerooms/{homeroom}', [\App\Http\Controllers\Admin\HomeroomController::class, 'update'])->name('homerooms.update');
        Route::delete('/homerooms/{homeroom}', [\App\Http\Controllers\Admin\HomeroomController::class, 'destroy'])->name('homerooms.destroy');

        Route::get('/authorized-pickups', [\App\Http\Controllers\Admin\AuthorizedPickupController::class, 'index'])->name('authorized-pickups.index');
        Route::get('/authorized-pickups/create', [\App\Http\Controllers\Admin\AuthorizedPickupController::class, 'create'])->name('authorized-pickups.create');
        Route::post('/authorized-pickups', [\App\Http\Controllers\Admin\AuthorizedPickupController::class, 'store'])->name('authorized-pickups.store');
        Route::delete('/authorized-pickups/{authorizedPickup}', [\App\Http\Controllers\Admin\AuthorizedPickupController::class, 'destroy'])->name('authorized-pickups.destroy');

        Route::get('/rfid-readers', [\App\Http\Controllers\Admin\RfidReaderController::class, 'index'])->name('rfid-readers.index');
        Route::get('/rfid-readers/create', [\App\Http\Controllers\Admin\RfidReaderController::class, 'create'])->name('rfid-readers.create');
        Route::post('/rfid-readers', [\App\Http\Controllers\Admin\RfidReaderController::class, 'store'])->name('rfid-readers.store');
        Route::get('/rfid-readers/{rfidReader}/edit', [\App\Http\Controllers\Admin\RfidReaderController::class, 'edit'])->name('rfid-readers.edit');
        Route::put('/rfid-readers/{rfidReader}', [\App\Http\Controllers\Admin\RfidReaderController::class, 'update'])->name('rfid-readers.update');
        Route::delete('/rfid-readers/{rfidReader}', [\App\Http\Controllers\Admin\RfidReaderController::class, 'destroy'])->name('rfid-readers.destroy');

        Route::get('/import', [\App\Http\Controllers\Admin\ImportController::class, 'index'])->name('import.index');
        Route::post('/import/{type}/preview', [\App\Http\Controllers\Admin\ImportController::class, 'preview'])->name('import.preview');
        Route::post('/import/{type}', [\App\Http\Controllers\Admin\ImportController::class, 'store'])->name('import.store');

        Route::get('/logs', \App\Http\Controllers\Admin\ActivityLogController::class)->name('logs.index');
    });
});

require __DIR__.'/auth.php';

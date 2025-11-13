<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthorizedPickup;
use App\Models\Driver;
use App\Models\Student;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthorizedPickupController extends Controller
{
    public function __construct(protected ActivityLogger $logger)
    {
    }

    public function index(): View
    {
        return view('admin.authorized-pickups.index', [
            'pickups' => AuthorizedPickup::with(['student', 'driver'])->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('admin.authorized-pickups.create', [
            'students' => Student::orderBy('name')->get(),
            'drivers' => Driver::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'driver_id' => ['required', 'exists:drivers,id'],
            'relationship' => ['nullable', 'string', 'max:255'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $pickup = AuthorizedPickup::create($data);

        $this->logger->log('authorized_pickup_change', 'Authorized pickup created', [
            'student_id' => $pickup->student_id,
            'driver_id' => $pickup->driver_id,
            'action' => 'linked',
        ]);

        return redirect()->route('admin.authorized-pickups.index')->with('status', 'Authorized pickup created.');
    }

    public function destroy(AuthorizedPickup $authorizedPickup): RedirectResponse
    {
        $authorizedPickup->delete();

        $this->logger->log('authorized_pickup_change', 'Authorized pickup removed', [
            'student_id' => $authorizedPickup->student_id,
            'driver_id' => $authorizedPickup->driver_id,
            'action' => 'unlinked',
        ]);

        return redirect()->route('admin.authorized-pickups.index')->with('status', 'Authorized pickup removed.');
    }
}


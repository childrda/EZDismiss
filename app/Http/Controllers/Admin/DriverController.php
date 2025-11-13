<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DriverController extends Controller
{
    public function __construct(protected ActivityLogger $logger)
    {
    }

    public function index(): View
    {
        return view('admin.drivers.index', [
            'drivers' => Driver::orderBy('name')->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('admin.drivers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'vehicle_desc' => ['nullable', 'string', 'max:255'],
            'external_id' => ['nullable', 'string', 'max:255'],
            'tag_uid' => ['required', 'string', 'max:255', 'unique:drivers,tag_uid'],
        ]);

        $driver = Driver::create($data);

        $this->logger->log('driver_created', 'Driver created', [
            'driver_id' => $driver->id,
        ]);

        return redirect()->route('admin.drivers.edit', $driver)->with('status', 'Driver created.');
    }

    public function edit(Driver $driver): View
    {
        return view('admin.drivers.edit', [
            'driver' => $driver,
        ]);
    }

    public function update(Request $request, Driver $driver): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'vehicle_desc' => ['nullable', 'string', 'max:255'],
            'external_id' => ['nullable', 'string', 'max:255'],
            'tag_uid' => ['required', 'string', 'max:255', 'unique:drivers,tag_uid,'.$driver->id],
        ]);

        $oldTag = $driver->tag_uid;
        $driver->update($data);

        if ($oldTag !== $driver->tag_uid) {
            $this->logger->log('rfid_assignment', 'RFID tag reassigned', [
                'driver_id' => $driver->id,
                'old_tag_uid' => $oldTag,
                'new_tag_uid' => $driver->tag_uid,
            ]);
        } else {
            $this->logger->log('driver_updated', 'Driver updated', [
                'driver_id' => $driver->id,
            ]);
        }

        return redirect()->route('admin.drivers.edit', $driver)->with('status', 'Driver updated.');
    }

    public function destroy(Driver $driver): RedirectResponse
    {
        $driver->delete();

        $this->logger->log('driver_deleted', 'Driver deleted', [
            'driver_id' => $driver->id,
        ]);

        return redirect()->route('admin.drivers.index')->with('status', 'Driver deleted.');
    }
}


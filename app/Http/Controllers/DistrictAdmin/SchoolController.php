<?php

namespace App\Http\Controllers\DistrictAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolController extends Controller
{
    public function __construct(protected ActivityLogger $logger)
    {
    }

    public function index(): View
    {
        return view('district.schools.index', [
            'schools' => School::orderBy('name')->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('district.schools.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'lane_count' => ['required', 'integer', 'min:1', 'max:10'],
            'lane_color_mode' => ['required', 'in:global,per_lane'],
            'default_lane_behavior' => ['required', 'in:manual,round_robin,rfid_based'],
            'primary_color' => ['nullable', 'string', 'max:7'],
            'api_key' => ['nullable', 'string', 'max:255', 'unique:schools,api_key'],
        ]);

        $school = School::create($data);

        $this->logger->log('school_created', 'School created', [
            'school_id' => $school->id,
        ]);

        return redirect()->route('district.schools.index')->with('status', 'School created.');
    }

    public function edit(School $school): View
    {
        return view('district.schools.edit', [
            'school' => $school,
        ]);
    }

    public function update(Request $request, School $school): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'lane_count' => ['required', 'integer', 'min:1', 'max:10'],
            'lane_color_mode' => ['required', 'in:global,per_lane'],
            'default_lane_behavior' => ['required', 'in:manual,round_robin,rfid_based'],
            'primary_color' => ['nullable', 'string', 'max:7'],
            'api_key' => ['nullable', 'string', 'max:255', 'unique:schools,api_key,'.$school->id],
        ]);

        $school->update($data);

        $this->logger->log('school_updated', 'School updated', [
            'school_id' => $school->id,
        ]);

        return redirect()->route('district.schools.index')->with('status', 'School updated.');
    }

    public function destroy(School $school): RedirectResponse
    {
        // Placeholder for soft disabling schools once the flag exists.
        return redirect()->route('district.schools.index')->with('status', 'Disabling schools is not yet implemented.');
    }
}


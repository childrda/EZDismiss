<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthorizedPickup;
use App\Models\Driver;
use App\Models\Homeroom;
use App\Models\Student;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(protected ActivityLogger $logger)
    {
    }

    public function index(Request $request): View
    {
        $students = Student::with('homeroom')
            ->orderBy('name')
            ->paginate(25);

        return view('admin.students.index', [
            'students' => $students,
        ]);
    }

    public function create(): View
    {
        return view('admin.students.create', [
            'homerooms' => Homeroom::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'powerschool_id' => ['nullable', 'string', 'max:255'],
            'grade' => ['nullable', 'string', 'max:10'],
            'homeroom_id' => ['nullable', 'exists:homerooms,id'],
        ]);

        $student = Student::create($data);

        $this->logger->log('student_created', 'Student created', [
            'student_id' => $student->id,
        ]);

        return redirect()->route('admin.students.edit', $student)->with('status', 'Student created.');
    }

    public function edit(Student $student): View
    {
        $student->load('authorizedPickups.driver');

        return view('admin.students.edit', [
            'student' => $student,
            'homerooms' => Homeroom::orderBy('name')->get(),
            'linkedDrivers' => $student->authorizedPickups->map->driver->filter(),
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'powerschool_id' => ['nullable', 'string', 'max:255'],
            'grade' => ['nullable', 'string', 'max:10'],
            'homeroom_id' => ['nullable', 'exists:homerooms,id'],
        ]);

        $student->update($data);

        $this->logger->log('student_updated', 'Student updated', [
            'student_id' => $student->id,
        ]);

        return redirect()->route('admin.students.edit', $student)->with('status', 'Student updated.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        $this->logger->log('student_deleted', 'Student deleted', [
            'student_id' => $student->id,
        ]);

        return redirect()->route('admin.students.index')->with('status', 'Student deleted.');
    }

    public function linkParent(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'driver_id' => ['required', 'exists:drivers,id'],
            'relationship' => ['nullable', 'string', 'max:255'],
        ]);

        $pickup = AuthorizedPickup::updateOrCreate(
            [
                'student_id' => $student->id,
                'driver_id' => $data['driver_id'],
            ],
            [
                'school_id' => $student->school_id,
                'relationship' => $data['relationship'] ?? null,
            ]
        );

        $this->logger->log('authorized_pickup_change', 'Parent linked to student', [
            'student_id' => $student->id,
            'driver_id' => $pickup->driver_id,
            'action' => 'linked',
        ]);

        return redirect()->route('admin.students.edit', $student)->with('status', 'Parent linked.');
    }

    public function unlinkParent(Student $student, Driver $driver): RedirectResponse
    {
        $pickup = AuthorizedPickup::where('student_id', $student->id)
            ->where('driver_id', $driver->id)
            ->first();

        if ($pickup) {
            $pickup->delete();

            $this->logger->log('authorized_pickup_change', 'Parent unlinked from student', [
                'student_id' => $student->id,
                'driver_id' => $driver->id,
                'action' => 'unlinked',
            ]);
        }

        return redirect()->route('admin.students.edit', $student)->with('status', 'Parent unlinked.');
    }
}


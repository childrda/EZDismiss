@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Edit Student</h1>
                <p class="text-sm text-slate-500">{{ $student->name }}</p>
            </div>
            <form method="POST" action="{{ route('admin.students.destroy', $student) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded border border-red-200 px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete</button>
            </form>
        </div>

        <form method="POST" action="{{ route('admin.students.update', $student) }}" class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">Name</label>
                    <input type="text" name="name" value="{{ old('name', $student->name) }}" required class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">Grade</label>
                    <input type="text" name="grade" value="{{ old('grade', $student->grade) }}" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">PowerSchool ID</label>
                    <input type="text" name="powerschool_id" value="{{ old('powerschool_id', $student->powerschool_id) }}" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">Homeroom</label>
                    <select name="homeroom_id" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                        <option value="">Select homeroom</option>
                        @foreach ($homerooms as $homeroom)
                            <option value="{{ $homeroom->id }}" @selected($student->homeroom_id === $homeroom->id)>{{ $homeroom->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.students.index') }}" class="rounded border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Back</a>
                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save Changes</button>
            </div>
        </form>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800">Linked Parents</h2>
            <p class="text-xs text-slate-500">Parents must have RFID tags for automatic queue participation.</p>

            <div class="mt-4 space-y-4">
                @forelse ($linkedDrivers as $driver)
                    <div class="flex items-center justify-between rounded border border-slate-100 bg-slate-50 p-4">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">{{ $driver->name }}</div>
                            <div class="text-xs text-slate-500">Tag: {{ $driver->tag_uid ?? 'Needs tag' }}</div>
                        </div>
                        <form method="POST" action="{{ route('admin.students.unlink-parent', [$student, $driver]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded border border-red-200 px-3 py-1 text-xs text-red-600 hover:bg-red-50">Remove</button>
                        </form>
                    </div>
                @empty
                    <div class="rounded border border-dashed border-slate-200 bg-slate-50 p-4 text-sm text-slate-500">
                        No parents linked yet.
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                <h3 class="text-sm font-semibold text-slate-700">Add Parent</h3>
                <form method="POST" action="{{ route('admin.students.link-parent', $student) }}" class="mt-3 grid gap-3 md:grid-cols-3">
                    @csrf
                    <input type="text" name="driver_id" placeholder="Driver ID" class="rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none" required>
                    <input type="text" name="relationship" placeholder="Relationship" class="rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                    <button type="submit" class="rounded bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Link Parent</button>
                </form>
                <p class="mt-2 text-xs text-slate-400">Use the driver ID from the Drivers list or search window.</p>
            </div>
        </div>
    </div>
@endsection


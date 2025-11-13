@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">Add Authorized Pickup</h1>
            <p class="text-sm text-slate-500">Link a driver to a student with optional expiration.</p>
        </div>

        <form method="POST" action="{{ route('admin.authorized-pickups.store') }}" class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Student</label>
                <select name="student_id" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">{{ $student->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Driver</label>
                <select name="driver_id" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                    @foreach ($drivers as $driver)
                        <option value="{{ $driver->id }}">{{ $driver->name }} ({{ $driver->tag_uid ?? 'Needs tag' }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">Relationship</label>
                    <input type="text" name="relationship" value="{{ old('relationship') }}" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">Expires At</label>
                    <input type="date" name="expires_at" value="{{ old('expires_at') }}" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.authorized-pickups.index') }}" class="rounded border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save Authorization</button>
            </div>
        </form>
    </div>
@endsection


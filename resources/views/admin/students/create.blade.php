@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">Add Student</h1>
            <p class="text-sm text-slate-500">Create a new student record for this school.</p>
        </div>

        <form method="POST" action="{{ route('admin.students.store') }}" class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">PowerSchool ID</label>
                    <input type="text" name="powerschool_id" value="{{ old('powerschool_id') }}" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">Grade</label>
                    <input type="text" name="grade" value="{{ old('grade') }}" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Homeroom</label>
                <select name="homeroom_id" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                    <option value="">Select homeroom</option>
                    @foreach ($homerooms as $homeroom)
                        <option value="{{ $homeroom->id }}">{{ $homeroom->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.students.index') }}" class="rounded border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save Student</button>
            </div>
        </form>
    </div>
@endsection


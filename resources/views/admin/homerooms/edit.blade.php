@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-lg space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Edit Homeroom</h1>
                <p class="text-sm text-slate-500">{{ $homeroom->name }}</p>
            </div>
            <form method="POST" action="{{ route('admin.homerooms.destroy', $homeroom) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded border border-red-200 px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete</button>
            </form>
        </div>

        <form method="POST" action="{{ route('admin.homerooms.update', $homeroom) }}" class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Name</label>
                <input type="text" name="name" value="{{ old('name', $homeroom->name) }}" required class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Teacher Name</label>
                <input type="text" name="teacher_name" value="{{ old('teacher_name', $homeroom->teacher_name) }}" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.homerooms.index') }}" class="rounded border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Back</a>
                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save Changes</button>
            </div>
        </form>
    </div>
@endsection


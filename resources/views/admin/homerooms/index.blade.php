@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Homerooms</h1>
                <p class="text-sm text-slate-500">Assign students to homerooms and update teacher information.</p>
            </div>
            <a href="{{ route('admin.homerooms.create') }}" class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Add Homeroom</a>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Teacher</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse ($homerooms as $homeroom)
                        <tr>
                            <td class="px-4 py-3">{{ $homeroom->name }}</td>
                            <td class="px-4 py-3">{{ $homeroom->teacher_name }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.homerooms.edit', $homeroom) }}" class="rounded bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-200">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-slate-500">No homerooms yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $homerooms->links() }}
    </div>
@endsection


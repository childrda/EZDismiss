@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Schools</h1>
                <p class="text-sm text-slate-500">Manage school-level settings and lane configurations.</p>
            </div>
            <a href="{{ route('district.schools.create') }}" class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Add School</a>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Lane Count</th>
                        <th class="px-4 py-3">Color Mode</th>
                        <th class="px-4 py-3">Primary Color</th>
                        <th class="px-4 py-3">API Key</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse ($schools as $school)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-800">{{ $school->name }}</div>
                                <div class="text-xs text-slate-500">{{ $school->address }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $school->lane_count }}</td>
                            <td class="px-4 py-3">{{ ucfirst($school->lane_color_mode) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-2 text-xs">
                                    <span class="h-3 w-3 rounded-full" style="background-color: {{ $school->primary_color ?? '#94a3b8' }}"></span>
                                    {{ $school->primary_color ?? 'Default' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $school->api_key ?? 'Not set' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('district.schools.edit', $school) }}" class="rounded bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-200">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-500">No schools configured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $schools->links() }}
    </div>
@endsection


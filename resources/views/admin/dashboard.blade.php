@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">School Admin Dashboard</h1>
            <p class="text-sm text-slate-500">Overview for {{ $school->name }}</p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-blue-100 bg-blue-50 p-5 shadow-sm">
                <div class="text-xs uppercase tracking-wide text-blue-600">Students</div>
                <div class="mt-2 text-3xl font-semibold text-blue-900">{{ $studentCount }}</div>
            </div>
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
                <div class="text-xs uppercase tracking-wide text-emerald-600">Drivers</div>
                <div class="mt-2 text-3xl font-semibold text-emerald-900">{{ $driverCount }}</div>
            </div>
            <div class="rounded-xl border border-amber-100 bg-amber-50 p-5 shadow-sm">
                <div class="text-xs uppercase tracking-wide text-amber-600">Homerooms</div>
                <div class="mt-2 text-3xl font-semibold text-amber-900">{{ $homeroomCount }}</div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 text-lg font-semibold text-slate-800">Recent Activity</div>
            <div class="space-y-3">
                @forelse ($recentLogs as $log)
                    <div class="flex items-start justify-between border-b border-slate-100 pb-3 last:border-0 last:pb-0">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">{{ $log->event_type }}</div>
                            <div class="text-xs text-slate-500">{{ $log->description ?? 'No description' }}</div>
                        </div>
                        <div class="text-xs text-slate-400">{{ $log->created_at?->diffForHumans() }}</div>
                    </div>
                @empty
                    <div class="text-sm text-slate-500">No activity logged yet.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection


@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">Activity Logs</h1>
            <p class="text-sm text-slate-500">Recent actions performed in this school.</p>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Timestamp</th>
                        <th class="px-4 py-3">Event</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">User</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ $log->created_at?->toDayDateTimeString() }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $log->event_type }}</td>
                            <td class="px-4 py-3">{{ $log->description }}</td>
                            <td class="px-4 py-3">{{ $log->user?->name ?? 'System' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">No activity yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $logs->links() }}
    </div>
@endsection


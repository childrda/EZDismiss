@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">District Logs</h1>
                <p class="text-sm text-slate-500">All activity across schools. Filter by school using query parameters.</p>
            </div>
            <form method="GET" class="flex gap-3">
                <input type="number" name="school_id" value="{{ request('school_id') }}" placeholder="School ID" class="w-32 rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                <button type="submit" class="rounded bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700">Filter</button>
            </form>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Timestamp</th>
                        <th class="px-4 py-3">School</th>
                        <th class="px-4 py-3">Event</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">User</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ $log->created_at?->toDayDateTimeString() }}</td>
                            <td class="px-4 py-3">{{ $log->school?->name ?? 'Unassigned' }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $log->event_type }}</td>
                            <td class="px-4 py-3">{{ $log->description }}</td>
                            <td class="px-4 py-3">{{ $log->user?->name ?? 'System' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">No logs to display.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $logs->links() }}
    </div>
@endsection


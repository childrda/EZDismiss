@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Authorized Pickups</h1>
                <p class="text-sm text-slate-500">Manage driver-to-student pickup permissions.</p>
            </div>
            <a href="{{ route('admin.authorized-pickups.create') }}" class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Add Authorization</a>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Driver</th>
                        <th class="px-4 py-3">Relationship</th>
                        <th class="px-4 py-3">Expires</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse ($pickups as $pickup)
                        <tr>
                            <td class="px-4 py-3">{{ $pickup->student?->name }}</td>
                            <td class="px-4 py-3">{{ $pickup->driver?->name }}</td>
                            <td class="px-4 py-3">{{ $pickup->relationship }}</td>
                            <td class="px-4 py-3">{{ optional($pickup->expires_at)->toFormattedDateString() ?? 'No expiry' }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.authorized-pickups.destroy', $pickup) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded bg-red-100 px-3 py-1 text-xs font-semibold text-red-600 hover:bg-red-200">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">No authorized pickups yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $pickups->links() }}
    </div>
@endsection


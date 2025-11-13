@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Drivers & Parents</h1>
                <p class="text-sm text-slate-500">Assign RFID tags and manage contact details.</p>
            </div>
            <a href="{{ route('admin.drivers.create') }}" class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Add Driver</a>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">Tag UID</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse ($drivers as $driver)
                        <tr @class(['bg-red-50' => empty($driver->tag_uid)])>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-800">{{ $driver->name }}</div>
                                <div class="text-xs text-slate-500">External ID: {{ $driver->external_id ?? 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $driver->email }}</td>
                            <td class="px-4 py-3">{{ $driver->phone }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'rounded px-2 py-1 text-xs font-semibold',
                                    'bg-red-100 text-red-700' => empty($driver->tag_uid),
                                    'bg-emerald-100 text-emerald-700' => !empty($driver->tag_uid),
                                ])>
                                    {{ $driver->tag_uid ?? 'Needs tag' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.drivers.edit', $driver) }}" class="rounded bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-200">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">No drivers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $drivers->links() }}
    </div>
@endsection


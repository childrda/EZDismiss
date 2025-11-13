@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">RFID Readers</h1>
                <p class="text-sm text-slate-500">Configure reader endpoints per lane.</p>
            </div>
            <a href="{{ route('admin.rfid-readers.create') }}" class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Add Reader</a>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Lane</th>
                        <th class="px-4 py-3">Endpoint</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse ($readers as $reader)
                        <tr>
                            <td class="px-4 py-3">{{ $reader->name }}</td>
                            <td class="px-4 py-3">Lane {{ $reader->lane }}</td>
                            <td class="px-4 py-3">
                                <div class="font-mono text-xs text-slate-500">{{ $reader->endpoint_type }}://{{ $reader->ip_address ?? 'configured remotely' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'rounded px-2 py-1 text-xs font-semibold',
                                    'bg-emerald-100 text-emerald-700' => $reader->enabled,
                                    'bg-red-100 text-red-700' => !$reader->enabled,
                                ])>
                                    {{ $reader->enabled ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.rfid-readers.edit', $reader) }}" class="rounded bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-200">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">No RFID readers configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $readers->links() }}
    </div>
@endsection


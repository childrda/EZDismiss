@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">{{ ucfirst($type) }} Import Preview</h1>
            <p class="text-sm text-slate-500">Review the planned actions before committing this import.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-4 text-center">
                <div class="text-sm font-semibold text-emerald-600">Will Create</div>
                <div class="mt-2 text-2xl font-bold text-emerald-900">{{ $summary['created'] }}</div>
            </div>
            <div class="rounded-lg border border-blue-100 bg-blue-50 p-4 text-center">
                <div class="text-sm font-semibold text-blue-600">Will Update</div>
                <div class="mt-2 text-2xl font-bold text-blue-900">{{ $summary['updated'] }}</div>
            </div>
            <div class="rounded-lg border border-yellow-100 bg-yellow-50 p-4 text-center">
                <div class="text-sm font-semibold text-yellow-600">Will Skip</div>
                <div class="mt-2 text-2xl font-bold text-yellow-900">{{ $summary['skipped'] }}</div>
            </div>
            <div class="rounded-lg border border-red-100 bg-red-50 p-4 text-center">
                <div class="text-sm font-semibold text-red-600">Errors</div>
                <div class="mt-2 text-2xl font-bold text-red-900">{{ count($summary['errors']) }}</div>
            </div>
        </div>

        @if (!empty($summary['errors']))
            <div class="rounded border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <div class="font-semibold">Issues Detected</div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($summary['errors'] as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3 text-sm font-semibold text-slate-600">Row Preview (first 50 rows)</div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-2 text-left">Identifier</th>
                            <th class="px-4 py-2 text-left">Name</th>
                            <th class="px-4 py-2 text-left">Action</th>
                            @if ($type === 'parents')
                                <th class="px-4 py-2 text-left">Tag UID</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @foreach (array_slice($summary['rows'], 0, 50) as $row)
                            <tr>
                                <td class="px-4 py-2">{{ $row['identifier'] ?? 'N/A' }}</td>
                                <td class="px-4 py-2">{{ $row['name'] ?? '—' }}</td>
                                <td class="px-4 py-2">
                                    <span @class([
                                        'rounded px-2 py-1 text-xs font-semibold',
                                        'bg-emerald-100 text-emerald-700' => $row['action'] === 'created',
                                        'bg-blue-100 text-blue-700' => $row['action'] === 'updated',
                                        'bg-yellow-100 text-yellow-700' => $row['action'] === 'skipped',
                                    ])>
                                        {{ ucfirst($row['action']) }}
                                    </span>
                                </td>
                                @if ($type === 'parents')
                                    <td class="px-4 py-2">{{ $row['tag_uid'] ?? '—' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.import.store', $type) }}" enctype="multipart/form-data" class="flex items-center justify-between rounded border border-slate-200 bg-white p-4 shadow-sm">
            @csrf
            <input type="hidden" name="replay" value="true">
            <label class="text-sm text-slate-600">Upload the same file again to confirm import:</label>
            <input type="file" name="file" accept=".csv,text/csv" required class="w-64 rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            <button type="submit" class="rounded bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Run Import</button>
        </form>
    </div>
@endsection


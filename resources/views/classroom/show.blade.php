@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">{{ $homeroom->name }} Classroom</h1>
            <p class="text-sm text-slate-500">Teacher: {{ $homeroom->teacher_name }}</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($students as $student)
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-lg font-semibold text-slate-800">{{ $student['name'] }}</div>
                            <div class="text-sm text-slate-500">
                                Lane: {{ $student['lane'] ?? '—' }} • Position: {{ $student['position'] ?? '—' }}
                            </div>
                        </div>
                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold uppercase text-blue-700">
                            {{ $student['indicator'] }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($canViewQueue)
            <div class="rounded border border-dashed border-blue-300 bg-blue-50 p-4 text-sm text-blue-700">
                Full queue access enabled for this classroom. Monitor the live queue from the admin menu.
            </div>
        @endif
    </div>
@endsection


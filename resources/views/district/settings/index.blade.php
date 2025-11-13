@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">System Settings</h1>
            <p class="text-sm text-slate-500">High-level configuration for CarLineManager environment.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($config as $key => $value)
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="text-xs uppercase tracking-wide text-slate-400">{{ str_replace('_', ' ', $key) }}</div>
                    <div class="mt-2 text-lg font-semibold text-slate-800">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        <div class="rounded border border-blue-200 bg-blue-50 p-4 text-sm text-blue-700">
            <div class="font-semibold">Coming Soon</div>
            <p class="mt-2">District-wide settings (PowerSchool integration, import schedules, lane defaults) will surface here.</p>
        </div>
    </div>
@endsection


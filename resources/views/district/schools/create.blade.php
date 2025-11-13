@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">Add School</h1>
            <p class="text-sm text-slate-500">Create a new school instance with lane settings.</p>
        </div>

        <form method="POST" action="{{ route('district.schools.store') }}" class="grid gap-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm md:grid-cols-2">
            @csrf

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-slate-600">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Address</label>
                <input type="text" name="address" value="{{ old('address') }}" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Lane Count</label>
                <input type="number" name="lane_count" value="{{ old('lane_count', 1) }}" min="1" max="10" required class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Lane Color Mode</label>
                <select name="lane_color_mode" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                    <option value="per_lane">Per Lane</option>
                    <option value="global">Global</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Default Lane Behavior</label>
                <select name="default_lane_behavior" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                    <option value="manual">Manual</option>
                    <option value="round_robin">Round Robin</option>
                    <option value="rfid_based">RFID Based</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Primary Color</label>
                <input type="text" name="primary_color" value="{{ old('primary_color') }}" placeholder="#0ea5e9" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">API Key</label>
                <input type="text" name="api_key" value="{{ old('api_key') }}" placeholder="Optional API key for RFID integrations" class="w-full rounded border border-slate-300 px-3 py-2 font-mono focus:border-blue-500 focus:outline-none">
            </div>

            <div class="md:col-span-2 flex justify-end gap-3">
                <a href="{{ route('district.schools.index') }}" class="rounded border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Create School</button>
            </div>
        </form>
    </div>
@endsection


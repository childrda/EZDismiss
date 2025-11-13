@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Edit School</h1>
                <p class="text-sm text-slate-500">{{ $school->name }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('district.schools.update', $school) }}" class="grid gap-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm md:grid-cols-2">
            @csrf
            @method('PUT')

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-slate-600">Name</label>
                <input type="text" name="name" value="{{ old('name', $school->name) }}" required class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Address</label>
                <input type="text" name="address" value="{{ old('address', $school->address) }}" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $school->phone) }}" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Lane Count</label>
                <input type="number" name="lane_count" value="{{ old('lane_count', $school->lane_count) }}" min="1" max="10" required class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Lane Color Mode</label>
                <select name="lane_color_mode" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                    <option value="per_lane" @selected($school->lane_color_mode === 'per_lane')>Per Lane</option>
                    <option value="global" @selected($school->lane_color_mode === 'global')>Global</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Default Lane Behavior</label>
                <select name="default_lane_behavior" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                    <option value="manual" @selected($school->default_lane_behavior === 'manual')>Manual</option>
                    <option value="round_robin" @selected($school->default_lane_behavior === 'round_robin')>Round Robin</option>
                    <option value="rfid_based" @selected($school->default_lane_behavior === 'rfid_based')>RFID Based</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Primary Color</label>
                <input type="text" name="primary_color" value="{{ old('primary_color', $school->primary_color) }}" placeholder="#0ea5e9" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">API Key</label>
                <input type="text" name="api_key" value="{{ old('api_key', $school->api_key) }}" class="w-full rounded border border-slate-300 px-3 py-2 font-mono focus:border-blue-500 focus:outline-none">
            </div>

            <div class="md:col-span-2 flex justify-end gap-3">
                <a href="{{ route('district.schools.index') }}" class="rounded border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Back</a>
                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save Changes</button>
            </div>
        </form>

        <div class="rounded border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700">
            School disabling is coming soon. For now, contact support to deactivate a school.
        </div>
    </div>
@endsection


@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">Add RFID Reader</h1>
            <p class="text-sm text-slate-500">Provide configuration for a new lane reader.</p>
        </div>

        <form method="POST" action="{{ route('admin.rfid-readers.store') }}" class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">Lane</label>
                    <input type="number" name="lane" value="{{ old('lane') }}" min="1" required class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">Endpoint Type</label>
                    <select name="endpoint_type" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                        <option value="http">HTTP</option>
                        <option value="mqtt">MQTT</option>
                        <option value="tcp">TCP</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">IP or Hostname</label>
                <input type="text" name="ip_address" value="{{ old('ip_address') }}" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">API Key</label>
                <input type="text" name="api_key" value="{{ old('api_key') }}" required class="w-full rounded border border-slate-300 px-3 py-2 font-mono focus:border-blue-500 focus:outline-none">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="enabled" value="1" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <label class="text-sm text-slate-600">Reader enabled</label>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.rfid-readers.index') }}" class="rounded border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save Reader</button>
            </div>
        </form>
    </div>
@endsection


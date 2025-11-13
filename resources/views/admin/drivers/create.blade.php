@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">Add Driver</h1>
            <p class="text-sm text-slate-500">Create parent/driver profiles with RFID tags.</p>
        </div>

        <form method="POST" action="{{ route('admin.drivers.store') }}" class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">External ID</label>
                    <input type="text" name="external_id" value="{{ old('external_id') }}" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">Vehicle Description</label>
                    <input type="text" name="vehicle_desc" value="{{ old('vehicle_desc') }}" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">RFID Tag UID</label>
                <input type="text" name="tag_uid" value="{{ old('tag_uid') }}" required class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.drivers.index') }}" class="rounded border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save Driver</button>
            </div>
        </form>
    </div>
@endsection


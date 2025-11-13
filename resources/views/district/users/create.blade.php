@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">Add User</h1>
            <p class="text-sm text-slate-500">Create district or school-level accounts.</p>
        </div>

        <form method="POST" action="{{ route('district.users.store') }}" x-data="{ role: '{{ old('role', 'district_admin') }}' }" class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Password</label>
                <input type="password" name="password" required class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Role</label>
                <select name="role" x-model="role" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                    <option value="district_admin">District Admin</option>
                    <option value="school_admin">School Admin</option>
                    <option value="teacher">Teacher</option>
                    <option value="staff">Staff</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">School</label>
                <select name="school_id" :disabled="role === 'district_admin'" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                    <option value="">District Wide</option>
                    @foreach ($schools as $school)
                        <option value="{{ $school->id }}" @selected(old('school_id') == $school->id)>{{ $school->name }}</option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-slate-400">School is ignored for district admins.</p>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('district.users.index') }}" class="rounded border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Create User</button>
            </div>
        </form>
    </div>
@endsection


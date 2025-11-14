@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Manage School: {{ $school->name }}</h1>
                <p class="text-sm text-slate-500">Edit students, drivers, homerooms, and other school-specific data.</p>
            </div>
            <a href="{{ route('district.schools.index') }}" class="rounded border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Back to Schools</a>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <a href="{{ route('district.schools.data.students.index', $school) }}" class="rounded-xl border border-blue-100 bg-blue-50 p-6 shadow-sm hover:bg-blue-100 transition">
                <div class="text-lg font-semibold text-blue-900">Students</div>
                <div class="mt-2 text-sm text-blue-600">Manage student records</div>
            </a>

            <a href="{{ route('district.schools.data.drivers.index', $school) }}" class="rounded-xl border border-emerald-100 bg-emerald-50 p-6 shadow-sm hover:bg-emerald-100 transition">
                <div class="text-lg font-semibold text-emerald-900">Drivers</div>
                <div class="mt-2 text-sm text-emerald-600">Manage parent/driver records</div>
            </a>

            <a href="{{ route('district.schools.data.homerooms.index', $school) }}" class="rounded-xl border border-amber-100 bg-amber-50 p-6 shadow-sm hover:bg-amber-100 transition">
                <div class="text-lg font-semibold text-amber-900">Homerooms</div>
                <div class="mt-2 text-sm text-amber-600">Manage homeroom assignments</div>
            </a>

            <a href="{{ route('district.schools.data.authorized-pickups.index', $school) }}" class="rounded-xl border border-purple-100 bg-purple-50 p-6 shadow-sm hover:bg-purple-100 transition">
                <div class="text-lg font-semibold text-purple-900">Authorized Pickups</div>
                <div class="mt-2 text-sm text-purple-600">Link students to drivers</div>
            </a>

            <a href="{{ route('district.schools.data.rfid-readers.index', $school) }}" class="rounded-xl border border-indigo-100 bg-indigo-50 p-6 shadow-sm hover:bg-indigo-100 transition">
                <div class="text-lg font-semibold text-indigo-900">RFID Readers</div>
                <div class="mt-2 text-sm text-indigo-600">Configure RFID scanners</div>
            </a>

            <a href="{{ route('district.schools.data.import.index', $school) }}" class="rounded-xl border border-slate-100 bg-slate-50 p-6 shadow-sm hover:bg-slate-100 transition">
                <div class="text-lg font-semibold text-slate-900">Import Data</div>
                <div class="mt-2 text-sm text-slate-600">Bulk import from CSV</div>
            </a>

            <a href="{{ route('district.schools.data.logs.index', $school) }}" class="rounded-xl border border-gray-100 bg-gray-50 p-6 shadow-sm hover:bg-gray-100 transition">
                <div class="text-lg font-semibold text-gray-900">Activity Logs</div>
                <div class="mt-2 text-sm text-gray-600">View school activity history</div>
            </a>
        </div>
    </div>
@endsection


@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">CSV Imports</h1>
            <p class="text-sm text-slate-500">Upload CSV files to batch update students, parents, and more. Each import runs in the context of your school.</p>
        </div>

        @php
            $imports = [
                'students' => 'Students',
                'parents' => 'Parents / Drivers',
                'teachers' => 'Teachers',
                'homerooms' => 'Homerooms',
                'authorized_pickups' => 'Authorized Pickups',
            ];
        @endphp

        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($imports as $type => $label)
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-3 text-lg font-semibold text-slate-800">{{ $label }}</div>
                    <form method="POST" action="{{ route('admin.import.preview', $type) }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <input type="file" name="file" accept=".csv,text/csv" required class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                        <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Preview {{ $label }} Import</button>
                    </form>
                </div>
            @endforeach
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm text-sm text-slate-600">
            <h2 class="text-lg font-semibold text-slate-800">Import Tips</h2>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                <li>Use UTF-8 encoded CSV files with headers. Header names can be lowercase or spaced.</li>
                <li>Students match on <code class="rounded bg-slate-100 px-1">powerschool_id</code>. Parents match on email or <code class="rounded bg-slate-100 px-1">parent_id</code>.</li>
                <li>Preview step highlights row actions (create, update, skip) before applying.</li>
                <li>Every driver should include <code class="rounded bg-slate-100 px-1">tag_uid</code> for RFID queue support. Missing tags are flagged.</li>
            </ul>
        </div>
    </div>
@endsection


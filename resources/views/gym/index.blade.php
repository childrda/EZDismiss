@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Gym Pickup Display</h1>
            <p class="text-sm text-slate-500">School: {{ $school->name }}</p>
        </div>

        <div class="space-y-6">
            @foreach ($lanes as $laneNumber => $checkins)
                <div>
                    <div class="mb-3 text-xl font-semibold text-blue-600">Lane {{ $laneNumber }}</div>
                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($checkins as $checkin)
                            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="text-lg font-semibold text-slate-900">{{ $checkin['driver'] }}</div>
                                <div class="text-sm text-slate-500 mb-3">Position {{ $checkin['position'] }} • Status {{ strtoupper($checkin['status']) }}</div>
                                <ul class="space-y-1 text-sm text-slate-700">
                                    @foreach ($checkin['students'] as $student)
                                        <li class="rounded bg-slate-100 px-3 py-2">{{ $student }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection


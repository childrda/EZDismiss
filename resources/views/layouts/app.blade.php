<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CarLineManager') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900">
        <div class="flex min-h-screen">
            <aside class="w-64 bg-white shadow-lg hidden md:flex flex-col">
                <div class="p-6 text-xl font-semibold text-blue-600 flex items-center justify-between">
                    {{ config('app.name', 'CarLineManager') }}
                </div>
                
                @auth
                    <div class="px-6 pb-4 border-b border-slate-200">
                        <div class="text-sm font-medium text-slate-900">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-slate-500 mt-1">
                            @if(auth()->user()->isDistrictAdmin())
                                District Admin
                            @elseif(auth()->user()->isSchoolAdmin())
                                School Admin
                                @if(auth()->user()->school)
                                    • {{ auth()->user()->school->name }}
                                @endif
                            @elseif(auth()->user()->isTeacher())
                                Teacher
                                @if(auth()->user()->school)
                                    • {{ auth()->user()->school->name }}
                                @endif
                            @elseif(auth()->user()->isStaff())
                                Staff
                                @if(auth()->user()->school)
                                    • {{ auth()->user()->school->name }}
                                @endif
                            @endif
                        </div>
                        <div class="text-xs text-slate-400 mt-1">{{ auth()->user()->email }}</div>
                    </div>
                @endauth
                
                <nav class="flex-1 px-4 space-y-2">
                    @php
                        $user = auth()->user();
                        $teacherHomeroom = $user?->teacherHomeroom();
                    @endphp

                    @if($user && ($user->isStaff() || $user->isSchoolAdmin() || $user->isDistrictAdmin()))
                        @if($user->isDistrictAdmin())
                            @php
                                $schools = \App\Models\School::orderBy('name')->get();
                                $selectedSchoolId = session('district_admin_school_id', $schools->first()?->id);
                            @endphp
                            <div class="mb-4">
                                <label class="mb-2 block text-xs font-medium text-slate-500">Select School</label>
                                <form method="POST" action="{{ route('district.select-school') }}" class="mb-2">
                                    @csrf
                                    <select name="school_id" onchange="this.form.submit()" class="w-full rounded border border-slate-300 px-2 py-1.5 text-xs focus:border-blue-500 focus:outline-none">
                                        @foreach($schools as $school)
                                            <option value="{{ $school->id }}" {{ $school->id == $selectedSchoolId ? 'selected' : '' }}>{{ $school->name }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        @endif
                        <a href="{{ route('queue.index') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('queue.*') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">Queue</a>
                        <a href="{{ route('gym.display') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('gym.display') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">Gym Display</a>
                        <a href="{{ route('mobile.entry') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('mobile.entry*') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">Mobile Entry</a>
                    @endif

                    @if($user && $user->isTeacher() && $teacherHomeroom)
                        <a href="{{ route('classroom.show', $teacherHomeroom) }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('classroom.show') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">Classroom</a>
                    @endif

                    @if($user && $user->isSchoolAdmin())
                        <div class="mt-4 text-xs uppercase tracking-wide text-slate-400">School Admin</div>
                        <a href="{{ route('admin.dashboard') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">Dashboard</a>
                        <a href="{{ route('admin.students.index') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('admin.students.*') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">Students</a>
                        <a href="{{ route('admin.drivers.index') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('admin.drivers.*') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">Drivers</a>
                        <a href="{{ route('admin.homerooms.index') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('admin.homerooms.*') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">Homerooms</a>
                        <a href="{{ route('admin.authorized-pickups.index') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('admin.authorized-pickups.*') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">Authorized Pickups</a>
                        <a href="{{ route('admin.rfid-readers.index') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('admin.rfid-readers.*') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">RFID Readers</a>
                        <a href="{{ route('admin.import.index') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('admin.import.*') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">Import</a>
                        <a href="{{ route('admin.logs.index') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('admin.logs.*') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">Activity Logs</a>
                    @endif

                    @if($user && $user->isDistrictAdmin())
                        <div class="mt-4 text-xs uppercase tracking-wide text-slate-400">District Admin</div>
                        <a href="{{ route('district.dashboard') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('district.dashboard') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">Dashboard</a>
                        <a href="{{ route('district.schools.index') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('district.schools.*') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">Schools</a>
                        <a href="{{ route('district.users.index') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('district.users.*') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">Users</a>
                        <a href="{{ route('district.settings') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('district.settings') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">Settings</a>
                        <a href="{{ route('district.logs') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('district.logs') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">Activity Logs</a>
                        <a href="{{ route('district.powerschool') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('district.powerschool') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">PowerSchool Import</a>
                    @endif
                </nav>
                <form method="POST" action="{{ route('logout') }}" class="p-4">
                    @csrf
                    <button type="submit" class="w-full rounded bg-red-500 px-4 py-2 text-white hover:bg-red-600">Logout</button>
                </form>
            </aside>

            <main class="flex-1">
                <header class="bg-white shadow-sm md:hidden">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200">
                        <div>
                            <div class="text-lg font-semibold text-blue-600">{{ config('app.name', 'CarLineManager') }}</div>
                            @auth
                                <div class="text-xs text-slate-600 mt-0.5">
                                    {{ auth()->user()->name }}
                                    @if(auth()->user()->school)
                                        • {{ auth()->user()->school->name }}
                                    @endif
                                </div>
                            @endauth
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded bg-red-500 px-3 py-1 text-sm text-white">Logout</button>
                        </form>
                    </div>
                </header>

                <div class="p-6">
                    @if(session('status'))
                        <div class="mb-4 rounded border border-green-200 bg-green-50 p-4 text-green-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 rounded border border-red-200 bg-red-50 p-4 text-red-600">
                            <div class="font-semibold mb-2">We ran into some issues:</div>
                            <ul class="list-disc space-y-1 pl-5 text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{ $slot ?? '' }}
                    @yield('content')
                </div>
            </main>
        </div>
    </body>
</html>


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
                <nav class="flex-1 px-4 space-y-2">
                    <a href="{{ route('queue.index') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('queue.*') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">Queue</a>
                    <a href="{{ route('gym.display') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('gym.display') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">Gym Display</a>
                    <a href="{{ route('mobile.entry') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('mobile.entry*') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">Mobile Entry</a>

                    @can('viewAny', App\Models\Student::class)
                        <div class="mt-4 text-xs uppercase tracking-wide text-slate-400">Admin</div>
                        <a href="{{ route('admin.dashboard') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('admin.*') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">School Admin</a>
                    @endcan

                    @if(auth()->user()?->isDistrictAdmin())
                        <div class="mt-4 text-xs uppercase tracking-wide text-slate-400">District</div>
                        <a href="{{ route('district.dashboard') }}" class="block rounded px-3 py-2 hover:bg-blue-50 {{ request()->routeIs('district.*') ? 'bg-blue-100 text-blue-700' : 'text-slate-600' }}">District Admin</a>
                    @endif
                </nav>
                <form method="POST" action="{{ route('logout') }}" class="p-4">
                    @csrf
                    <button type="submit" class="w-full rounded bg-red-500 px-4 py-2 text-white hover:bg-red-600">Logout</button>
                </form>
            </aside>

            <main class="flex-1">
                <header class="bg-white shadow-sm md:hidden">
                    <div class="flex items-center justify-between px-4 py-3">
                        <div class="text-lg font-semibold text-blue-600">{{ config('app.name', 'CarLineManager') }}</div>
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


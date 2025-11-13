@extends('layouts.guest')

@section('content')
    <div class="mx-auto max-w-md rounded bg-white p-8 shadow">
        <h1 class="mb-6 text-2xl font-semibold text-center text-blue-600">Sign in</h1>

        <form method="POST" action="{{ route('login.store') }}" class="space-y-6">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-600">Password</label>
                <input type="password" name="password" required class="w-full rounded border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
            </div>

            <div class="flex items-center justify-between">
                <label class="inline-flex items-center text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2">Remember me</span>
                </label>
            </div>

            <button type="submit" class="w-full rounded bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">Sign in</button>
        </form>
    </div>
@endsection


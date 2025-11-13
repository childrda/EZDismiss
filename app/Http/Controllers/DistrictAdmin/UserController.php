<?php

namespace App\Http\Controllers\DistrictAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('district.users.index', [
            'users' => User::with('school')->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('district.users.create', [
            'schools' => School::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', 'in:district_admin,school_admin,teacher,staff'],
            'school_id' => ['nullable', 'exists:schools,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'school_id' => $data['role'] === 'district_admin' ? null : $data['school_id'],
        ]);

        return redirect()->route('district.users.edit', $user)->with('status', 'User created.');
    }

    public function edit(User $user): View
    {
        return view('district.users.edit', [
            'user' => $user,
            'schools' => School::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', Password::defaults()],
            'role' => ['required', 'in:district_admin,school_admin,teacher,staff'],
            'school_id' => ['nullable', 'exists:schools,id'],
        ]);

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'school_id' => $data['role'] === 'district_admin' ? null : $data['school_id'],
        ]);

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('district.users.edit', $user)->with('status', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('district.users.index')->with('status', 'User deleted.');
    }
}


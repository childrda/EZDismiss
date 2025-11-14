<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Homeroom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();
        
        // For teachers, always use the redirectPathFor method to ensure they go to accessible routes
        if ($user->isTeacher()) {
            return redirect($this->redirectPathFor($user));
        }

        // For other users, use intended redirect with fallback
        return redirect()->intended($this->redirectPathFor($user));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function redirectPathFor($user): string
    {
        if ($user->isDistrictAdmin() || $user->isSchoolAdmin() || $user->isStaff()) {
            return route('queue.index');
        }

        if ($user->isTeacher()) {
            // First try to find by teacher_id (new way)
            $homeroom = Homeroom::query()
                ->where('school_id', $user->school_id)
                ->where('teacher_id', $user->id)
                ->first();

            // Fallback to teacher_name matching (for backward compatibility)
            if (!$homeroom) {
                $homeroom = Homeroom::query()
                    ->where('school_id', $user->school_id)
                    ->where('teacher_name', $user->name)
                    ->first();
            }

            if ($homeroom) {
                return route('classroom.show', $homeroom);
            }

            return route('gym.display');
        }

        return route('queue.index');
    }
}


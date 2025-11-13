<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Homeroom;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeroomController extends Controller
{
    public function __construct(protected ActivityLogger $logger)
    {
    }

    public function index(): View
    {
        return view('admin.homerooms.index', [
            'homerooms' => Homeroom::orderBy('name')->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('admin.homerooms.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'teacher_name' => ['nullable', 'string', 'max:255'],
        ]);

        $homeroom = Homeroom::create($data);

        $this->logger->log('homeroom_created', 'Homeroom created', [
            'homeroom_id' => $homeroom->id,
        ]);

        return redirect()->route('admin.homerooms.index')->with('status', 'Homeroom created.');
    }

    public function edit(Homeroom $homeroom): View
    {
        return view('admin.homerooms.edit', [
            'homeroom' => $homeroom,
        ]);
    }

    public function update(Request $request, Homeroom $homeroom): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'teacher_name' => ['nullable', 'string', 'max:255'],
        ]);

        $homeroom->update($data);

        $this->logger->log('homeroom_updated', 'Homeroom updated', [
            'homeroom_id' => $homeroom->id,
        ]);

        return redirect()->route('admin.homerooms.index')->with('status', 'Homeroom updated.');
    }

    public function destroy(Homeroom $homeroom): RedirectResponse
    {
        $homeroom->delete();

        $this->logger->log('homeroom_deleted', 'Homeroom deleted', [
            'homeroom_id' => $homeroom->id,
        ]);

        return redirect()->route('admin.homerooms.index')->with('status', 'Homeroom deleted.');
    }
}


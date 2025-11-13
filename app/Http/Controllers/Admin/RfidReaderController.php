<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RfidReader;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RfidReaderController extends Controller
{
    public function __construct(protected ActivityLogger $logger)
    {
    }

    public function index(): View
    {
        return view('admin.rfid-readers.index', [
            'readers' => RfidReader::orderBy('lane')->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('admin.rfid-readers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'lane' => ['required', 'integer', 'min:1'],
            'endpoint_type' => ['required', 'in:http,mqtt,tcp'],
            'ip_address' => ['nullable', 'string', 'max:255'],
            'api_key' => ['required', 'string', 'max:255'],
            'enabled' => ['boolean'],
        ]);

        $reader = RfidReader::create($data + ['enabled' => $request->boolean('enabled', true)]);

        $this->logger->log('rfid_reader_created', 'RFID reader created', [
            'reader_id' => $reader->id,
            'lane' => $reader->lane,
        ]);

        return redirect()->route('admin.rfid-readers.index')->with('status', 'RFID reader created.');
    }

    public function edit(RfidReader $rfidReader): View
    {
        return view('admin.rfid-readers.edit', [
            'reader' => $rfidReader,
        ]);
    }

    public function update(Request $request, RfidReader $rfidReader): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'lane' => ['required', 'integer', 'min:1'],
            'endpoint_type' => ['required', 'in:http,mqtt,tcp'],
            'ip_address' => ['nullable', 'string', 'max:255'],
            'api_key' => ['required', 'string', 'max:255'],
            'enabled' => ['boolean'],
        ]);

        $rfidReader->update($data + ['enabled' => $request->boolean('enabled', true)]);

        $this->logger->log('rfid_reader_updated', 'RFID reader updated', [
            'reader_id' => $rfidReader->id,
            'lane' => $rfidReader->lane,
        ]);

        return redirect()->route('admin.rfid-readers.index')->with('status', 'RFID reader updated.');
    }

    public function destroy(RfidReader $rfidReader): RedirectResponse
    {
        $rfidReader->delete();

        $this->logger->log('rfid_reader_deleted', 'RFID reader deleted', [
            'reader_id' => $rfidReader->id,
        ]);

        return redirect()->route('admin.rfid-readers.index')->with('status', 'RFID reader deleted.');
    }
}


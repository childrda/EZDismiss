<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\CsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function __construct(
        protected CsvImportService $importService,
        protected ActivityLogger $logger,
    )
    {
    }

    public function index(): View
    {
        return view('admin.import.index');
    }

    public function preview(Request $request, string $type): View
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $summary = $this->importService->preview($type, $data['file'], $request->user()->school);

        return view('admin.import.preview', [
            'type' => $type,
            'summary' => $summary,
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $school = $request->user()->school;
        $summary = $this->importService->import($type, $data['file'], $school);

        $this->logger->log('csv_import', ucfirst($type).' import performed', [
            'file_type' => $type,
            'created' => $summary['created'],
            'updated' => $summary['updated'],
            'skipped' => $summary['skipped'],
        ]);

        return redirect()->route('admin.import.index')->with('status', sprintf(
            '%s import completed. Created: %d, Updated: %d, Skipped: %d',
            ucfirst($type),
            $summary['created'],
            $summary['updated'],
            $summary['skipped'],
        ));
    }
}


<?php

namespace App\Http\Controllers\DistrictAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('district.settings.index', [
            'config' => [
                'broadcast_driver' => config('broadcasting.default'),
                'queue_driver' => config('queue.default'),
            ],
        ]);
    }
}


<?php

namespace App\Http\Controllers\DistrictAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PowerSchoolController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('district.powerschool.index');
    }
}


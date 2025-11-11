<?php

namespace App\Http\Controllers;

use App\Models\RequestArea;
use App\Models\RequestType;
use Illuminate\Http\Request;

class RequestAreaController extends Controller
{
    public function index()
    {
        $areas = RequestArea::where('active', true)
            ->orderBy('sort_order')
            ->get();

        return view('tickets.select-area', compact('areas'));
    }

    public function show(string $area)
    {
        $requestArea = RequestArea::where('slug', $area)->where('active', true)->firstOrFail();
        $requestTypes = $requestArea->activeRequestTypes;

        return view('tickets.select-type', compact('requestArea', 'requestTypes'));
    }
}
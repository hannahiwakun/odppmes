<?php

namespace App\Http\Controllers;

use App\Models\Project;

class MapController extends Controller
{
    public function index()
    {
        $projects = Project::with(['mda', 'developmentPartner'])
            ->visibleTo(auth()->user())
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return view('maps.index', compact('projects'));
    }
}
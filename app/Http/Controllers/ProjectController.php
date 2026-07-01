<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Mda;
use App\Models\DevelopmentPartner;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $projects = Project::with(['mda', 'developmentPartner'])
                ->latest()
                ->paginate(10);

    return view('projects.index', compact('projects'));
}

    /**
     * Show the form for creating a new resource.
     */
  public function create()
{
    $mdas = \App\Models\Mda::where('status', 1)
                ->orderBy('name')
                ->get();

    $developmentPartners = \App\Models\DevelopmentPartner::where('status', 1)
                            ->orderBy('partner_name')
                            ->get();

    return view('projects.create', compact(
        'mdas',
        'developmentPartners'
    ));
}
    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
{
    $request->validate([
        'project_title' => 'required|string|max:255',
        'mda_id' => 'required|exists:mdas,id',
        'development_partner_id' => 'nullable|exists:development_partners,id',
        'sector' => 'required|string|max:255',
        'lga' => 'required|string|max:255',
    ]);

    Project::create([
        'project_title' => $request->project_title,
        'project_code' => $request->project_code,
        'mda_id' => $request->mda_id,
        'development_partner_id' => $request->development_partner_id,
        'sector' => $request->sector,
        'lga' => $request->lga,
        'ward' => $request->ward,
        'budget' => $request->budget ?? 0,
        'funding_source' => $request->funding_source,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'status' => $request->status ?? 'Planning',
        'progress' => $request->progress ?? 0,
        'latitude' => $request->latitude,
        'longitude' => $request->longitude,
        'description' => $request->description,
    ]);

    return redirect()
        ->route('projects.index')
        ->with('success', 'Project created successfully.');
}

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
{
    $mdas = \App\Models\Mda::orderBy('name')->get();

    $developmentPartners = \App\Models\DevelopmentPartner::orderBy('partner_name')->get();

    return view('projects.edit', compact(
        'project',
        'mdas',
        'developmentPartners'
    ));
}
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
{
    $request->validate([
        'project_title' => 'required|string|max:255',
        'mda_id' => 'required|exists:mdas,id',
        'development_partner_id' => 'nullable|exists:development_partners,id',
        'sector' => 'required|string|max:255',
        'lga' => 'required|string|max:255',
    ]);

    $project->update([
        'project_title' => $request->project_title,
        'project_code' => $request->project_code,
        'mda_id' => $request->mda_id,
        'development_partner_id' => $request->development_partner_id,
        'sector' => $request->sector,
        'lga' => $request->lga,
        'status' => $request->status,
        'progress' => $request->progress,
    ]);

    return redirect()
        ->route('projects.index')
        ->with('success', 'Project updated successfully.');
}

    /**
     * Remove the specified resource from storage.
     */
   public function destroy(Project $project)
{
    $project->delete();

    return redirect()
        ->route('projects.index')
        ->with('success', 'Project deleted successfully.');
}
}

<?php

namespace App\Http\Controllers;

use App\Models\Monitoring;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MonitoringController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $monitorings = Monitoring::with(['project', 'user'])
            ->latest()
            ->get();

        return view('monitorings.index', compact('monitorings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $projects = Project::orderBy('project_title')->get();

        return view('monitorings.create', compact('projects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'monitoring_date'  => 'required|date',
            'status'           => 'required',
            'photo'            => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('monitorings', 'public');
        }

        $monitoring = Monitoring::create([
            'project_id'        => $request->project_id,
            'user_id'           => auth()->id(),
            'monitoring_date'   => $request->monitoring_date,
            'progress_before'   => $request->progress_before,
            'progress_after'    => $request->progress_after,
            'status'            => $request->status,
            'findings'          => $request->findings,
            'challenges'        => $request->challenges,
            'recommendations'   => $request->recommendations,
            'photo'             => $photoPath,
        ]);

        // Automatically update the related project
        $monitoring->project->update([
            'progress' => $request->progress_after,
            'status'   => $request->status,
        ]);

        return redirect()
            ->route('monitorings.index')
            ->with('success', 'Monitoring record created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Monitoring $monitoring)
    {
        return view('monitorings.show', compact('monitoring'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Monitoring $monitoring)
    {
        $projects = Project::orderBy('project_title')->get();

        return view('monitorings.edit', compact('monitoring', 'projects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Monitoring $monitoring)
    {
        $request->validate([
            'project_id'      => 'required|exists:projects,id',
            'monitoring_date' => 'required|date',
            'status'          => 'required',
            'photo'           => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        $photoPath = $monitoring->photo;

        if ($request->hasFile('photo')) {

            if ($monitoring->photo && Storage::disk('public')->exists($monitoring->photo)) {
                Storage::disk('public')->delete($monitoring->photo);
            }

            $photoPath = $request->file('photo')->store('monitorings', 'public');
        }

        $monitoring->update([
            'project_id'        => $request->project_id,
            'monitoring_date'   => $request->monitoring_date,
            'progress_before'   => $request->progress_before,
            'progress_after'    => $request->progress_after,
            'status'            => $request->status,
            'findings'          => $request->findings,
            'challenges'        => $request->challenges,
            'recommendations'   => $request->recommendations,
            'photo'             => $photoPath,
        ]);

        // Automatically update the related project
        $monitoring->project->update([
            'progress' => $request->progress_after,
            'status'   => $request->status,
        ]);

        return redirect()
            ->route('monitorings.index')
            ->with('success', 'Monitoring updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Monitoring $monitoring)
    {
        // Delete photo if it exists
        if ($monitoring->photo && Storage::disk('public')->exists($monitoring->photo)) {
            Storage::disk('public')->delete($monitoring->photo);
        }

        $monitoring->delete();

        return redirect()
            ->route('monitorings.index')
            ->with('success', 'Monitoring deleted successfully.');
    }
}
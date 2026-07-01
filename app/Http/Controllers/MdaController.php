<?php

namespace App\Http\Controllers;

use App\Models\Mda;
use Illuminate\Http\Request;

class MdaController extends Controller
{
    public function index()
    {
        $mdas = Mda::latest()->paginate(10);

        return view('mdas.index', compact('mdas'));
    }

    public function create()
    {
        return view('mdas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:mdas,code',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        Mda::create($validated);

        return redirect()
            ->route('mdas.index')
            ->with('success', 'MDA created successfully.');
    }

    public function edit(Mda $mda)
    {
        return view('mdas.edit', compact('mda'));
    }

    public function update(Request $request, Mda $mda)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:mdas,code,' . $mda->id,
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        $mda->update($validated);

        return redirect()
            ->route('mdas.index')
            ->with('success', 'MDA updated successfully.');
    }

    public function destroy(Mda $mda)
    {
        $mda->delete();

        return redirect()
            ->route('mdas.index')
            ->with('success', 'MDA deleted successfully.');
    }
}
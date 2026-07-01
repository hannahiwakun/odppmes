<?php

namespace App\Http\Controllers;

use App\Models\DevelopmentPartner;
use Illuminate\Http\Request;

class DevelopmentPartnerController extends Controller
{
    public function index()
    {
        $partners = DevelopmentPartner::latest()->get();

        return view('development_partners.index', compact('partners'));
    }

    public function create()
    {
        return view('development_partners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'partner_name' => 'required',
            'partner_type' => 'required',
        ]);

        DevelopmentPartner::create($request->all());

        return redirect()
            ->route('development-partners.index')
            ->with('success', 'Development Partner created successfully.');
    }

    public function edit(DevelopmentPartner $development_partner)
    {
        return view('development_partners.edit', compact('development_partner'));
    }

    public function update(Request $request, DevelopmentPartner $development_partner)
    {
        $request->validate([
            'partner_name' => 'required',
            'partner_type' => 'required',
        ]);

        $development_partner->update($request->all());

        return redirect()
            ->route('development-partners.index')
            ->with('success', 'Development Partner updated successfully.');
    }

    public function destroy(DevelopmentPartner $development_partner)
    {
        $development_partner->delete();

        return redirect()
            ->route('development-partners.index')
            ->with('success', 'Development Partner deleted successfully.');
    }
}
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
        DevelopmentPartner::create($this->validatedPartner($request));

        return redirect()
            ->route('development-partners.index')
            ->with('success', 'Development Partner created successfully.');
    }

    public function show(DevelopmentPartner $development_partner)
    {
        $development_partner->loadCount('projects');

        return view('development_partners.show', compact('development_partner'));
    }

    public function edit(DevelopmentPartner $development_partner)
    {
        return view('development_partners.edit', compact('development_partner'));
    }

    public function update(Request $request, DevelopmentPartner $development_partner)
    {
        $development_partner->update($this->validatedPartner($request));

        return redirect()
            ->route('development-partners.index')
            ->with('success', 'Development Partner updated successfully.');
    }

    public function destroy(DevelopmentPartner $development_partner)
    {
        if ($development_partner->projects()->exists()) {
            return redirect()
                ->route('development-partners.index')
                ->with('error', 'This development partner is linked to projects and was not deleted. The project records were kept.');
        }

        $development_partner->delete();

        return redirect()
            ->route('development-partners.index')
            ->with('success', 'Development Partner deleted successfully.');
    }

    /**
     * Fields an administrator may set. Request keys outside this list are ignored.
     */
    private function validatedPartner(Request $request): array
    {
        return $request->validate([
            'partner_name' => ['required', 'string', 'max:255'],
            'acronym' => ['nullable', 'string', 'max:255'],
            'partner_type' => ['required', 'string', 'in:Government,NGO,UN Agency,Bilateral,Multilateral,Private Sector'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'boolean'],
        ]);
    }
}

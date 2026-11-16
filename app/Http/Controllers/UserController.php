<?php

namespace App\Http\Controllers;

use App\Models\DevelopmentPartner;
use App\Models\Mda;
use App\Models\User;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['mda', 'developmentPartner'])->latest()->paginate(10);

        return view('users.index', compact('users'));
    }


    public function create()
    {
        $roles = $this->roles();
        $mdas = Mda::orderBy('name')->get();
        $partners = DevelopmentPartner::orderBy('partner_name')->get();

        return view('users.create', compact('roles', 'mdas', 'partners'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => ['required', 'string', Rule::in($this->roles())],
            'mda_id' => ['nullable', 'exists:mdas,id', Rule::requiredIf($request->input('role') === 'MDA Officer')],
            'development_partner_id' => ['nullable', 'exists:development_partners,id', Rule::requiredIf($request->input('role') === 'Development Partner')],
            'password' => 'required|min:8|confirmed',
        ]);


        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'mda_id' => $request->input('role') === 'MDA Officer' ? $request->mda_id : null,
                'development_partner_id' => $request->input('role') === 'Development Partner' ? $request->development_partner_id : null,
                'password' => Hash::make($request->password),
            ]);

            AuditLogger::log(
                'Created',
                'User',
                'Created user: '.$user->name
            );
        });


        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }


    public function show(User $user)
    {
        $user->load(['mda', 'developmentPartner']);

        return view('users.show', compact('user'));
    }


    public function edit(User $user)
    {
        $roles = $this->roles();
        $mdas = Mda::orderBy('name')->get();
        $partners = DevelopmentPartner::orderBy('partner_name')->get();

        return view('users.edit', compact('user', 'roles', 'mdas', 'partners'));
    }


    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => ['required', 'string', Rule::in($this->roles())],
            'mda_id' => ['nullable', 'exists:mdas,id', Rule::requiredIf($request->input('role') === 'MDA Officer')],
            'development_partner_id' => ['nullable', 'exists:development_partners,id', Rule::requiredIf($request->input('role') === 'Development Partner')],
            'password' => 'nullable|min:8|confirmed',
        ]);


        if (
            $user->isSuperAdmin()
            && $request->role !== 'Super Administrator'
            && ! $user->anotherSuperAdministratorExists()
        ) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    'role' => 'This role was not changed because it would leave the portal without a Super Administrator.',
                ]);
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'mda_id' => $request->input('role') === 'MDA Officer' ? $request->mda_id : null,
            'development_partner_id' => $request->input('role') === 'Development Partner' ? $request->development_partner_id : null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        DB::transaction(function () use ($user, $data) {
            $user->update($data);

            AuditLogger::log(
                'Updated',
                'User',
                'Updated user: '.$user->name
            );
        });


        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }


    public function destroy(User $user)
    {

        if (auth()->id() === $user->id) {
            return redirect()
                ->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        if ($user->isSuperAdmin() && ! $user->anotherSuperAdministratorExists()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'This account was not deleted because it is the last Super Administrator.');
        }

        $blocked = $user->deletionBlockers();

        if ($blocked !== []) {
            return redirect()
                ->route('users.index')
                ->with('error', 'This user was not deleted because they still have '.implode(', ', $blocked).'.');
        }

        DB::transaction(function () use ($user) {
            AuditLogger::log(
                'Deleted',
                'User',
                'Deleted user: '.$user->name
            );

            $user->delete();
        });


        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * @return list<string>
     */
    private function roles(): array
    {
        return [
            'Super Administrator',
            'M&E Officer',
            'MDA Officer',
            'Development Partner',
            'Viewer',
            'DPMO Website Administrator',
        ];
    }
}
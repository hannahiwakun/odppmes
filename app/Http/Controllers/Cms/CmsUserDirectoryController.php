<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CmsUserDirectoryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage-cms-users');

        $users = User::query()
            ->whereIn('role', ['Super Administrator', 'DPMO Website Administrator'])
            ->orderBy('name')
            ->paginate(15);

        return view('cms.users.index', compact('users'));
    }
}

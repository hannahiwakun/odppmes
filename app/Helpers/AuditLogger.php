<?php

namespace App\Helpers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    public static function log(
        string $action,
        string $module,
        ?string $description = null
    )
    {
        AuditLog::create([

            'user_id' => Auth::id(),

            'action' => $action,

            'module' => $module,

            'description' => $description,

            'ip_address' => Request::ip(),

        ]);
    }
}
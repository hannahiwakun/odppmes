<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'module',
        'description',
        'ip_address',
    ];


    /**
     * Audit belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
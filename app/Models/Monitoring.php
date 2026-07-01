<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Monitoring extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'monitoring_date',
        'progress_before',
        'progress_after',
        'status',
        'findings',
        'challenges',
        'recommendations',
        'photo',
        'latitude',
        'longitude',
    ];

    /**
     * Monitoring belongs to a Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Monitoring belongs to a User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
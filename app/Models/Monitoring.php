<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Monitoring extends Model
{
    use HasFactory;

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

    protected $casts = [
        'monitoring_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(MonitoringEvidence::class)->latest();
    }
}
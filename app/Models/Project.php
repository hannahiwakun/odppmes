<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'mda_id',
        'development_partner_id',
        'project_title',
        'project_code',
        'sector',
        'lga',
        'ward',
        'budget',
        'funding_source',
        'start_date',
        'end_date',
        'status',
        'progress',
        'latitude',
        'longitude',
        'description',
    ];

    /**
     * A project belongs to an MDA
     */
    public function mda()
    {
        return $this->belongsTo(Mda::class);
    }

    /**
     * A project belongs to a Development Partner
     */
    public function developmentPartner()
    {
        return $this->belongsTo(DevelopmentPartner::class);
    }
    /**
 * A Project has many Monitoring Records
 */
public function monitorings()
{
    return $this->hasMany(Monitoring::class);
}
}
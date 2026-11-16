<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ProjectPhoto;
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
        'nature',
        'geographical_coverage',
        'initiative_status',
        'objectives',
        'key_activities',
        'state_priority',
        'funding_institution',
        'assistance_nature',
        'funding_arrangement',
        'agreement_date',
        'funding_currency',
        'approved_value',
        'partner_contribution',
        'federal_contribution',
        'state_contribution',
        'other_contributions',
        'counterpart_required',
        'counterpart_paid',
        'funds_drawn',
        'cumulative_expenditure',
        'exchange_rate',
        'financial_as_of',
        'financial_source',
        'direct_beneficiaries',
        'indirect_beneficiaries',
        'beneficiary_categories',
        'beneficiaries_are_estimates',
        'expected_outcomes',
        'programme_officer',
        'programme_officer_designation',
        'programme_officer_phone',
        'programme_officer_email',
        'verification_status',
        'return_submitted_on',
    ];

    protected $casts = [
        'beneficiaries_are_estimates' => 'boolean',
        'agreement_date' => 'date',
        'financial_as_of' => 'date',
        'return_submitted_on' => 'date',
    ];

    /**
     * A Project belongs to an MDA
     */
    public function mda()
    {
        return $this->belongsTo(Mda::class);
    }

    public function scopeVisibleTo($query, ?User $user)
    {
        if ($user === null) {
            return $query;
        }

        if ($user->isMdaOfficer()) {
            if ($user->mda_id === null) {
                return $query->whereRaw('0 = 1');
            }

            return $query->where('mda_id', $user->mda_id);
        }

        if ($user->isDevelopmentPartner()) {
            if ($user->development_partner_id === null) {
                return $query->whereRaw('0 = 1');
            }

            return $query->where('development_partner_id', $user->development_partner_id);
        }

        if ($user->isSuperAdmin() || $user->isMEO() || $user->isViewer()) {
            return $query;
        }

        return $query->whereRaw('0 = 1');
    }

    /**
     * A Project belongs to a Development Partner
     */
    public function developmentPartner()
    {
        return $this->belongsTo(DevelopmentPartner::class);
    }

    /**
     * A Project has many Monitoring Records
     */
    public function monitorings(): HasMany
    {
        return $this->hasMany(Monitoring::class);
    }

    /**
     * A Project has many Documents
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }

/**
 * A project has many photos
 */
public function photos()
{
    return $this->hasMany(ProjectPhoto::class);
}

    /**
     * Other MDAs taking part, besides the lead MDA.
     */
    public function participatingMdas()
    {
        return $this->belongsToMany(Mda::class, 'project_mda')->withTimestamps();
    }

    public function outstandingCounterpart(): ?float
    {
        if ($this->counterpart_required === null || $this->counterpart_paid === null) {
            return null;
        }

        return (float) $this->counterpart_required - (float) $this->counterpart_paid;
    }
}

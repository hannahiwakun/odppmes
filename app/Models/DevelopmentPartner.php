<?php

namespace App\Models;

use App\Models\Project;
use Illuminate\Database\Eloquent\Model;

class DevelopmentPartner extends Model
{
    protected $fillable = [
        'partner_name',
        'acronym',
        'partner_type',
        'contact_person',
        'email',
        'phone',
        'website',
        'country',
        'address',
        'status',
    ];

    /**
     * A Development Partner has many Projects
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
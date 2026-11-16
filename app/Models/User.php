<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'mda_id',
    'development_partner_id',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
   public function isSuperAdmin()
{
    return $this->role === 'Super Administrator';
}

public function isMEO()
{
    return $this->role === 'M&E Officer';
}

public function isMdaOfficer()
{
    return $this->role === 'MDA Officer';
}

public function isDevelopmentPartner()
{
    return $this->role === 'Development Partner';
}

public function isViewer()
{
    return $this->role === 'Viewer';
}

public function isWebsiteAdmin()
{
    return $this->role === 'DPMO Website Administrator';
}

public function hasCmsAccess(): bool
{
    return $this->isSuperAdmin() || $this->isWebsiteAdmin();
}

public function canManageCmsUsers(): bool
{
    return $this->isSuperAdmin();
}

public function canReviewCmsContent(): bool
{
    return $this->isSuperAdmin();
}

    public function mda()
    {
        return $this->belongsTo(Mda::class);
    }

    public function developmentPartner()
    {
        return $this->belongsTo(DevelopmentPartner::class);
    }

    /**
     * Super Administrators, M&E Officers, and Viewers keep statewide project access.
     * An MDA Officer is limited to the lead MDA stored on the project.
     * A Development Partner is limited to projects for the assigned organisation.
     * Website Administrators and any other role do not inherit statewide records.
     */
    public function canAccessProject(?Project $project): bool
    {
        if ($this->isMdaOfficer()) {
            if ($project === null || $this->mda_id === null) {
                return false;
            }

            return (int) $project->mda_id === (int) $this->mda_id;
        }

        if ($this->isDevelopmentPartner()) {
            if ($project === null || $this->development_partner_id === null) {
                return false;
            }

            return (int) $project->development_partner_id === (int) $this->development_partner_id;
        }

        if ($this->isSuperAdmin() || $this->isMEO() || $this->isViewer()) {
            return true;
        }

        return false;
    }

    /**
     * True when another account still has the Super Administrator role.
     */
    public function anotherSuperAdministratorExists(): bool
    {
        return static::query()
            ->where('role', 'Super Administrator')
            ->whereKeyNot($this->id)
            ->exists();
    }

    /**
     * Records that must stay if this account is removed.
     *
     * @return list<string>
     */
    public function deletionBlockers(): array
    {
        $blocked = [];

        if (Monitoring::where('user_id', $this->id)->exists()) {
            $blocked[] = 'monitoring records';
        }

        if (ProjectDocument::where('uploaded_by', $this->id)->exists()) {
            $blocked[] = 'project documents';
        }

        if (ProjectPhoto::where('uploaded_by', $this->id)->exists()) {
            $blocked[] = 'project photos';
        }

        if (AuditLog::where('user_id', $this->id)->exists()) {
            $blocked[] = 'audit history';
        }

        return $blocked;
    }
}

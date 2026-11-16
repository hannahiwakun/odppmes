<?php
namespace App\Models;

use App\Models\Project;
use Illuminate\Database\Eloquent\Model;

class Mda extends Model
{
    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'contact_person',
        'address',
        'status',
        'focal_name',
        'focal_designation',
        'focal_department',
        'focal_phone',
        'focal_email',
        'focal_supervisor',
        'alternate_name',
        'alternate_phone',
        'alternate_email',
        ];
        /**
 * An MDA has many Projects
 */
public function projects()
{
    return $this->hasMany(Project::class);
}

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function officers()
    {
        return $this->hasMany(User::class)->where('role', 'MDA Officer');
    }

    public function participatingProjects()
    {
        return $this->belongsToMany(Project::class, 'project_mda')->withTimestamps();
    }
}
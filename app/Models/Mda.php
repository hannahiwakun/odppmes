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
        ];
        /**
 * An MDA has many Projects
 */
public function projects()
{
    return $this->hasMany(Project::class);
}
}
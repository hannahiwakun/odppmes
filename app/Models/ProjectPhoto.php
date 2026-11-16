<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPhoto extends Model
{
    protected $fillable = [
        'project_id',
        'photo',
        'caption',
        'uploaded_by',
    ];


    /**
     * Photo belongs to a project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }


    /**
     * Photo uploaded by a user
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
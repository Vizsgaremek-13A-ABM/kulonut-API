<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Polygon extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function coords()
    {
        return $this->hasMany(Coord::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'polygons_projects');
    }
}

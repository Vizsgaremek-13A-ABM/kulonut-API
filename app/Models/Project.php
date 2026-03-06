<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_name',
        'work_number',
        'plan_issue_date',
        'client',
        'designer_id',
        'general_designer_id',
        'geodesy',
        'road_construction_plan',
        'water_network_plan',
        'sewage_plan',
        'stormwater_drainage_plan',
        'public_lighting_plan',
        'other_work_parts',
        'eutility_statement_issue_date',
        'road_construction_permit_date',
        'water_rights_permit_date',
        'notes',
        'folder_number',
        'min_role_level'
    ];

    protected $casts = [
        'plan_issue_date' => 'date',
        'eutility_statement_issue_date' => 'date',
        'road_construction_permit_date' => 'date',
        'water_rights_permit_date' => 'date',
    ];

    public function designer()
    {
        return $this->belongsTo(Designer::class);
    }

    public function generalDesigner()
    {
        return $this->belongsTo(GeneralDesigner::class);
    }

    public function polygons()
    {
        return $this->belongsToMany(Polygon::class, 'polygons_projects');
    }
}

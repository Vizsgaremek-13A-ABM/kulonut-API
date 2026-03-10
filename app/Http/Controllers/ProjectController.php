<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::with(['designer', 'generalDesigner', 'polygons'])->get();

        return ProjectResource::collection($projects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:50',
            'work_number' => 'nullable|string|max:50',
            'folder_number' => 'nullable|string|max:50',
            'client' => 'nullable|string|max:100',

            'designer_id' => 'required|exists:designers,id',
            'general_designer_id' => 'nullable|exists:general_designers,id',

            'plan_issue_date' => 'nullable|date',
            'eutility_statement_issue_date' => 'nullable|date',
            'road_construction_permit_date' => 'nullable|date',
            'water_rights_permit_date' => 'nullable|date',

            'geodesy' => 'nullable|string',
            'road_construction_plan' => 'nullable|boolean',
            'water_network_plan' => 'nullable|boolean',
            'sewage_plan' => 'nullable|boolean',
            'stormwater_drainage_plan' => 'nullable|boolean',
            'public_lighting_plan' => 'nullable|boolean',
            'other_work_parts' => 'nullable|string',

            'notes' => 'nullable|string',
            'min_role_level' => 'required|integer',
        ]);

        $project = Project::create($validated);

        $project->load(['designer', 'generalDesigner', 'polygons']);

        return (new ProjectResource($project))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $project->load(['designer', 'generalDesigner', 'polygons']);

        return new ProjectResource($project);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'project_name' => 'sometimes|required|string|max:50',
            'work_number' => 'sometimes|nullable|string|max:50',
            'folder_number' => 'sometimes|nullable|string|max:50',
            'client' => 'sometimes|nullable|string|max:100',

            'designer_id' => 'sometimes|required|exists:designers,id',
            'general_designer_id' => 'sometimes|nullable|exists:general_designers,id',

            'plan_issue_date' => 'sometimes|nullable|date',
            'eutility_statement_issue_date' => 'sometimes|nullable|date',
            'road_construction_permit_date' => 'sometimes|nullable|date',
            'water_rights_permit_date' => 'sometimes|nullable|date',

            'geodesy' => 'sometimes|nullable|string',
            'road_construction_plan' => 'sometimes|nullable|boolean',
            'water_network_plan' => 'sometimes|nullable|boolean',
            'sewage_plan' => 'sometimes|nullable|boolean',
            'stormwater_drainage_plan' => 'sometimes|nullable|boolean',
            'public_lighting_plan' => 'sometimes|nullable|boolean',
            'other_work_parts' => 'sometimes|nullable|string',

            'notes' => 'sometimes|nullable|string',
            'min_role_level' => 'sometimes|required|integer',
        ]);

        $project->update($validated);

        $project->load(['designer', 'generalDesigner', 'polygons']);

        return new ProjectResource($project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();
        return response()->noContent();
    }
}

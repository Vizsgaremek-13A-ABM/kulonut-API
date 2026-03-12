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
        $projects = Project::with(['client', 'geodesy', 'designer', 'generalDesigner', 'polygons'])->get();

        return ProjectResource::collection($projects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'work_number' => 'nullable|string|max:100',
            'plan_issue_date' => 'nullable|date',

            'client_id' => 'required|integer|exists:clients,id',
            'general_designer_id' => 'required|integer|exists:general_designers,id',

            'designer_id' => 'nullable|integer|exists:designers,id',
            'geodesy_id' => 'nullable|integer|exists:geodesies,id',

            'road_construction_plan' => 'nullable|boolean',
            'water_network_plan' => 'nullable|boolean',
            'sewage_plan' => 'nullable|boolean',
            'stormwater_drainage_plan' => 'nullable|boolean',

            'min_role_level' => 'required|integer|between:-128,127',
        ]);

        $project = Project::create($validated);

        $project->load(['client', 'geodesy', 'designer', 'generalDesigner', 'polygons']);

        return (new ProjectResource($project))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $project->load(['client', 'geodesy', 'designer', 'generalDesigner', 'polygons']);
        return new ProjectResource($project);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'project_name' => 'sometimes|required|string|max:255',
            'work_number' => 'sometimes|nullable|string|max:100',
            'plan_issue_date' => 'sometimes|nullable|date',

            'client_id' => 'sometimes|required|integer|exists:clients,id',
            'general_designer_id' => 'sometimes|required|integer|exists:general_designers,id',

            'designer_id' => 'sometimes|nullable|integer|exists:designers,id',
            'geodesy_id' => 'sometimes|nullable|integer|exists:geodesies,id',

            'road_construction_plan' => 'sometimes|nullable|boolean',
            'water_network_plan' => 'sometimes|nullable|boolean',
            'sewage_plan' => 'sometimes|nullable|boolean',
            'stormwater_drainage_plan' => 'sometimes|nullable|boolean',

            'min_role_level' => 'sometimes|required|integer|between:-128,127',
        ]);

        $project->update($validated);

        $project->load(['client', 'geodesy', 'designer', 'generalDesigner', 'polygons']);
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

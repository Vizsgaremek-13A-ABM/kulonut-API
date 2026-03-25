<?php

namespace App\Http\Controllers;

use App\Http\Resources\PolygonResource;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Client;
use App\Models\GeneralDesigner;
use App\Models\Designer;
use App\Models\Geodesy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['client', 'geodesy', 'designer', 'generalDesigner'])->get();
        return ProjectResource::collection($projects);
    }

    public function mapView()
    {
        $projects = Project::with('polygons:id')->get();

        return $projects->map(fn($project) => [
            'project_id' => $project->id,
            'project_name' => $project->project_name,
            'plan_issue_date' => $project->plan_issue_date?->format('Y-m-d'),
            'polygon_ids' => $project->polygons->pluck('id')->values(),
        ]);
    }

    public function polygons(Project $project)
    {
        $polygons = $project->polygons()->with('coords')->get();
        return PolygonResource::collection($polygons);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'work_number' => 'nullable|string|max:100',
            'plan_issue_date' => 'nullable|date',

            'client_id' => 'required',
            'general_designer_id' => 'required',
            'designer_id' => 'nullable',
            'geodesy_id' => 'nullable',

            'road_construction_plan' => 'nullable|boolean',
            'water_network_plan' => 'nullable|boolean',
            'sewage_plan' => 'nullable|boolean',
            'stormwater_drainage_plan' => 'nullable|boolean',
            'public_lighting_plan' => 'nullable|boolean',

            'utility_statement_issue_date' => 'nullable|date',
            'road_construction_permit_date' => 'nullable|date',
            'water_rights_permit_date' => 'nullable|date',

            'notes' => 'nullable|string',
            'other_work_parts' => 'nullable|string',
            'folder_number' => 'nullable|string|max:100',

            'min_role_level' => 'required|integer|between:-128,127',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $validated['client_id'] = $this->resolveEntityId(Client::class, $request->client_id);
            $validated['general_designer_id'] = $this->resolveEntityId(GeneralDesigner::class, $request->general_designer_id);
            $validated['designer_id'] = $this->resolveEntityId(Designer::class, $request->designer_id);
            $validated['geodesy_id'] = $this->resolveEntityId(Geodesy::class, $request->geodesy_id);

            $project = Project::create($validated);
            $project->load(['client', 'geodesy', 'designer', 'generalDesigner', 'polygons']);

            return (new ProjectResource($project))->response()->setStatusCode(201);
        });
    }

    public function show(Project $project)
    {
        return new ProjectResource($project->load(['client', 'geodesy', 'designer', 'generalDesigner']));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'project_name' => 'sometimes|required|string|max:255',
            'work_number' => 'sometimes|nullable|string|max:100',
            'plan_issue_date' => 'sometimes|nullable|date',

            'client_id' => 'sometimes|required',
            'general_designer_id' => 'sometimes|required',
            'designer_id' => 'sometimes|nullable',
            'geodesy_id' => 'sometimes|nullable',

            'road_construction_plan' => 'sometimes|nullable|boolean',
            'water_network_plan' => 'sometimes|nullable|boolean',
            'sewage_plan' => 'sometimes|nullable|boolean',
            'stormwater_drainage_plan' => 'sometimes|nullable|boolean',
            'public_lighting_plan' => 'sometimes|nullable|boolean',

            'utility_statement_issue_date' => 'sometimes|nullable|date',
            'road_construction_permit_date' => 'sometimes|nullable|date',
            'water_rights_permit_date' => 'sometimes|nullable|date',
            'notes' => 'sometimes|nullable|string',
            'other_work_parts' => 'sometimes|nullable|string',
            'folder_number' => 'sometimes|nullable|string|max:100',

            'min_role_level' => 'sometimes|required|integer|between:-128,127',
        ]);

        return DB::transaction(function () use ($validated, $request, $project) {
            if ($request->has('client_id')) {
                $validated['client_id'] = $this->resolveEntityId(Client::class, $request->client_id);
            }
            if ($request->has('general_designer_id')) {
                $validated['general_designer_id'] = $this->resolveEntityId(GeneralDesigner::class, $request->general_designer_id);
            }
            if ($request->has('designer_id')) {
                $validated['designer_id'] = $this->resolveEntityId(Designer::class, $request->designer_id);
            }
            if ($request->has('geodesy_id')) {
                $validated['geodesy_id'] = $this->resolveEntityId(Geodesy::class, $request->geodesy_id);
            }

            $project->update($validated);
            $project->load(['client', 'geodesy', 'designer', 'generalDesigner']);

            return new ProjectResource($project);
        });
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return response()->noContent();
    }

    private function resolveEntityId($modelClass, $value)
    {
        if (empty($value)) return null;

        if (is_numeric($value)) {
            return $value;
        }

        $record = $modelClass::firstOrCreate(['name' => $value]);
        return $record->id;
    }
}

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
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    /**
    * Display a listing of the resource.
    */
    public function index()
    {
        $projects = Project::with(['client', 'geodesy', 'designer', 'generalDesigner'])->get();
        return ProjectResource::collection($projects);
    }

    /**
     * Gets a simplified list of projects for map view.
     */
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

    /**
    * Gets all the polygons related to a project.
    */
    public function polygons(Project $project)
    {
        $polygons = $project->polygons()->with('coords')->get();
        return PolygonResource::collection($polygons);
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

            'client_id' => 'required|max:255',
            'general_designer_id' => 'required|max:255',
            'designer_id' => 'nullable|max:255',
            'geodesy_id' => 'nullable|max:255',

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
            $validated['client_id'] = $this->resolveEntityId(Client::class, $request->client_id, 'client_id');
            $validated['general_designer_id'] = $this->resolveEntityId(GeneralDesigner::class, $request->general_designer_id, 'general_designer_id');
            $validated['designer_id'] = $this->resolveEntityId(Designer::class, $request->designer_id, 'designer_id');
            $validated['geodesy_id'] = $this->resolveEntityId(Geodesy::class, $request->geodesy_id, 'geodesy_id');

            $project = Project::create($validated);
            return response()->json([
                'id' => $project->id,
            ], 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return new ProjectResource($project->load(['client', 'geodesy', 'designer', 'generalDesigner']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'project_name' => 'sometimes|string|max:255',
            'work_number' => 'sometimes|nullable|string|max:100',
            'plan_issue_date' => 'sometimes|nullable|date',

            'client_id' => 'sometimes|max:255',
            'general_designer_id' => 'sometimes|max:255',
            'designer_id' => 'sometimes|nullable|max:255',
            'geodesy_id' => 'sometimes|nullable|max:255',

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

            'min_role_level' => 'sometimes|integer|between:-128,127',
        ]);

        return DB::transaction(function () use ($validated, $request, $project) {
            if (array_key_exists('client_id', $validated)) {
                $validated['client_id'] = $this->resolveEntityId(Client::class, $request->client_id, 'client_id');
            }
            if (array_key_exists('general_designer_id', $validated)) {
                $validated['general_designer_id'] = $this->resolveEntityId(GeneralDesigner::class, $request->general_designer_id, 'general_designer_id');
            }
            if (array_key_exists('designer_id', $validated)) {
                $validated['designer_id'] = $this->resolveEntityId(Designer::class, $request->designer_id, 'designer_id');
            }
            if (array_key_exists('geodesy_id', $validated)) {
                $validated['geodesy_id'] = $this->resolveEntityId(Geodesy::class, $request->geodesy_id, 'geodesy_id');
            }

            $project->update($validated);
            return new ProjectResource($project->load(['client', 'geodesy', 'designer', 'generalDesigner']));
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();
        return response()->noContent();
    }

    private function resolveEntityId($modelClass, $value, $fieldName)
    {
        if (empty($value)) return null;

        if (is_numeric($value)) {
            if (!$modelClass::where('id', $value)->exists()) {
                throw ValidationException::withMessages([
                    $fieldName => ["The selected {$fieldName} is invalid."]
                ]);
            }
            return $value;
        }


        $record = $modelClass::firstOrCreate(['name' => $value]);
        return $record->id;
    }
}

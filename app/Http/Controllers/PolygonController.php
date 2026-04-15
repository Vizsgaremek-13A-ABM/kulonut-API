<?php

namespace App\Http\Controllers;

use App\Http\Resources\PolygonResource;
use App\Models\Project;
use App\Models\Polygon;
use App\Support\Rbac;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PolygonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Polygon::class);

        $roleLevel = $this->currentRoleLevel(request());

        $polygons = Polygon::with(['coords', 'projects'])
            ->where(function ($query) use ($roleLevel) {
                $query->whereHas('projects', function ($projectQuery) use ($roleLevel) {
                    $projectQuery->where('min_role_level', '<=', $roleLevel);
                })->orWhereDoesntHave('projects');

            })->get();

        return PolygonResource::collection($polygons);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Polygon::class);

        $validated = $request->validate([
            'project_id'   => 'required|exists:projects,id',
            'polygon_name' => 'required|string|max:50',
            'coordinates'  => 'required|array',
            'coordinates.*.latitude'  => 'required|numeric|between:-90,90',
            'coordinates.*.longitude' => 'required|numeric|between:-180,180',
        ]);

        return DB::transaction(function () use ($validated) {
            $project = Project::query()->findOrFail($validated['project_id']);
            $this->authorize('view', $project);

            $polygon = Polygon::create([
                'name' => $validated['polygon_name']
            ]);

            $polygon->projects()->attach($validated['project_id']);

            $polygon->coords()->createMany($validated['coordinates']);

            return response()->json([
                'id' => $polygon->id,
            ], 201);
        });
    }

    /**
     * Bulk store newly created polygons.
     */
    public function bulkStore(Request $request)
    {
        $this->authorize('create', Polygon::class);

        $validated = $request->validate([
            'polygons' => 'required|array|min:1',
            'polygons.*.project_id' => 'required|exists:projects,id',
            'polygons.*.polygon_name' => 'required|string|max:50',
            'polygons.*.coordinates' => 'required|array|min:1',
            'polygons.*.coordinates.*.latitude' => 'required|numeric|between:-90,90',
            'polygons.*.coordinates.*.longitude' => 'required|numeric|between:-180,180',
        ]);

        return DB::transaction(function () use ($validated) {
            $createdPolygons = collect($validated['polygons'])->map(function ($polygonData) {
                $project = Project::query()->findOrFail($polygonData['project_id']);
                $this->authorize('view', $project);

                $polygon = Polygon::create([
                    'name' => $polygonData['polygon_name'],
                ]);

                $polygon->projects()->attach($polygonData['project_id']);
                $polygon->coords()->createMany($polygonData['coordinates']);

                return $polygon->load(['coords', 'projects']);
            });

            return PolygonResource::collection($createdPolygons)
                ->response()
                ->setStatusCode(201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Polygon $polygon)
    {
        $this->authorize('view', $polygon);

        $polygon->load(['coords', 'projects']);
        return new PolygonResource($polygon);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Polygon $polygon)
    {
        $this->authorize('update', $polygon);

        $validated = $request->validate([
            'project_id'   => 'sometimes|exists:projects,id',
            'polygon_name' => 'sometimes|string|max:50',
            'coordinates'  => 'sometimes|array',
            'coordinates.*.latitude'  => 'sometimes|numeric|between:-90,90',
            'coordinates.*.longitude' => 'sometimes|numeric|between:-180,180',
        ]);

        return DB::transaction(function () use ($validated, $polygon) {
            if (isset($validated['polygon_name'])) {
                $polygon->update(['name' => $validated['polygon_name']]);
            }

            if (isset($validated['project_id'])) {
                $project = Project::query()->findOrFail($validated['project_id']);
                $this->authorize('view', $project);

                $polygon->projects()->sync([$validated['project_id']]);
            }

            if (isset($validated['coordinates'])) {
                $polygon->coords()->delete();
                $polygon->coords()->createMany($validated['coordinates']);
            }

            return new PolygonResource($polygon->load(['coords', 'projects']));
        });
    }

    /**
     * Bulk update polygons.
     */
    public function bulkUpdate(Request $request)
    {
        $this->authorize('updateAny', Polygon::class);

        $validated = $request->validate([
            'polygons' => 'required|array|min:1',
            'polygons.*.polygon_id' => 'required|exists:polygons,id',
            'polygons.*.project_id' => 'sometimes|exists:projects,id',
            'polygons.*.polygon_name' => 'sometimes|string|max:50',
            'polygons.*.coordinates' => 'sometimes|array|min:1',
            'polygons.*.coordinates.*.latitude' => 'sometimes|numeric|between:-90,90',
            'polygons.*.coordinates.*.longitude' => 'sometimes|numeric|between:-180,180',
        ]);

        return DB::transaction(function () use ($validated) {
            $polygonIds = collect($validated['polygons'])->pluck('polygon_id')->all();
            $polygonsById = Polygon::whereIn('id', $polygonIds)->get()->keyBy('id');

            $updatedPolygons = collect($validated['polygons'])->map(function ($polygonData) use ($polygonsById) {
                $polygon = $polygonsById->get($polygonData['polygon_id']);

                if (!$polygon) {
                    abort(404);
                }
                if (array_key_exists('polygon_name', $polygonData)) {
                    $polygon->update(['name' => $polygonData['polygon_name']]);
                }

                if (array_key_exists('project_id', $polygonData)) {
                    $project = Project::query()->findOrFail($polygonData['project_id']);
                    $this->authorize('view', $project);

                    $polygon->projects()->syncWithoutDetaching([$polygonData['project_id']]);
                }

                if (array_key_exists('coordinates', $polygonData)) {
                    $polygon->coords()->delete();
                    $polygon->coords()->createMany($polygonData['coordinates']);
                }

                return $polygon->load(['coords', 'projects']);
            });

            return PolygonResource::collection($updatedPolygons);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Polygon $polygon)
    {
        $this->authorize('delete', $polygon);

        $polygon->delete();
        return response()->noContent();
    }

    /**
     * Bulk delete polygons.
     */
    public function bulkDestroy(Request $request)
    {
        $this->authorize('deleteAny', Polygon::class);

        $validated = $request->validate([
            'polygon_ids' => 'required|array|min:1',
            'polygon_ids.*' => 'required|exists:polygons,id',
        ]);

        return DB::transaction(function () use ($validated) {
            $deletedCount = Polygon::whereIn('id', $validated['polygon_ids'])->delete();

            return response()->json([
                'deleted_count' => $deletedCount,
            ]);
        });
    }

    /**
     * Unlink a polygon from a specific project.
     */
    public function unlink(Polygon $polygon, Project $project)
    {
        $this->authorize('update', $polygon);
        $this->authorize('view', $project);

        $polygon->projects()->detach($project->id);

        return new PolygonResource($polygon->load(['coords', 'projects']));
    }

    /**
     * Bulk unlink polygons from projects.
     */
    public function bulkUnlink(Request $request)
    {
        $this->authorize('updateAny', Polygon::class);

        $request->merge([
            'links' => $this->normalizeBulkUnlinkLinks($request),
        ]);

        $validated = $request->validate([
            'links' => 'required|array|min:1',
            'links.*.polygon_id' => 'required|exists:polygons,id',
            'links.*.project_id' => 'required|exists:projects,id',
        ]);

        return DB::transaction(function () use ($validated) {
            $polygonIds = collect($validated['links'])->pluck('polygon_id')->all();
            $projectIds = collect($validated['links'])->pluck('project_id')->all();

            $polygonsById = Polygon::whereIn('id', $polygonIds)->get()->keyBy('id');
            $projectsById = Project::whereIn('id', $projectIds)->get()->keyBy('id');

            $updatedPolygons = collect($validated['links'])->map(function ($linkData) use ($polygonsById, $projectsById) {
                $polygon = $polygonsById->get($linkData['polygon_id']);
                $project = $projectsById->get($linkData['project_id']);

                if (!$polygon || !$project) {
                    abort(404);
                }

                $this->authorize('update', $polygon);
                $this->authorize('view', $project);

                $polygon->projects()->detach($project->id);

                return $polygon->load(['coords', 'projects']);
            });

            return PolygonResource::collection($updatedPolygons);
        });
    }

    /**
     * Accepts multiple payload shapes and normalizes to links[] for bulk unlink.
     */
    private function normalizeBulkUnlinkLinks(Request $request): array
    {
        $links = $request->input('links');

        // Scramble/form clients sometimes send links as a JSON string.
        if (is_string($links)) {
            $decoded = json_decode($links, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $links = $decoded;
            }
        }

        // If the raw body is an array, treat it as links directly.
        if ($links === null) {
            $jsonPayload = $request->json()->all();
            if (is_array($jsonPayload) && array_is_list($jsonPayload)) {
                $links = $jsonPayload;
            }
        }

        return is_array($links) ? $links : [];
    }

    private function currentRoleLevel(Request $request): int
    {
        return Rbac::levelOf($request->user());
    }
}

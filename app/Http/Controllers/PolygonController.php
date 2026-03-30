<?php

namespace App\Http\Controllers;

use App\Http\Resources\PolygonResource;
use App\Models\Polygon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PolygonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $polygons = Polygon::with(['coords', 'projects'])->get();
        return PolygonResource::collection($polygons);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'   => 'required|exists:projects,id',
            'polygon_name' => 'required|string|max:50',
            'coordinates'  => 'required|array',
            'coordinates.*.latitude'  => 'required|numeric|between:-90,90',
            'coordinates.*.longitude' => 'required|numeric|between:-180,180',
        ]);

        return DB::transaction(function () use ($validated) {
            $polygon = Polygon::create([
                'name' => $validated['polygon_name']
            ]);

            $polygon->projects()->attach($validated['project_id']);

            $polygon->coords()->createMany($validated['coordinates']);

            return (new PolygonResource($polygon->load(['coords', 'projects'])))->response()->setStatusCode(201);
        });
    }

    /**
     * Bulk store newly created polygons.
     */
    public function bulkStore(Request $request)
    {
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
        $polygon->load(['coords', 'projects']);
        return new PolygonResource($polygon);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Polygon $polygon)
    {
        $validated = $request->validate([
            'project_id'   => 'sometimes|required|exists:projects,id',
            'polygon_name' => 'sometimes|required|string|max:50',
            'coordinates'  => 'sometimes|required|array',
            'coordinates.*.latitude'  => 'sometimes|required|numeric|between:-90,90',
            'coordinates.*.longitude' => 'sometimes|required|numeric|between:-180,180',
        ]);

        return DB::transaction(function () use ($validated, $polygon) {
            if (isset($validated['polygon_name'])) {
                $polygon->update(['name' => $validated['polygon_name']]);
            }

            if (isset($validated['project_id'])) {
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
        $validated = $request->validate([
            'polygons' => 'required|array|min:1',
            'polygons.*.polygon_id' => 'required|exists:polygons,id',
            'polygons.*.project_id' => 'sometimes|required|exists:projects,id',
            'polygons.*.polygon_name' => 'sometimes|required|string|max:50',
            'polygons.*.coordinates' => 'sometimes|required|array|min:1',
            'polygons.*.coordinates.*.latitude' => 'sometimes|required|numeric|between:-90,90',
            'polygons.*.coordinates.*.longitude' => 'sometimes|required|numeric|between:-180,180',
        ]);

        return DB::transaction(function () use ($validated) {
            $polygonIds = collect($validated['polygons'])->pluck('polygon_id')->all();
            $polygonsById = Polygon::whereIn('id', $polygonIds)->get()->keyBy('id');

            $updatedPolygons = collect($validated['polygons'])->map(function ($polygonData) use ($polygonsById) {
                $polygon = $polygonsById->get($polygonData['polygon_id']);

                // Preserve fail-fast behaviour similar to findOrFail in case of race conditions.
                if (!$polygon) {
                    abort(404);
                }
                if (array_key_exists('polygon_name', $polygonData)) {
                    $polygon->update(['name' => $polygonData['polygon_name']]);
                }

                if (array_key_exists('project_id', $polygonData)) {
                    $polygon->projects()->sync([$polygonData['project_id']]);
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
        $polygon->delete();
        return response()->noContent();
    }

    /**
     * Bulk delete polygons.
     */
    public function bulkDestroy(Request $request)
    {
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
}

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
     * Display the specified resource.
     */
    public function show(Polygon $polygon)
    {
        $polygon = Polygon::with(['coords', 'projects'])->findOrFail($polygon->id);
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
     * Remove the specified resource from storage.
     */
    public function destroy(Polygon $polygon)
    {
        $polygon->delete();
        return response()->noContent();
    }
}

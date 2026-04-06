<?php

namespace App\Http\Controllers;

use App\Models\Coord;
use App\Http\Resources\CoordResource;
use App\Support\Rbac;
use Illuminate\Http\Request;

class CoordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Coord::class);

        $roleLevel = $this->currentRoleLevel($request);

        $coords = Coord::with('polygon.projects')
            ->whereHas('polygon.projects', function ($query) use ($roleLevel) {
                $query->where('min_role_level', '<=', $roleLevel);
            })->get();

        return CoordResource::collection($coords);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Coord::class);

        $validated = $request->validate([
            'polygon_id' => 'required|exists:polygons,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);
        $coord = Coord::create($validated);

        return response()->json([
            'id' => $coord->id,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Coord $coord)
    {
        $this->authorize('view', $coord);

        return new CoordResource($coord);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Coord $coord)
    {
        $this->authorize('update', $coord);

        $validated = $request->validate([
            'polygon_id' => 'sometimes|exists:polygons,id',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
        ]);

        $coord->update($validated);
        return new CoordResource($coord);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coord $coord)
    {
        $this->authorize('delete', $coord);

        $coord->delete();
        return response()->noContent();
    }

    private function currentRoleLevel(Request $request): int
    {
        return Rbac::levelOf($request->user());
    }
}

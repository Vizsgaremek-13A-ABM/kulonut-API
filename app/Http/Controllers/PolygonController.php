<?php

namespace App\Http\Controllers;

use App\Http\Resources\PolygonResource;
use App\Models\Polygon;
use Illuminate\Http\Request;

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
            'name' => 'required|string|max:50',
        ]);

        $polygon = Polygon::create($validated);

        return (new PolygonResource($polygon))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Polygon $polygon)
    {
        return new PolygonResource($polygon);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Polygon $polygon)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:50',
        ]);

        $polygon->update($validated);

        return new PolygonResource($polygon);
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

<?php

namespace App\Http\Controllers;

use App\Models\Coord;
use App\Http\Resources\CoordResource;
use Illuminate\Http\Request;

class CoordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CoordResource::collection(Coord::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
        return new CoordResource($coord);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Coord $coord)
    {
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
        $coord->delete();
        return response()->noContent();
    }
}

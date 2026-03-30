<?php

namespace App\Http\Controllers;

use App\Http\Resources\GeodesyResource;
use App\Models\Geodesy;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class GeodesyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return GeodesyResource::collection(Geodesy::all());
    }

    /**
     * Gets all the projects related to a geodesy.
     */
    public function getProjects(Geodesy $geodesy)
    {
        return response()->json(
            $geodesy->projects()->select('id', 'project_name', 'plan_issue_date')->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:100',
        ]);

        $geodesy = Geodesy::create($validated);

        return response()->json([
            'id' => $geodesy->id,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Geodesy $geodesy)
    {
        return new GeodesyResource($geodesy);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Geodesy $geodesy)
    {
        $validated = $request->validate([
            'name' => 'sometimes|nullable|string|max:100',
        ]);

        $geodesy->update($validated);

        return new GeodesyResource($geodesy);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Geodesy $geodesy)
    {
        try {
            $geodesy->delete();

            return response()->noContent();
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Geodesy cannot be deleted because one or more projects reference it.',
            ], 409);
        }
    }
}

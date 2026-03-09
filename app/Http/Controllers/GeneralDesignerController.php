<?php

namespace App\Http\Controllers;

use App\Http\Resources\GeneralDesignerResource;
use App\Models\GeneralDesigner;
use Illuminate\Http\Request;

class GeneralDesignerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return GeneralDesignerResource::collection(GeneralDesigner::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $generalDesigner = GeneralDesigner::create($validated);

        return (new GeneralDesignerResource($generalDesigner))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GeneralDesigner $generalDesigner)
    {
        return new GeneralDesignerResource($generalDesigner);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GeneralDesigner $generalDesigner)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:50',
        ]);

        $generalDesigner->update($validated);

        return new GeneralDesignerResource($generalDesigner);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GeneralDesigner $generalDesigner)
    {
        $generalDesigner->delete();
        return response()->noContent();
    }
}

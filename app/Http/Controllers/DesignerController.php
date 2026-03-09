<?php

namespace App\Http\Controllers;

use App\Models\Designer;
use App\Http\Resources\DesignerResource;
use Illuminate\Http\Request;

class DesignerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return DesignerResource::collection(Designer::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $designer = Designer::create($validated);

        return (new DesignerResource($designer))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Designer $designer)
    {
        return new DesignerResource($designer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Designer $designer)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:50',
        ]);

        $designer->update($validated);

        return new DesignerResource($designer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Designer $designer)
    {
        $designer->delete();
        return response()->noContent();
    }
}

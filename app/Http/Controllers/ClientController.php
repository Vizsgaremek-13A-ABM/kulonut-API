<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ClientResource::collection(Client::all());
    }

    /**
     * Gets all the projects related to a client.
     */
    public function getProjects(Client $client)
    {
        return response()->json(
            $client->projects()->select('id', 'project_name', 'plan_issue_date')->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $client = Client::create($validated);

        return (new ClientResource($client))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        return new ClientResource($client);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
        ]);

        $client->update($validated);

        return new ClientResource($client);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        try {
            $client->delete();

            return response()->noContent();
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Client cannot be deleted because one or more projects reference it.',
            ], 409);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return ClientResource::collection(Client::all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client): ClientResource
    {
        $client->load('transactions');

        return new ClientResource($client);
    }
}

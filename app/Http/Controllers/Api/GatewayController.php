<?php

namespace App\Http\Controllers\Api;

use App\Actions\Gateway\{CreateGatewayAction, UpdateGatewayAction};
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\{StoreGatewayRequest, UpdateGatewayRequest};
use App\Http\Resources\GatewayResource;
use App\Models\Gateway;
use App\Observers\GatewayObserver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\{Cache, Gate};
use Symfony\Component\HttpFoundation\Response;

class GatewayController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('manage-finances');

        $gateways = Cache::rememberForever(GatewayObserver::LIST_CACHE_KEY, fn () => Gateway::active()->get());

        return GatewayResource::collection($gateways);
    }
    public function store(StoreGatewayRequest $request, CreateGatewayAction $action): JsonResponse
    {
        Gate::authorize('manage-finances');
        $gateway = $action->handle($request->validated());

        return (new GatewayResource($gateway))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
    public function show(Gateway $gateway): GatewayResource
    {
        Gate::authorize('manage-finances');

        return new GatewayResource($gateway);
    }
    public function update(UpdateGatewayRequest $request, Gateway $gateway, UpdateGatewayAction $action): JsonResponse
    {
        Gate::authorize('manage-finances');
        $gateway = $action->handle($gateway, $request->validated());

        return (new GatewayResource($gateway))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
    public function destroy(Gateway $gateway): Response
    {
        Gate::authorize('manage-finances');
        $gateway->delete();

        return response()->noContent();
    }
}

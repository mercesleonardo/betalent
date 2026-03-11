<?php

namespace App\Http\Controllers\Api;

use App\Actions\Product\{CreateProductAction, UpdateProductAction};
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\{StoreProductRequest, UpdateProductRequest};
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Observers\ProductObserver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{Cache, Gate};
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $products = Cache::rememberForever(ProductObserver::LIST_CACHE_KEY, fn () => Product::all());

        $page      = (int) request('page', 1);
        $perPage   = 15;
        $items     = $products->forPage($page, $perPage)->values();
        $paginated = new LengthAwarePaginator(
            $items,
            $products->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return ProductResource::collection($paginated);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request, CreateProductAction $action): JsonResponse
    {
        Gate::authorize('manage-products');

        $product = $action->handle($request->validated());

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): ProductResource
    {
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product, UpdateProductAction $action): JsonResponse
    {
        Gate::authorize('manage-products');

        $product = $action->handle($product, $request->validated());

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): Response
    {
        Gate::authorize('manage-products');

        $product->delete();

        return response()->noContent();
    }
}

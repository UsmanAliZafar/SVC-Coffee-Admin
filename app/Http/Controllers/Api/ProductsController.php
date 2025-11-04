<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductsController extends Controller
{
    /**
     * Get all products with optional filters
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::query()
                ->active()
                ->available()
                ->with(['category', 'images']);

            // Filter by category
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Filter by featured
            if ($request->has('is_featured')) {
                $query->where('is_featured', filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN));
            }

            // Filter by show_on_home
            if ($request->has('show_on_home')) {
                $query->where('show_on_home', filter_var($request->show_on_home, FILTER_VALIDATE_BOOLEAN));
            }

            // Filter by price range
            if ($request->has('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }
            if ($request->has('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }

            // Filter by stock availability
            if ($request->has('in_stock')) {
                $query->inStock();
            }

            // Filter by on sale
            if ($request->has('on_sale')) {
                $query->onSale();
            }

            // Filter by product type
            if ($request->has('product_type')) {
                $query->where('product_type', $request->product_type);
            }

            // Search products
            if ($request->has('search')) {
                $query->search($request->search);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'sort_order');
            $sortOrder = $request->get('sort_order', 'asc');

            if ($sortBy === 'price') {
                $query->orderBy('price', $sortOrder);
            } elseif ($sortBy === 'name') {
                $query->orderBy('name', $sortOrder);
            } elseif ($sortBy === 'newest') {
                $query->orderBy('created_at', 'desc');
            } else {
                $query->ordered();
            }

            // Pagination
            $perPage = min($request->get('per_page', 20), 100);
            $products = $query->paginate($perPage);

            // Transform products
            $products->getCollection()->transform(function ($product) {
                return $this->transformProduct($product, true);
            });

            return response()->json([
                'success' => true,
                'message' => 'Products retrieved successfully',
                'data' => $products->items(),
                'pagination' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ],
                'timestamp' => now()->toIso8601String()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products',
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Get single product by ID with full details
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $product = Product::query()
                ->active()
                ->with(['category', 'images', 'vendor', 'tags', 'variants'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Product retrieved successfully',
                'data' => $this->transformProduct($product, true),
                'timestamp' => now()->toIso8601String()
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'timestamp' => now()->toIso8601String()
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product',
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Get product by slug with full details
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function getBySlug(string $slug): JsonResponse
    {
        try {
            $product = Product::query()
                ->active()
                ->where('slug', $slug)
                ->with(['category', 'images', 'vendor', 'tags', 'variants'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Product retrieved successfully',
                'data' => $this->transformProduct($product, true),
                'timestamp' => now()->toIso8601String()
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'timestamp' => now()->toIso8601String()
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product',
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Get product by SKU
     *
     * @param string $sku
     * @return JsonResponse
     */
    public function getBySku(string $sku): JsonResponse
    {
        try {
            $product = Product::query()
                ->active()
                ->where('sku', $sku)
                ->with(['category', 'images'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Product retrieved successfully',
                'data' => $this->transformProduct($product, true),
                'timestamp' => now()->toIso8601String()
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'timestamp' => now()->toIso8601String()
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product',
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Get featured products
     *
     * @return JsonResponse
     */
    public function featured(): JsonResponse
    {
        try {
            $products = Product::query()
                ->active()
                ->available()
                ->featured()
                ->inStock()
                ->with(['category', 'images'])
                ->ordered()
                ->limit(12)
                ->get();

            $data = $products->map(function ($product) {
                return $this->transformProduct($product, false);
            });

            return response()->json([
                'success' => true,
                'message' => 'Featured products retrieved successfully',
                'data' => $data,
                'timestamp' => now()->toIso8601String()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve featured products',
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Get products for homepage
     *
     * @return JsonResponse
     */
    public function homepage(): JsonResponse
    {
        try {
            $products = Product::query()
                ->active()
                ->available()
                ->showOnHome()
                ->inStock()
                ->with(['category', 'images'])
                ->ordered()
                ->limit(12)
                ->get();

            $data = $products->map(function ($product) {
                return $this->transformProduct($product, false);
            });

            return response()->json([
                'success' => true,
                'message' => 'Homepage products retrieved successfully',
                'data' => $data,
                'timestamp' => now()->toIso8601String()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve homepage products',
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Get products on sale
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function onSale(Request $request): JsonResponse
    {
        try {
            $query = Product::query()
                ->active()
                ->available()
                ->onSale()
                ->inStock()
                ->with(['category', 'images']);

            // Sorting
            $sortBy = $request->get('sort_by', 'discount_percentage');
            $sortOrder = $request->get('sort_order', 'desc');

            if ($sortBy === 'price') {
                $query->orderBy('price', $sortOrder);
            } elseif ($sortBy === 'discount_percentage') {
                $query->orderByRaw('((price - sale_price) / price * 100) DESC');
            } else {
                $query->ordered();
            }

            // Pagination
            $perPage = min($request->get('per_page', 20), 100);
            $products = $query->paginate($perPage);

            // Transform products
            $products->getCollection()->transform(function ($product) {
                return $this->transformProduct($product, false);
            });

            return response()->json([
                'success' => true,
                'message' => 'Sale products retrieved successfully',
                'data' => $products->items(),
                'pagination' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ],
                'timestamp' => now()->toIso8601String()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve sale products',
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Search products
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        try {
            $query = Product::query()
                ->active()
                ->available()
                ->search($request->q)
                ->with(['category', 'images']);

            // Pagination
            $perPage = min($request->get('per_page', 20), 100);
            $products = $query->paginate($perPage);

            // Transform products
            $products->getCollection()->transform(function ($product) {
                return $this->transformProduct($product, false);
            });

            return response()->json([
                'success' => true,
                'message' => 'Search results retrieved successfully',
                'query' => $request->q,
                'data' => $products->items(),
                'pagination' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ],
                'timestamp' => now()->toIso8601String()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Transform product data for API response
     *
     * @param Product $product
     * @param bool $detailed
     * @return array
     */
    private function transformProduct(Product $product, bool $detailed = false): array
    {
        $data = [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'short_description' => $product->short_description,
            'main_image' => $product->getMainImageUrl(),
            'images' => $product->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->getImageUrl(),
                    'alt_text' => $image->alt_text,
                    'is_primary' => $image->is_primary,
                ];
            }),
            'category' => $product->category ? [
                'id' => $product->category->id,
                'title' => $product->category->title,
                'slug' => $product->category->slug,
            ] : null,
            'price' => [
                'regular' => (float) $product->price,
                'sale' => $product->sale_price ? (float) $product->sale_price : null,
                'final' => $product->getFinalPrice(),
                'formatted_regular' => $product->getFormattedPrice(),
                'formatted_sale' => $product->getFormattedSalePrice(),
                'formatted_final' => $product->getFormattedFinalPrice(),
                'currency' => $product->curency,
                'is_on_sale' => $product->isOnSale(),
                'discount_percentage' => $product->getDiscountPercentage(),
                'discount_amount' => $product->getDiscountAmount(),
            ],
            'tax' => $product->getTaxInfo(),
            'stock' => [
                'is_available' => $product->isAvailableForPurchase(),
                'is_in_stock' => $product->isInStock(),
                'is_low_stock' => $product->isLowStock(),
                'quantity' => $product->track_inventory ? $product->stock_quantity : null,
                'low_stock_threshold' => $product->low_stock_threshold,
                'track_inventory' => $product->track_inventory,
            ],
            'availability' => [
                'is_available' => $product->is_available,
                'available_from' => $product->available_from?->toIso8601String(),
                'available_until' => $product->available_until?->toIso8601String(),
                'requires_login' => $product->requires_login,
            ],
            'status' => [
                'code' => $product->status_key_code,
                'is_published' => $product->isPublished(),
                'published_at' => $product->published_at?->toIso8601String(),
            ],
            'flags' => [
                'is_featured' => $product->is_featured,
                'show_on_home' => $product->show_on_home,
            ],
            'product_type' => $product->product_type,
            'product_type_label' => $product->getProductTypeLabel(),
            'sort_order' => $product->sort_order,
        ];

        // Add detailed information if requested
        if ($detailed) {
            $data['description'] = $product->description;
            $data['attributes'] = $product->attributes;
            $data['specifications'] = $product->specifications;
            $data['features'] = $product->getFormattedFeatures();

            // Add vendor info if available
            if ($product->relationLoaded('vendor') && $product->vendor) {
                $data['vendor'] = [
                    'id' => $product->vendor->id,
                    'name' => $product->vendor->name,
                ];
            }

            // SEO Information
            $data['seo'] = [
                'meta_title' => $product->meta_title ?: $product->name,
                'meta_description' => $product->meta_description ?: $product->short_description,
                'meta_keywords' => $product->meta_keywords,
                'canonical_url' => $product->canonical_url,
                'structured_data' => $product->structured_data,
                'og_title' => $product->meta_title ?: $product->name,
                'og_description' => $product->meta_description ?: $product->short_description,
                'og_image' => $product->getMainImageUrl(),
            ];

            // Visibility settings
            $data['visibility_settings'] = $product->visibility_settings;

            // Related products if loaded
            if ($product->relationLoaded('relatedProducts') && $product->relatedProducts->isNotEmpty()) {
                $data['related_products'] = $product->relatedProducts->map(function ($related) {
                    return [
                        'id' => $related->id,
                        'name' => $related->name,
                        'slug' => $related->slug,
                        'main_image' => $related->getMainImageUrl(),
                        'price' => $related->getFinalPrice(),
                    ];
                });
            }

            // Variants if loaded
            if ($product->relationLoaded('variants') && $product->variants->isNotEmpty()) {
                $data['variants'] = $product->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'sku' => $variant->sku,
                        'price' => (float) $variant->price,
                        'stock_quantity' => $variant->stock_quantity,
                        'is_default' => $variant->is_default,
                    ];
                });
            }

            // Tags if loaded
            if ($product->relationLoaded('tags') && $product->tags->isNotEmpty()) {
                $data['tags'] = $product->tags->map(function ($tag) {
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'slug' => $tag->slug,
                    ];
                });
            }
        } else {
            // Light version - just include essential attributes
            $data['attributes'] = $product->attributes;
            $data['specifications'] = $product->specifications;
            $data['features'] = $product->getFormattedFeatures();
        }

        return $data;
    }
}

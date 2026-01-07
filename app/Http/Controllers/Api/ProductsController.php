<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductsCategories;
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
                ->with(['category', 'images','variants']);

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
            // Normalize the requested URL (to match how it's stored in UrlRedirect)
            $requestedUrl = '/products/' . trim($slug, '/');
            $normalizedUrl = \App\Models\UrlRedirect::normalizeUrl($requestedUrl);

            // Check if there’s a redirect for this old URL
            $redirect = \App\Models\UrlRedirect::findByOldUrl($normalizedUrl);

            if ($redirect) {
                // Increment hit count
                $redirect->incrementHits();

                // If the redirect points to a valid product
                if ($redirect->entity_type === 'product' && $redirect->entity_id) {
                    $product = \App\Models\Product::query()
                        ->active()
                        ->where('id', $redirect->entity_id)
                        ->with(['category', 'images', 'vendor', 'tags', 'variants', 'urlRedirects'])
                        ->first();

                    if ($product) {
                        // Return redirect response (you can use 301 or 302)
                        return response()->json([
                            'success' => true,
                            'redirect' => true,
                            'redirect_type' => $redirect->redirect_type,
                            'new_url' => ltrim(str_replace('/products/', '', $redirect->new_url), '/'),
                            'message' => 'Redirected from old slug to new slug',
                            'timestamp' => now()->toIso8601String(),
                        ], $redirect->isPermanent() ? 301 : 302);
                    }
                }

                // If redirect exists but product not found
                return response()->json([
                    'success' => false,
                    'message' => 'Redirect target not found',
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            // No redirect found — try to find product by current slug
            $product = \App\Models\Product::query()
                ->active()
                ->where('slug', $slug)
                ->with(['category', 'images', 'vendor', 'tags', 'variants', 'urlRedirects'])
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
            $searchTerm = trim($request->q);

            $query = Product::query()
                ->select(['id', 'name', 'slug', 'sku', 'barcode', 'short_description'])
                ->active()
                ->available()
                ->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('slug', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('short_description', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('sku', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('barcode', 'LIKE', "%{$searchTerm}%");
                })
                ->with(['urlRedirects:id,entity_id,old_url,new_url,redirect_type']);

            // Pagination
            $perPage = min($request->get('per_page', 20), 100);
            $products = $query->paginate($perPage);

            // Transform products to limited data
            $products->getCollection()->transform(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'url_redirects' => $product->urlRedirects->map(function($redirect) {
                        return [
                            'id' => $redirect->id,
                            'old_url' => ltrim(str_replace('/products/', '', $redirect->old_url), '/'),
                            'new_url' => ltrim(str_replace('/products/', '', $redirect->new_url), '/'),
                            'redirect_type' => $redirect->redirect_type,
                        ];
                    }),
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'short_description' => $product->short_description,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Search results retrieved successfully',
                'query' => $searchTerm,
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
            'url_redirects' => $product->urlRedirects->map(function($redirect) {
                return [
                    'id' => $redirect->id,
                    'old_url' => ltrim(str_replace('/products/', '', $redirect->old_url), '/'),
                    'new_url' => ltrim(str_replace('/products/', '', $redirect->new_url), '/'),
                    'redirect_type' => $redirect->redirect_type,
                ];
            }),
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'short_description' => $product->short_description,
            'main_image' => $product->getMainImageUrl(),
            'images' => $product->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->getImageUrl(),
                    'media_type' => $image->media_type ?? 'image',
                    'mime_type' => $image->mime_type ?? 'image/jpeg',
                    'alt_text' => $image->alt_text,
                    'is_primary' => $image->is_primary,
                ];
            }),
            'category' => $product->category ? [
                'id' => $product->category->id,
                'title' => $product->category->title,
                'slug' => $product->category->slug,
                'translations' => $this->getCategoryTranslations($product->category),
            ] : null,
            'price' => [
                'regular' => format_amount($product->price),
                'sale' => $product->sale_price ? format_amount($product->sale_price) : 00.00,
                'final' =>  format_amount($product->getFinalPrice()) ?? 00.00,
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
                'is_available' =>  $product->isAvailableForPurchase(),
                'is_in_stock' => $product->isInStock(),
                'is_low_stock' => $product->isLowStock(),
                'quantity' => $product->track_inventory ? $product->stock_quantity : null,
                'low_stock_threshold' => $product->low_stock_threshold,
                'track_inventory' => $product->track_inventory,
                'available_stock' => $product->getTotalAvailableStock(),
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
            // ========== MULTI-LANGUAGE TRANSLATIONS ==========
            'translations' => $this->getProductTranslations($product),
            // =================================================
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
            $data['has_variants'] = $product->has_variants;
            // Variants if loaded
            if ($product->relationLoaded('variants') && $product->variants->isNotEmpty()) {
                $data['variants'] = $product->variants->map(function ($variant) {
                    return [
                        // Basic Information
                        'id' => $variant->id,
                        'variant_name' => $variant->variant_name,  // e.g., "Size", "Color", "Weight"
                        'variant_value' => $variant->variant_value, // e.g., "Large", "Red", "500g"
                        'full_name' => $variant->getFullName(), // e.g., "Size: Large"
                        'display_name' => $variant->getDisplayName(), // e.g., "Large"
                        'sku' => $variant->sku,

                        // Pricing
                        'price' => [
                            'regular' => format_amount($variant->price),
                            'sale' => $variant->sale_price ? format_amount($variant->sale_price) : null,
                            'final' => format_amount($variant->getFinalPrice()),
                            'formatted_regular' => $variant->getFormattedPrice(),
                            'formatted_sale' => $variant->getFormattedSalePrice(),
                            'formatted_final' => $variant->getFormattedFinalPrice(),
                            'is_on_sale' => $variant->isOnSale(),
                            'discount_percentage' => $variant->getDiscountPercentage(),
                            'discount_amount' => $variant->getDiscountAmount(),
                        ],

                        // Stock Information
                        'stock' => [
                            'quantity' => $variant->stock_quantity,
                            'low_stock_threshold' => $variant->low_stock_threshold,
                            'is_in_stock' => $variant->isInStock(),
                            'is_low_stock' => $variant->isLowStock(),
                            'is_out_of_stock' => $variant->isOutOfStock(),
                            'total_warehouse_stock' => $variant->getTotalWarehouseStock(),
                            'total_available_stock' => $variant->getTotalAvailableStock(),
                            'total_reserved_stock' => $variant->getTotalReservedStock(),
                        ],

                        // Shipping Information
                        'shipping' => [
                            'weight' => $variant->weight,
                            'formatted_weight' => $variant->getFormattedWeight(),
                            'dimensions' => [
                                'length' => $variant->length,
                                'width' => $variant->width,
                                'height' => $variant->height,
                                'formatted' => $variant->getDimensions(), // e.g., "10 × 5 × 3 cm"
                            ],
                            'has_dimensions' => $variant->hasPhysicalDimensions(),
                            'has_weight' => $variant->hasWeight(),
                        ],

                        // Image
                        'image' => [
                            'path' => $variant->image_path,
                            'url' => $variant->getImageUrl(),
                            'alt_text' => $variant->getFullName(),
                        ],

                        // Status & Availability
                        'status' => [
                            'code' => $variant->status_key_code,
                            'is_active' => $variant->isActive(),
                            'badge' => strip_tags($variant->getStatusBadge()), // Remove HTML for API
                        ],

                        // Flags
                        'is_default' => $variant->is_default,
                        'sort_order' => $variant->sort_order,

                        // Timestamps
                        'created_at' => $variant->created_at?->toIso8601String(),
                        'updated_at' => $variant->updated_at?->toIso8601String(),
                    ];
                });

                // Additional variant metadata
                $data['variant_metadata'] = [
                    'total_variants' => $product->variants->count(),
                    'default_variant_id' => $product->defaultVariant()?->id,
                    'has_variants' => $product->has_variants,
                    'active_variants_count' => $product->activeVariants()->count(),
                    'in_stock_variants_count' => $product->variants->filter(fn($v) => $v->isInStock())->count(),
                    'out_of_stock_variants_count' => $product->variants->filter(fn($v) => $v->isOutOfStock())->count(),
                ];
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

    /**
     * Get all translations for a category
     *
     * @param ProductsCategories $category
     * @return array
     */
    private function getCategoryTranslations(ProductsCategories $category): array
    {
        // Get all available languages
        $availableLanguages = get_available_languages(false); // Don't include English

        $translations = [
            'available_languages' => array_keys($availableLanguages),
            'has_translations' => false,
            'translation_stats' => [],
            'data' => []
        ];

        // English is the default, add it first
        $translations['data']['en'] = [
            'language_code' => 'en',
            'language_name' => 'English',
            'is_default' => true,
            'completion' => 100,
            'fields' => [
                'title' => $category->title,
                'short_description' => $category->short_description,
                'description' => $category->description,
                'meta_title' => $category->meta_title,
                'meta_description' => $category->meta_description,
                'meta_keywords' => $category->meta_keywords,
            ]
        ];

        // Get all category translations grouped by language
        if (method_exists($category, 'getTranslationsGroupedByLanguage')) {
            $categoryTranslations = $category->getTranslationsGroupedByLanguage();

            foreach ($availableLanguages as $langCode => $langInfo) {
                $langTranslations = $categoryTranslations[$langCode] ?? [];

                // Calculate completion
                $translatableFields = $category->getTranslatableFields();
                $translatedCount = count(array_filter($langTranslations, function($value) {
                    return !empty($value) && trim($value) !== '';
                }));
                $completion = count($translatableFields) > 0
                    ? round(($translatedCount / count($translatableFields)) * 100, 2)
                    : 0;

                // Only include languages that have at least some translations
                if ($completion > 0) {
                    $translations['has_translations'] = true;

                    $translations['data'][$langCode] = [
                        'language_code' => $langCode,
                        'language_name' => $langInfo['name'],
                        'native_name' => $langInfo['native_name'],
                        'flag' => $langInfo['flag'],
                        'direction' => $langInfo['direction'],
                        'is_rtl' => $langInfo['direction'] === 'rtl',
                        'is_default' => false,
                        'completion' => $completion,
                        'fields' => [
                            'title' => $langTranslations['title'] ?? null,
                            'short_description' => $langTranslations['short_description'] ?? null,
                            'description' => $langTranslations['description'] ?? null,
                            'meta_title' => $langTranslations['meta_title'] ?? null,
                            'meta_description' => $langTranslations['meta_description'] ?? null,
                            'meta_keywords' => $langTranslations['meta_keywords'] ?? null,
                        ]
                    ];

                    // Add to stats
                    $translations['translation_stats'][$langCode] = [
                        'completion' => $completion,
                        'translated_fields' => $translatedCount,
                        'total_fields' => count($translatableFields),
                        'missing_fields' => count($translatableFields) - $translatedCount,
                    ];
                }
            }
        }

        return $translations;
    }

    /**
     * Get all translations for a product
     *
     * @param Product $product
     * @return array
     */
    private function getProductTranslations(Product $product): array
    {
        // Get all available languages
        $availableLanguages = get_available_languages(false); // Don't include English

        $translations = [
            'available_languages' => array_keys($availableLanguages),
            'has_translations' => false,
            'translation_stats' => [],
            'data' => []
        ];

        // English is the default, add it first
        $translations['data']['en'] = [
            'language_code' => 'en',
            'language_name' => 'English',
            'is_default' => true,
            'completion' => 100,
            'fields' => [
                'name' => $product->name,
                'short_description' => $product->short_description,
                'description' => $product->description,
                'product_type' => $product->product_type,
                'meta_title' => $product->meta_title,
                'meta_description' => $product->meta_description,
                'meta_keywords' => $product->meta_keywords,
            ]
        ];

        // Get all product translations grouped by language
        if (method_exists($product, 'getTranslationsGroupedByLanguage')) {
            $productTranslations = $product->getTranslationsGroupedByLanguage();

            foreach ($availableLanguages as $langCode => $langInfo) {
                $langTranslations = $productTranslations[$langCode] ?? [];

                // Calculate completion
                $translatableFields = $product->getTranslatableFields();
                $translatedCount = count(array_filter($langTranslations, function($value) {
                    return !empty($value) && trim($value) !== '';
                }));
                $completion = count($translatableFields) > 0
                    ? round(($translatedCount / count($translatableFields)) * 100, 2)
                    : 0;

                // Only include languages that have at least some translations
                if ($completion > 0) {
                    $translations['has_translations'] = true;

                    $translations['data'][$langCode] = [
                        'language_code' => $langCode,
                        'language_name' => $langInfo['name'],
                        'native_name' => $langInfo['native_name'],
                        'flag' => $langInfo['flag'],
                        'direction' => $langInfo['direction'],
                        'is_rtl' => $langInfo['direction'] === 'rtl',
                        'is_default' => false,
                        'completion' => $completion,
                        'fields' => [
                            'name' => $langTranslations['name'] ?? null,
                            'short_description' => $langTranslations['short_description'] ?? null,
                            'description' => $langTranslations['description'] ?? null,
                            'product_type' => $langTranslations['product_type'] ?? null,
                            'meta_title' => $langTranslations['meta_title'] ?? null,
                            'meta_description' => $langTranslations['meta_description'] ?? null,
                            'meta_keywords' => $langTranslations['meta_keywords'] ?? null,
                        ]
                    ];

                    // Add to stats
                    $translations['translation_stats'][$langCode] = [
                        'completion' => $completion,
                        'translated_fields' => $translatedCount,
                        'total_fields' => count($translatableFields),
                        'missing_fields' => count($translatableFields) - $translatedCount,
                    ];
                }
            }
        }

        return $translations;
    }
}

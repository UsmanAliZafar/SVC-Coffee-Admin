<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductsCategories;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoriesController extends Controller
{
    /**
     * Get all categories with optional filters
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ProductsCategories::query()
                ->active()
                ->with(['parent', 'children'])
                ->withCount('products');

            // Filter by parent category
            if ($request->has('parent_id')) {
                if ($request->parent_id === 'null' || $request->parent_id === 'root') {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $request->parent_id);
                }
            }

            // Filter by featured
            if ($request->has('is_featured')) {
                $query->where('is_featured', filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN));
            }

            // Filter by show_in_menu
            if ($request->has('show_in_menu')) {
                $query->where('show_in_menu', filter_var($request->show_in_menu, FILTER_VALIDATE_BOOLEAN));
            }

            // Filter by show_on_home
            if ($request->has('show_on_home')) {
                $query->where('show_on_home', filter_var($request->show_on_home, FILTER_VALIDATE_BOOLEAN));
            }

            // Search by title
            if ($request->has('search')) {
                $query->search($request->search);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'order');
            $sortOrder = $request->get('sort_order', 'asc');

            if ($sortBy === 'order') {
                $query->ordered();
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = min($request->get('per_page', 15), 100); // Max 100 items per page
            $categories = $query->paginate($perPage);

            // Transform data
            $categories->getCollection()->transform(function ($category) {
                return $this->transformCategory($category);
            });

            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => $categories->items(),
                'pagination' => [
                    'total' => $categories->total(),
                    'per_page' => $categories->perPage(),
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'from' => $categories->firstItem(),
                    'to' => $categories->lastItem(),
                ],
                'timestamp' => now()->toIso8601String()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories',
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Get single category by ID
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $category = ProductsCategories::query()
                ->active()
                ->with(['parent', 'children' => function($query) {
                    $query->active()->ordered();
                }])
                ->withCount('products')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Category retrieved successfully',
                'data' => $this->transformCategory($category, true),
                'timestamp' => now()->toIso8601String()
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'timestamp' => now()->toIso8601String()
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve category',
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    public function getBySlug(string $slug): JsonResponse
    {
        try {
            // Normalize incoming category URL
            $requestedUrl = '/category/' . trim($slug, '/');
            $normalizedUrl = \App\Models\UrlRedirect::normalizeUrl($requestedUrl);

            // Check if redirect exists for this category
            $redirect = \App\Models\UrlRedirect::findByOldUrl($normalizedUrl);

            if ($redirect) {

                // Increment redirect hit counter
                $redirect->incrementHits();

                // If redirect points to a valid category
                if ($redirect->entity_type === 'category' && $redirect->entity_id) {

                    $category = \App\Models\ProductsCategories::query()
                        ->active()
                        ->where('id', $redirect->entity_id)
                        ->with([
                            'parent',
                            'children' => function ($query) {
                                $query->active()->ordered();
                            },
                        ])
                        ->withCount('products')
                        ->first();

                    if ($category) {
                        return response()->json([
                            'success'        => true,
                            'redirect'       => true,
                            'redirect_type'  => $redirect->redirect_type,
                            'new_url'        => ltrim(str_replace('/category/', '', $redirect->new_url), '/'),
                            'message'        => 'Redirected from old category slug to new slug',
                            'timestamp'      => now()->toIso8601String(),
                        ], $redirect->isPermanent() ? 301 : 302);
                    }
                }

                // If redirect exists but no category found
                return response()->json([
                    'success' => false,
                    'message' => 'Redirect target category not found',
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            // No redirect — load category by current slug
            $category = \App\Models\ProductsCategories::query()
                ->active()
                ->where('slug', $slug)
                ->with([
                    'parent',
                    'children' => function ($query) {
                        $query->active()->ordered();
                    },
                ])
                ->withCount('products')
                ->firstOrFail();

            // Increment views
            $category->incrementViews();

            return response()->json([
                'success'   => true,
                'message'   => 'Category retrieved successfully',
                'data'      => $this->transformCategory($category, true),
                'timestamp' => now()->toIso8601String(),
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'timestamp' => now()->toIso8601String(),
            ], 404);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve category',
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }


    /**
     * Get category tree structure
     *
     * @return JsonResponse
     */
    public function tree(): JsonResponse
    {
        try {
            $categories = ProductsCategories::query()
                ->active()
                ->with(['children' => function($query) {
                    $query->active()->ordered()->with('children');
                }])
                ->roots()
                ->ordered()
                ->get();

            $tree = $categories->map(function ($category) {
                return $this->transformCategoryTree($category);
            });

            return response()->json([
                'success' => true,
                'message' => 'Category tree retrieved successfully',
                'data' => $tree,
                'timestamp' => now()->toIso8601String()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve category tree',
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Get root categories (no parent)
     *
     * @return JsonResponse
     */
    public function roots(): JsonResponse
    {
        try {
            $categories = ProductsCategories::query()
                ->active()
                ->roots()
                ->ordered()
                ->withCount('products')
                ->get();

            $data = $categories->map(function ($category) {
                return $this->transformCategory($category);
            });

            return response()->json([
                'success' => true,
                'message' => 'Root categories retrieved successfully',
                'data' => $data,
                'timestamp' => now()->toIso8601String()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve root categories',
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Get featured categories
     *
     * @return JsonResponse
     */
    public function featured(): JsonResponse
    {
        try {
            $categories = ProductsCategories::query()
                ->active()
                ->featured()
                ->ordered()
                ->withCount('products')
                ->limit(10)
                ->get();

            $data = $categories->map(function ($category) {
                return $this->transformCategory($category);
            });

            return response()->json([
                'success' => true,
                'message' => 'Featured categories retrieved successfully',
                'data' => $data,
                'timestamp' => now()->toIso8601String()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve featured categories',
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Get categories for menu
     *
     * @return JsonResponse
     */
    public function menu(): JsonResponse
    {
        try {
            $categories = ProductsCategories::query()
                ->active()
                ->showInMenu()
                ->with(['children' => function($query) {
                    $query->active()->showInMenu()->ordered();
                }])
                ->roots()
                ->ordered()
                ->get();

            $data = $categories->map(function ($category) {
                return $this->transformCategoryForMenu($category);
            });

            return response()->json([
                'success' => true,
                'message' => 'Menu categories retrieved successfully',
                'data' => $data,
                'timestamp' => now()->toIso8601String()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve menu categories',
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Get categories for homepage
     *
     * @return JsonResponse
     */
    public function homepage(): JsonResponse
    {
        try {
            $categories = ProductsCategories::query()
                ->active()
                ->showOnHome()
                ->ordered()
                ->withCount('products')
                ->limit(8)
                ->get();

            $data = $categories->map(function ($category) {
                return $this->transformCategory($category);
            });

            return response()->json([
                'success' => true,
                'message' => 'Homepage categories retrieved successfully',
                'data' => $data,
                'timestamp' => now()->toIso8601String()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve homepage categories',
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Get category breadcrumbs
     *
     * @param string $id
     * @return JsonResponse
     */
    public function breadcrumbs(string $id): JsonResponse
    {
        try {
            $category = ProductsCategories::findOrFail($id);

            // Build breadcrumbs manually for API (without routes)
            $breadcrumbs = [];
            $ancestors = $category->getAncestors();

            foreach ($ancestors as $ancestor) {
                $breadcrumbs[] = [
                    'id' => $ancestor->id,
                    'title' => $ancestor->title,
                    'slug' => $ancestor->slug,
                ];
            }

            // Add current category
            $breadcrumbs[] = [
                'id' => $category->id,
                'title' => $category->title,
                'slug' => $category->slug,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Breadcrumbs retrieved successfully',
                'data' => $breadcrumbs,
                'timestamp' => now()->toIso8601String()
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'timestamp' => now()->toIso8601String()
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve breadcrumbs',
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Transform category data for API response
     *
     * @param ProductsCategories $category
     * @param bool $detailed
     * @return array
     */
    private function transformCategory(ProductsCategories $category, bool $detailed = false): array
    {
        $data = [
            'id' => $category->id,
            'title' => $category->title,
            'slug' => $category->slug,
            'url_redirects' => $category->urlRedirects->map(function($redirect) {
                return [
                    'id' => $redirect->id,
                    'old_url' => ltrim(str_replace('/products/', '', $redirect->old_url), '/'),
                    'new_url' => ltrim(str_replace('/products/', '', $redirect->new_url), '/'),
                    'redirect_type' => $redirect->redirect_type,
                ];
            }),
            'short_description' => $category->short_description,
            'parent_id' => $category->parent_id,
            'images' => $category->getAllImages(),
            'order' => $category->order,
            'is_featured' => $category->is_featured,
            'show_in_menu' => $category->show_in_menu,
            'show_on_home' => $category->show_on_home,
            'products_count' => $category->products_count ?? 0,
            'has_children' => $category->has_children,
            'depth' => $category->depth,
            // ========== MULTI-LANGUAGE TRANSLATIONS ==========
            'translations' => $this->getCategoryTranslations($category),
            // =================================================
        ];

        if ($detailed) {
            $data['description'] = $category->description;
            $data['full_path'] = $category->full_path;

            // Build SEO data without route helper
            $data['seo'] = [
                'title' => $category->meta_title ?: $category->title,
                'description' => $category->meta_description ?: $category->short_description,
                'keywords' => $category->meta_keywords,
                'canonical' => $category->canonical_url ?: null,
                'og_title' => $category->meta_title ?: $category->title,
                'og_description' => $category->meta_description ?: $category->short_description,
                'og_image' => $category->getImageUrl('image'),
                'structured_data' => $category->structured_data,
            ];

            if ($category->relationLoaded('parent') && $category->parent) {
                $data['parent'] = [
                    'id' => $category->parent->id,
                    'title' => $category->parent->title,
                    'slug' => $category->parent->slug,
                ];
            }

            if ($category->relationLoaded('children') && $category->children->isNotEmpty()) {
                $data['children'] = $category->children->map(function ($child) {
                    return $this->transformCategory($child);
                });
            }
        }

        return $data;
    }

    /**
     * Get all translations for a category
     *
     * @param ProductsCategories $category
     * @param array|string|null $requestedLanguages
     * @return array
     */
    private function getCategoryTranslations(ProductsCategories $category, $requestedLanguages = null): array
    {
        // Parse requested languages
        $languagesToInclude = null;
        if ($requestedLanguages) {
            $languagesToInclude = is_string($requestedLanguages)
                ? explode(',', $requestedLanguages)
                : $requestedLanguages;
        }

        // Get all available languages
        $availableLanguages = get_available_languages(false); // Don't include English

        // Filter languages if specific ones requested
        if ($languagesToInclude) {
            $availableLanguages = array_intersect_key(
                $availableLanguages,
                array_flip($languagesToInclude)
            );
        }

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

            // Use helper function to get translatable fields
            $translatableFields = get_translatable_fields('category');

            foreach ($availableLanguages as $langCode => $langInfo) {
                $langTranslations = $categoryTranslations[$langCode] ?? [];

                // Calculate completion
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
     * Transform category tree recursively
     *
     * @param ProductsCategories $category
     * @return array
     */
    private function transformCategoryTree(ProductsCategories $category): array
    {
        $data = $this->transformCategory($category);

        if ($category->relationLoaded('children') && $category->children->isNotEmpty()) {
            $data['children'] = $category->children->map(function ($child) {
                return $this->transformCategoryTree($child);
            });
        }

        return $data;
    }

    /**
     * Transform category for menu
     *
     * @param ProductsCategories $category
     * @return array
     */
    private function transformCategoryForMenu(ProductsCategories $category): array
    {
        $data = [
            'id' => $category->id,
            'title' => $category->title,
            'slug' => $category->slug,
            'icon' => $category->getImageUrl('icon'),
            'order' => $category->order,
        ];

        if ($category->relationLoaded('children') && $category->children->isNotEmpty()) {
            $data['children'] = $category->children->map(function ($child) {
                return $this->transformCategoryForMenu($child);
            });
        }

        return $data;
    }

    /**
     * Get products by category ID
     *
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function products(string $id, Request $request): JsonResponse
    {
        try {
            $category = ProductsCategories::findOrFail($id);

            $query = $category->products()
                ->active()
                ->available()
                ->with(['category', 'images','variants']);

            // Filter by featured
            if ($request->has('is_featured')) {
                $query->where('is_featured', filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN));
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
                'category' => [
                    'id' => $category->id,
                    'title' => $category->title,
                    'slug' => $category->slug,
                ],
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

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'timestamp' => now()->toIso8601String()
            ], 404);

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
     * Get products by category slug
     *
     * @param string $slug
     * @param Request $request
     * @return JsonResponse
     */
    public function productsBySlug(string $slug, Request $request): JsonResponse
    {
        try {
            // Normalize incoming category URL
            $requestedUrl = '/category/' . trim($slug, '/');
            $normalizedUrl = \App\Models\UrlRedirect::normalizeUrl($requestedUrl);

            // Check if redirect exists
            $redirect = \App\Models\UrlRedirect::findByOldUrl($normalizedUrl);

            if ($redirect) {

                // Increment redirect hit count
                $redirect->incrementHits();

                // If redirect has a valid category target
                if ($redirect->entity_type === 'category' && $redirect->entity_id) {

                    $category = \App\Models\ProductsCategories::query()
                        ->active()
                        ->where('id', $redirect->entity_id)
                        ->first();

                    if ($category) {
                        return response()->json([
                            'success'        => true,
                            'redirect'       => true,
                            'redirect_type'  => $redirect->redirect_type,
                            'new_url'        => ltrim(str_replace('/category/', '', $redirect->new_url), '/'),
                            'message'        => 'Redirected from old category slug to new slug',
                            'timestamp'      => now()->toIso8601String(),
                        ], $redirect->isPermanent() ? 301 : 302);
                    }
                }

                // Redirect exists but category missing
                return response()->json([
                    'success' => false,
                    'message' => 'Redirect target category not found',
                    'timestamp' => now()->toIso8601String()
                ], 404);
            }

            // No redirect found — load category by slug
            $category = \App\Models\ProductsCategories::where('slug', $slug)->firstOrFail();

            // Pass category ID to products() method
            $request->merge(['category_id' => $category->id]);

            return $this->products($category->id, $request);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'timestamp' => now()->toIso8601String()
            ], 404);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to load category products',
                'error'   => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }
    /**
     * Get featured products from a category
     *
     * @param string $id
     * @return JsonResponse
     */
    public function featuredProducts(string $id): JsonResponse
    {
        try {
            $category = ProductsCategories::findOrFail($id);

            $products = $category->products()
                ->active()
                ->available()
                ->featured()
                ->inStock()
                ->with(['category', 'images'])
                ->ordered()
                ->limit(10)
                ->get();

            $data = $products->map(function ($product) {
                return $this->transformProduct($product);
            });

            return response()->json([
                'success' => true,
                'message' => 'Featured products retrieved successfully',
                'category' => [
                    'id' => $category->id,
                    'title' => $category->title,
                    'slug' => $category->slug,
                ],
                'data' => $data,
                'timestamp' => now()->toIso8601String()
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'timestamp' => now()->toIso8601String()
            ], 404);

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
     * Transform product data for API response
     *
     * @param Product $product
     * @param bool $detailed
     * @return array
     */
    private function transformProduct($product, bool $detailed = false): array
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
            if ($product->vendor) {
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
            $data['has_variants'] = $product->has_variants;
            // Variants if loaded - COMPLETE VERSION WITH ALL FIELDS
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


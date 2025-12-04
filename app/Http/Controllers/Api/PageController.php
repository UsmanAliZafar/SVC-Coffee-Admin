<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Get all published pages with pagination
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $perPage = min($perPage, 100); // Max 100 per page

            $pages = Page::published()
                ->public()
                ->with(['parent', 'children'])
                ->orderBy('display_order')
                ->orderBy('title')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Pages retrieved successfully',
                'data' => [
                    'pages' => $pages->map(fn($page) => $this->formatPageData($page)),
                    'pagination' => [
                        'total' => $pages->total(),
                        'per_page' => $pages->perPage(),
                        'current_page' => $pages->currentPage(),
                        'last_page' => $pages->lastPage(),
                        'from' => $pages->firstItem(),
                        'to' => $pages->lastItem(),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single page by slug
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function show(string $slug): JsonResponse
    {
        try {
            $page = Page::where('slug', $slug)
                ->published()
                ->public()
                ->with(['parent', 'children'])
                ->first();

            if (!$page) {
                return response()->json([
                    'success' => false,
                    'message' => 'Page not found',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Page retrieved successfully',
                'data' => $this->formatPageDetail($page)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve page',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get page by ID
     *
     * @param string $id
     * @return JsonResponse
     */
    public function showById(string $id): JsonResponse
    {
        try {
            $page = Page::where('id', $id)
                ->published()
                ->public()
                ->with(['parent', 'children'])
                ->first();

            if (!$page) {
                return response()->json([
                    'success' => false,
                    'message' => 'Page not found',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Page retrieved successfully',
                'data' => $this->formatPageDetail($page)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve page',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pages for header menu
     *
     * @return JsonResponse
     */
    public function headerMenu(): JsonResponse
    {
        try {
            $pages = Page::published()
                ->public()
                ->inHeader()
                ->parentOnly()
                ->with(['children' => function($query) {
                    $query->published()
                          ->public()
                          ->where('show_in_header', true)
                          ->orderBy('display_order');
                }])
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Header menu pages retrieved successfully',
                'data' => $pages->map(fn($page) => $this->formatMenuPage($page))
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve header menu pages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pages for footer menu
     *
     * @return JsonResponse
     */
    public function footerMenu(): JsonResponse
    {
        try {
            $pages = Page::published()
                ->public()
                ->inFooter()
                ->parentOnly()
                ->with(['children' => function($query) {
                    $query->published()
                          ->public()
                          ->where('show_in_footer', true)
                          ->orderBy('display_order');
                }])
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Footer menu pages retrieved successfully',
                'data' => $pages->map(fn($page) => $this->formatMenuPage($page))
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve footer menu pages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get parent pages only
     *
     * @return JsonResponse
     */
    public function parents(): JsonResponse
    {
        try {
            $pages = Page::published()
                ->public()
                ->parentOnly()
                ->orderBy('display_order')
                ->orderBy('title')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Parent pages retrieved successfully',
                'data' => $pages->map(fn($page) => $this->formatPageData($page))
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve parent pages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get child pages of a specific parent
     *
     * @param string $parentSlug
     * @return JsonResponse
     */
    public function children(string $parentSlug): JsonResponse
    {
        try {
            $parent = Page::where('slug', $parentSlug)
                ->published()
                ->public()
                ->first();

            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent page not found',
                    'data' => null
                ], 404);
            }

            $children = $parent->children()
                ->published()
                ->public()
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Child pages retrieved successfully',
                'data' => [
                    'parent' => $this->formatPageData($parent),
                    'children' => $children->map(fn($page) => $this->formatPageData($page))
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve child pages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get page breadcrumbs
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function breadcrumbs(string $slug): JsonResponse
    {
        try {
            $page = Page::where('slug', $slug)
                ->published()
                ->public()
                ->first();

            if (!$page) {
                return response()->json([
                    'success' => false,
                    'message' => 'Page not found',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Breadcrumbs retrieved successfully',
                'data' => $page->getBreadcrumbs()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve breadcrumbs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search pages
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'query' => 'required|string|min:2|max:255',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $query = $validated['query'];
            $perPage = $validated['per_page'] ?? 15;

            $pages = Page::published()
                ->public()
                ->where(function($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('excerpt', 'LIKE', "%{$query}%")
                      ->orWhere('content', 'LIKE', "%{$query}%")
                      ->orWhere('meta_keywords', 'LIKE', "%{$query}%");
                })
                ->orderBy('display_order')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Search completed successfully',
                'data' => [
                    'query' => $query,
                    'results' => $pages->map(fn($page) => $this->formatSearchResult($page)),
                    'pagination' => [
                        'total' => $pages->total(),
                        'per_page' => $pages->perPage(),
                        'current_page' => $pages->currentPage(),
                        'last_page' => $pages->lastPage(),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get page sitemap data
     *
     * @return JsonResponse
     */
    public function sitemap(): JsonResponse
    {
        try {
            $pages = Page::published()
                ->public()
                ->orderBy('display_order')
                ->get();

            $sitemap = $pages->map(function($page) {
                return [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'url' => $page->url,
                    'parent_id' => $page->parent_id,
                    'updated_at' => $page->updated_at->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Sitemap data retrieved successfully',
                'data' => $sitemap
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve sitemap data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get page by template type
     *
     * @param string $template
     * @return JsonResponse
     */
    public function byTemplate(string $template): JsonResponse
    {
        try {
            $pages = Page::published()
                ->public()
                ->where('template', $template)
                ->orderBy('display_order')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Pages retrieved successfully',
                'data' => [
                    'template' => $template,
                    'pages' => $pages->map(fn($page) => $this->formatPageData($page))
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // PRIVATE HELPER METHODS
    // ============================================

    /**
     * Format basic page data
     */
    private function formatPageData($page): array
    {
        return [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'excerpt' => $page->excerpt,
            'url' => $page->url,
            'featured_image' => $page->featured_image,
            'featured_image_alt' => $page->featured_image_alt,
            'template' => $page->template,
            'display_order' => $page->display_order,
            'menu_label' => $page->menu_label,
            'parent_id' => $page->parent_id,
            'has_children' => $page->hasChildren(),
            'published_date' => $page->published_date,
            'created_at' => $page->created_at->toIso8601String(),
            'updated_at' => $page->updated_at->toIso8601String(),
        ];
    }

    /**
     * Format detailed page data
     */
    private function formatPageDetail($page): array
    {
        return [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'excerpt' => $page->excerpt,
            'content' => $page->content,
            'url' => $page->url,
            'featured_image' => $page->featured_image,
            'featured_image_alt' => $page->featured_image_alt,
            'template' => $page->template,
            'display_order' => $page->display_order,
            'menu_label' => $page->menu_label,
            'custom_css_class' => $page->custom_css_class,
            'meta' => [
                'title' => $page->meta_title,
                'description' => $page->meta_description,
                'keywords' => $page->meta_keywords,
            ],
            'parent' => $page->parent ? [
                'id' => $page->parent->id,
                'title' => $page->parent->title,
                'slug' => $page->parent->slug,
                'url' => $page->parent->url,
            ] : null,
            'children' => $page->children->map(fn($child) => [
                'id' => $child->id,
                'title' => $child->title,
                'slug' => $child->slug,
                'url' => $child->url,
                'menu_label' => $child->menu_label,
            ]),
            'breadcrumbs' => $page->getBreadcrumbs(),
            'full_path' => $page->full_path,
            'has_children' => $page->hasChildren(),
            'published_at' => $page->published_at?->toIso8601String(),
            'published_date' => $page->published_date,
            'created_at' => $page->created_at->toIso8601String(),
            'updated_at' => $page->updated_at->toIso8601String(),
        ];
    }

    /**
     * Format page for menu
     */
    private function formatMenuPage($page): array
    {
        return [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'url' => $page->url,
            'menu_label' => $page->menu_label,
            'display_order' => $page->display_order,
            'custom_css_class' => $page->custom_css_class,
            'has_children' => $page->hasChildren(),
            'children' => $page->children->map(fn($child) => [
                'id' => $child->id,
                'title' => $child->title,
                'slug' => $child->slug,
                'url' => $child->url,
                'menu_label' => $child->menu_label,
                'display_order' => $child->display_order,
                'custom_css_class' => $child->custom_css_class,
            ]),
        ];
    }

    /**
     * Format search result
     */
    private function formatSearchResult($page): array
    {
        return [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'excerpt' => $page->excerpt,
            'url' => $page->url,
            'featured_image' => $page->featured_image,
            'published_date' => $page->published_date,
            'parent' => $page->parent ? [
                'title' => $page->parent->title,
                'slug' => $page->parent->slug,
            ] : null,
        ];
    }
}

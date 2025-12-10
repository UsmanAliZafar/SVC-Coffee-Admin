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
            'show_in_header' => $page->show_in_header,
            'show_in_footer' => $page->show_in_footer,
            'has_children' => $page->hasChildren(),
            'published_date' => $page->published_date,
            'created_at' => $page->created_at->toIso8601String(),
            'updated_at' => $page->updated_at->toIso8601String(),
            // ========== MULTI-LANGUAGE TRANSLATIONS ==========
                'translations' => $this->getPageTranslations($page),
            // =================================================
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
            // ========== MULTI-LANGUAGE TRANSLATIONS ==========
            'translations' => $this->getPageTranslations($page),
            // =================================================
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

    /**
     * Get all translations for a page
     *
     * @param Page $page
     * @param array|string|null $requestedLanguages
     * @return array
     */
    private function getPageTranslations(Page $page, $requestedLanguages = null): array
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
                'title' => $page->title,
                'excerpt' => $page->excerpt,
                'content' => $page->content,
                'meta_title' => $page->meta_title,
                'meta_description' => $page->meta_description,
                'meta_keywords' => $page->meta_keywords,
                'featured_image_alt' => $page->featured_image_alt,
                'menu_label' => $page->menu_label,
            ]
        ];

        // Get all page translations grouped by language
        if (method_exists($page, 'getTranslationsGroupedByLanguage')) {
            $pageTranslations = $page->getTranslationsGroupedByLanguage();

            // Use helper function to get translatable fields
            $translatableFields = $page->getTranslatableFields();

            foreach ($availableLanguages as $langCode => $langInfo) {
                $langTranslations = $pageTranslations[$langCode] ?? [];

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
                            'excerpt' => $langTranslations['excerpt'] ?? null,
                            'content' => $langTranslations['content'] ?? null,
                            'meta_title' => $langTranslations['meta_title'] ?? null,
                            'meta_description' => $langTranslations['meta_description'] ?? null,
                            'meta_keywords' => $langTranslations['meta_keywords'] ?? null,
                            'featured_image_alt' => $langTranslations['featured_image_alt'] ?? null,
                            'menu_label' => $langTranslations['menu_label'] ?? null,
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

    public function testPageTranslations(string $slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();

        // Direct query
        $directTranslations = \DB::table('translations')
            ->where('module', 'page')
            ->where('item_id', $page->id)
            ->get();

        // Via relationship
        $relationshipTranslations = $page->translations()->get();

        // Via trait method
        $groupedTranslations = $page->getTranslationsGroupedByLanguage();

        return response()->json([
            'page_id' => $page->id,
            'module' => $page->getTranslationModule(),
            'direct_count' => $directTranslations->count(),
            'direct_data' => $directTranslations,
            'relationship_count' => $relationshipTranslations->count(),
            'relationship_data' => $relationshipTranslations,
            'grouped_data' => $groupedTranslations,
            'has_translations' => !empty($groupedTranslations),
        ]);
    }

    public function testActualResponse(string $slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();

        // Test the helper function
        $availableLanguages = get_available_languages(false);

        // Test the actual method
        $translations = $this->getPageTranslations($page);

        return response()->json([
            'available_languages_helper' => $availableLanguages,
            'grouped_translations' => $page->getTranslationsGroupedByLanguage(),
            'final_translations_output' => $translations,
        ]);
    }
}

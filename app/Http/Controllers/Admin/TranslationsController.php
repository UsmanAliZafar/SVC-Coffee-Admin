<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

// Models
use App\Models\Translation;
use App\Models\Product;
use App\Models\ProductsCategories;
use App\Models\Vendor;
use App\Models\ProductTag;
use App\Models\Page;

class TranslationsController extends Controller
{
    /**
     * Supported modules configuration
     */
    protected $supportedModules = [
        'product' => [
            'model' => Product::class,
            'table' => 'products',
            'name_field' => 'name',
            'permission' => 'products',
            'fields' => ['name', 'short_description', 'description','product_type', 'meta_title', 'meta_description', 'meta_keywords'],
        ],
        'category' => [
            'model' => ProductsCategories::class,
            'table' => 'products_categories',
            'name_field' => 'title',
            'permission' => 'categories',
            'fields' => ['title', 'short_description', 'description', 'meta_title', 'meta_description', 'meta_keywords'],
        ],
        'vendor' => [
            'model' => Vendor::class,
            'table' => 'vendors',
            'name_field' => 'name',
            'permission' => 'vendors',
            'fields' => ['name', 'description', 'short_description'],
        ],
        'tag' => [
            'model' => ProductTag::class,
            'table' => 'product_tags',
            'name_field' => 'name',
            'permission' => 'tags',
            'fields' => ['name', 'description'],
        ],
        'page' => [
            'model' => Page::class,
            'table' => 'pages',
            'name_field' => 'title',
            'permission' => 'pages',
            'fields' => ['title', 'excerpt', 'content', 'meta_title', 'meta_description', 'meta_keywords', 'featured_image_alt', 'menu_label'],
        ],
    ];

    /**
     * Get module configuration
     */
    protected function getModuleConfig(string $module): ?array
    {
        return $this->supportedModules[$module] ?? null;
    }

    /**
     * Validate module and get model instance
     */
    protected function getModelInstance(string $module, string $itemId)
    {
        $config = $this->getModuleConfig($module);

        if (!$config) {
            throw new \Exception("Unsupported module: {$module}");
        }

        $modelClass = $config['model'];
        $model = $modelClass::find($itemId);

        if (!$model) {
            throw new \Exception("Item not found in {$module}");
        }

        return $model;
    }

    /**
     * Check permission for module action
     */
    protected function checkPermission(string $module, string $action = 'read'): bool
    {
        $config = $this->getModuleConfig($module);

        if (!$config) {
            return false;
        }

        $permission = $config['permission'] . '.' . $action;
        return auth('admin')->user()->hasPermission($permission);
    }

    // ==================== API ENDPOINTS ====================

    /**
     * Get translations for a specific item and language
     *
     * GET /admin/translations/get
     * Query params: module, item_id, lang
     */
    public function getTranslations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module' => 'required|string',
            'item_id' => 'required|uuid',
            'lang' => 'required|string|size:2',
        ], [
            'module.required' => 'Module is required',
            'item_id.required' => 'Item ID is required',
            'item_id.uuid' => 'Invalid Item ID format',
            'lang.required' => 'Language code is required',
            'lang.size' => 'Language code must be 2 characters',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $module = $request->input('module');
            $itemId = $request->input('item_id');
            $lang = $request->input('lang');

            // Check permission
            if (!$this->checkPermission($module, 'read')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Get model instance
            $model = $this->getModelInstance($module, $itemId);

            // Get all translations for this language
            $translations = $model->getAllTranslations($lang);

            // Get translation completion
            $completion = $model->getTranslationCompleteness($lang);

            // Get original values for comparison
            $config = $this->getModuleConfig($module);
            $originalValues = [];
            foreach ($config['fields'] as $field) {
                $originalValues[$field] = $model->$field ?? '';
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'translations' => $translations,
                    'original_values' => $originalValues,
                    'completion' => $completion,
                    'has_translation' => $model->hasTranslation($lang),
                    'available_languages' => $model->getAvailableLanguages(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get translations error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get translations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all translations for an item (all languages)
     *
     * GET /admin/translations/get-all
     * Query params: module, item_id
     */
    public function getAllTranslations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module' => 'required|string',
            'item_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $module = $request->input('module');
            $itemId = $request->input('item_id');

            // Check permission
            if (!$this->checkPermission($module, 'read')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Get model instance
            $model = $this->getModelInstance($module, $itemId);

            // Get translations grouped by language
            $translationsGrouped = $model->getTranslationsGroupedByLanguage();

            // Get translation statistics
            $stats = $model->getTranslationStats();

            return response()->json([
                'success' => true,
                'data' => [
                    'translations' => $translationsGrouped,
                    'stats' => $stats,
                    'available_languages' => $model->getAvailableLanguages(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get all translations error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get translations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save/Update translations for a specific item and language
     *
     * POST /admin/translations/save
     * Body: module, item_id, lang, translations (array of field => value)
     */
    public function saveTranslations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module' => 'required|string',
            'item_id' => 'required|uuid',
            'lang' => 'required|string|size:2',
            'translations' => 'required|array',
            'translations.*' => 'nullable|string',
        ], [
            'module.required' => 'Module is required',
            'item_id.required' => 'Item ID is required',
            'item_id.uuid' => 'Invalid Item ID format',
            'lang.required' => 'Language code is required',
            'lang.size' => 'Language code must be 2 characters',
            'translations.required' => 'Translations data is required',
            'translations.array' => 'Translations must be an array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $module = $request->input('module');
            $itemId = $request->input('item_id');
            $lang = $request->input('lang');
            $translations = $request->input('translations');
            // dd($module);
            // Check permission
            if (!$this->checkPermission($module, 'update')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Don't allow saving English translations
            if ($lang === 'en') {
                return response()->json([
                    'success' => false,
                    'message' => 'English content should be saved in the main fields, not as translations'
                ], 400);
            }

            // Get model instance
            $model = $this->getModelInstance($module, $itemId);

            // Get module config to validate fields
            $config = $this->getModuleConfig($module);
            $allowedFields = $config['fields'];

            // Filter and save only allowed fields
            $filteredTranslations = [];
            foreach ($translations as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    $filteredTranslations[$field] = $value;
                }
            }

            // Filter out empty values
            $filteredTranslations = array_filter($filteredTranslations, function($value) {
                return !is_null($value) && trim($value) !== '';
            });

            if (empty($filteredTranslations)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid translations provided'
                ], 400);
            }

            // Save translations
            $model->setTranslations($lang, $filteredTranslations);

            // Get updated completion
            $completion = $model->getTranslationCompleteness($lang);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Translations saved successfully',
                'data' => [
                    'completion' => $completion,
                    'saved_fields' => array_keys($filteredTranslations),
                    'saved_count' => count($filteredTranslations),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Save translations error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save translations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk save translations (multiple languages at once)
     *
     * POST /admin/translations/bulk-save
     * Body: module, item_id, translations (array of lang => fields)
     */
    public function bulkSaveTranslations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module' => 'required|string',
            'item_id' => 'required|uuid',
            'translations' => 'required|array',
            'translations.*' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $module = $request->input('module');
            $itemId = $request->input('item_id');
            $translations = $request->input('translations');

            // Check permission
            if (!$this->checkPermission($module, 'update')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Get model instance
            $model = $this->getModelInstance($module, $itemId);

            // Get module config
            $config = $this->getModuleConfig($module);
            $allowedFields = $config['fields'];

            $savedLanguages = [];
            $stats = [];

            foreach ($translations as $lang => $fields) {
                // Skip English
                if ($lang === 'en') {
                    continue;
                }

                // Filter allowed fields
                $filteredFields = [];
                foreach ($fields as $field => $value) {
                    if (in_array($field, $allowedFields) && !empty($value) && trim($value) !== '') {
                        $filteredFields[$field] = $value;
                    }
                }

                if (!empty($filteredFields)) {
                    $model->setTranslations($lang, $filteredFields);
                    $savedLanguages[] = $lang;
                    $stats[$lang] = [
                        'saved_fields' => count($filteredFields),
                        'completion' => $model->getTranslationCompleteness($lang),
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Translations saved successfully for ' . count($savedLanguages) . ' language(s)',
                'data' => [
                    'saved_languages' => $savedLanguages,
                    'stats' => $stats,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk save translations error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save translations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete translation for a specific language
     *
     * DELETE /admin/translations/delete
     * Body: module, item_id, lang
     */
    public function deleteTranslation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module' => 'required|string',
            'item_id' => 'required|uuid',
            'lang' => 'required|string|size:2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $module = $request->input('module');
            $itemId = $request->input('item_id');
            $lang = $request->input('lang');

            // Check permission
            if (!$this->checkPermission($module, 'delete')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Don't allow deleting English
            if ($lang === 'en') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete English content'
                ], 400);
            }

            // Delete translations
            $deletedCount = Translation::where('module', $module)
                                      ->where('item_id', $itemId)
                                      ->where('lang', $lang)
                                      ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Translation deleted successfully",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete translation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete translation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete specific field translation
     *
     * DELETE /admin/translations/delete-field
     * Body: module, item_id, lang, field
     */
    public function deleteFieldTranslation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module' => 'required|string',
            'item_id' => 'required|uuid',
            'lang' => 'required|string|size:2',
            'field' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $module = $request->input('module');
            $itemId = $request->input('item_id');
            $lang = $request->input('lang');
            $field = $request->input('field');

            // Check permission
            if (!$this->checkPermission($module, 'update')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Delete specific field translation
            $deleted = Translation::where('module', $module)
                                 ->where('item_id', $itemId)
                                 ->where('lang', $lang)
                                 ->where('field', $field)
                                 ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Field translation deleted successfully',
                'deleted' => $deleted > 0
            ]);

        } catch (\Exception $e) {
            Log::error('Delete field translation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete field translation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get translation statistics for an item
     *
     * GET /admin/translations/stats
     * Query params: module, item_id
     */
    public function getStats(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module' => 'required|string',
            'item_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $module = $request->input('module');
            $itemId = $request->input('item_id');

            // Check permission
            if (!$this->checkPermission($module, 'read')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Get model instance
            $model = $this->getModelInstance($module, $itemId);

            // Get translation stats
            $stats = $model->getTranslationStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Get stats error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clone translations from one language to another
     *
     * POST /admin/translations/clone
     * Body: module, item_id, from_lang, to_lang
     */
    public function cloneTranslation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module' => 'required|string',
            'item_id' => 'required|uuid',
            'from_lang' => 'required|string|size:2',
            'to_lang' => 'required|string|size:2|different:from_lang',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $module = $request->input('module');
            $itemId = $request->input('item_id');
            $fromLang = $request->input('from_lang');
            $toLang = $request->input('to_lang');

            // Check permission
            if (!$this->checkPermission($module, 'update')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Get model instance
            $model = $this->getModelInstance($module, $itemId);

            // Clone translations
            $model->cloneTranslations($fromLang, $toLang);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Translations cloned from {$fromLang} to {$toLang} successfully"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Clone translation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to clone translation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available languages list
     *
     * GET /admin/translations/languages
     */
    public function getAvailableLanguages()
    {
        try {
            $languages = get_available_languages(false); // Don't include English

            return response()->json([
                'success' => true,
                'data' => $languages
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get languages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get translatable fields for a module
     *
     * GET /admin/translations/fields
     * Query param: module
     */
    public function getTranslatableFields(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $module = $request->input('module');
            $config = $this->getModuleConfig($module);

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid module'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'module' => $module,
                    'fields' => $config['fields'],
                    'name_field' => $config['name_field'],
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get fields: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search translations across all modules
     *
     * GET /admin/translations/search
     * Query params: query, lang (optional), module (optional)
     */
    public function searchTranslations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'lang' => 'nullable|string|size:2',
            'module' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = $request->input('query');
            $lang = $request->input('lang');
            $module = $request->input('module');

            $translationsQuery = Translation::where('value', 'like', "%{$query}%")
                                           ->active();

            if ($lang) {
                $translationsQuery->where('lang', $lang);
            }

            if ($module) {
                $translationsQuery->where('module', $module);
            }

            $translations = $translationsQuery->with(['creator'])
                                             ->limit(50)
                                             ->get();

            return response()->json([
                'success' => true,
                'data' => $translations,
                'count' => $translations->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Search translations error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to search translations: ' . $e->getMessage()
            ], 500);
        }
    }
}

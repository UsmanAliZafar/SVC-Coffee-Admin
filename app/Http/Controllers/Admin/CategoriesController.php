<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
// Models
use App\Models\ProductsCategories;
use App\Models\SystemStatus;
use App\Models\UrlRedirect;

class CategoriesController extends Controller
{
    /**
     * Display listing page
     */
    public function index()
    {
        $statusList = SystemStatus::where('module', 'categories')->get();
        $parentCategories = ProductsCategories::roots()->active()->ordered()->get();

        return view('admin.categories.index', compact('statusList', 'parentCategories'));
    }

    /**
     * Ajax DataTable data
     */
    public function getData(Request $request)
    {
        $query = ProductsCategories::with(['parent', 'status', 'creator'])
            ->withCount('products');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status_key_code', $request->status);
        }

        if ($request->filled('parent_id')) {
            if ($request->parent_id === 'root') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        if ($request->filled('is_featured')) {
            $query->where('is_featured', $request->is_featured);
        }

        if ($request->filled('show_in_menu')) {
            $query->where('show_in_menu', $request->show_in_menu);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        return DataTables::of($query)
            ->addColumn('checkbox', function ($category) {
                return '<input type="checkbox" class="category-checkbox" value="' . $category->id . '">';
            })
           ->addColumn('image_preview', function ($category) {
                $imageUrl = $category->getImageUrl('thumbnail');
                return '<img src="' . $imageUrl . '"
                            alt="' . htmlspecialchars($category->title) . '"
                            class="img-thumbnail category-image-preview"
                            style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;"
                            onclick="viewCategoryImage(\'' . $imageUrl . '\', \'' . htmlspecialchars($category->title, ENT_QUOTES) . '\')">';
            })
            ->addColumn('title_link', function ($category) {
                $depth = $category->depth;
                $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $depth);
                $icon = $category->hasChildren() ? '<i class="bi bi-folder-fill text-warning"></i>' : '<i class="bi bi-tag-fill text-primary"></i>';

                $title = htmlspecialchars($category->title, ENT_QUOTES);
                $created = $category->created_at
                    ? '<div class="text-muted small mt-1"><i class="bi bi-calendar3"></i> Created On: ' . $category->created_at->format('Y-m-d H:i:s A') . '</div>'
                    : '';

                return $indent . $icon . ' <a href="' . route('admin.categories.show', $category->id) . '" class="fw-semibold">' . $title . '</a>' . $created;
            })
            ->addColumn('parent_name', function ($category) {
                return $category->parent ? $category->parent->title : '<span class="badge bg-secondary">Root</span>';
            })
            ->addColumn('products_count_badge', function ($category) {
                $count = $category->products_count;
                $badgeClass = $count > 0 ? 'bg-success' : 'bg-secondary';
                return '<span class="badge ' . $badgeClass . '">' . $count . '</span>';
            })
            ->addColumn('status_badge', function ($category) {
                return $category->getStatusBadge();
            })
            ->addColumn('badges', function ($category) {
                $badges = '';
                if ($category->is_featured) {
                    $badges .= '<span class="badge bg-warning text-dark me-1"><i class="bi bi-star-fill"></i> Featured</span>';
                }
                if ($category->show_in_menu) {
                    $badges .= '<span class="badge bg-info me-1"><i class="bi bi-menu-button"></i> Menu</span>';
                }
                if ($category->show_on_home) {
                    $badges .= '<span class="badge bg-primary me-1"><i class="bi bi-house"></i> Home</span>';
                }
                return $badges ?: '<span class="text-muted">—</span>';
            })
            ->addColumn('actions', function ($category) {
                $actions = '<div class="btn-group btn-group-sm" role="group">';

                if (auth('admin')->user()->hasPermission('categories.read')) {
                    $actions .= '<a href="' . route('admin.categories.show', $category->id) . '" class="btn btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>';
                }

                if (auth('admin')->user()->hasPermission('categories.update')) {
                    $actions .= '<a href="' . route('admin.categories.edit', $category->id) . '" class="btn btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>';
                }

                if (auth('admin')->user()->hasPermission('categories.delete')) {
                    $actions .= '<button type="button" class="btn btn-outline-danger delete-category" data-id="' . $category->id . '" title="Delete"><i class="bi bi-trash"></i></button>';
                }

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['checkbox', 'image_preview', 'title_link', 'parent_name', 'products_count_badge', 'status_badge', 'badges', 'actions'])
            ->make(true);
    }

    /**
     * Show create form
     */
    public function create()
    {
        // dd(1);
        $statusList = SystemStatus::where('module', 'categories')->get();
        $parentCategories = ProductsCategories::getFlatList();

        return view('admin.categories.create', compact('statusList', 'parentCategories'));
    }

    /**
     * Store new category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products_categories,slug',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'parent_id' => 'nullable|uuid|exists:products_categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'icon' => 'nullable|file|mimes:jpeg,png,jpg,webp,svg|max:1024',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
            'order' => 'nullable|integer|min:0',
            'is_featured' => 'nullable|boolean',
            'show_in_menu' => 'nullable|boolean',
            'show_on_home' => 'nullable|boolean',
            'status_key_code' => 'required|string|exists:system_statuses,key_code',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
        ], [
            // Custom error messages
            'title.required' => 'Category title is required',
            'title.max' => 'Category title cannot exceed 255 characters',
            'slug.unique' => 'This slug is already taken',
            'parent_id.exists' => 'Selected parent category does not exist',
            'image.image' => 'Main image must be a valid image file',
            'image.mimes' => 'Main image must be jpeg, png, jpg, or webp',
            'image.max' => 'Main image size cannot exceed 2MB',
            'banner_image.image' => 'Banner image must be a valid image file',
            'banner_image.max' => 'Banner image size cannot exceed 2MB',
            'icon.max' => 'Icon size cannot exceed 1MB',
            'thumbnail.max' => 'Thumbnail size cannot exceed 1MB',
            'status_key_code.required' => 'Status is required',
            'status_key_code.exists' => 'Selected status is invalid',
        ]);

        DB::beginTransaction();
        try {
            // Handle file uploads
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('categories/images', 'public');
            }
            if ($request->hasFile('banner_image')) {
                $validated['banner_image'] = $request->file('banner_image')->store('categories/banners', 'public');
            }
            if ($request->hasFile('icon')) {
                $validated['icon'] = $request->file('icon')->store('categories/icons', 'public');
            }
            if ($request->hasFile('thumbnail')) {
                $validated['thumbnail'] = $request->file('thumbnail')->store('categories/thumbnails', 'public');
            }

            $validated['order'] = $validated['order'] ?? 0;
            $validated['is_featured'] = $request->has('is_featured') ? true : false;
            $validated['show_in_menu'] = $request->has('show_in_menu') ? true : false;
            $validated['show_on_home'] = $request->has('show_on_home') ? true : false;

            // Ensure parent_id is null if empty string
            if (empty($validated['parent_id'])) {
                $validated['parent_id'] = null;
            }
            $category = ProductsCategories::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully!',
                'redirect' => route('admin.categories.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show category details
     */
    public function show($id)
    {
        $category = ProductsCategories::with(['parent', 'children', 'products', 'status', 'creator', 'updater'])
            ->withCount('products')
            ->findOrFail($id);

        // ADD THIS LINE - Get redirects for this category
        $categoryRedirects = UrlRedirect::forEntity('category', $id)
                ->orderBy('created_at', 'desc')
                ->get();

        return view('admin.categories.show', compact('category', 'categoryRedirects'));
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $category = ProductsCategories::findOrFail($id);

        // Get redirects for this category
        $categoryRedirects = UrlRedirect::forEntity('category', $id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

        $statusList = SystemStatus::where('module', 'categories')->get();
        $parentCategories = ProductsCategories::getFlatList();

        return view('admin.categories.edit', compact(
            'category',
            'statusList',
            'parentCategories',
            'categoryRedirects'
        ));
    }

    /**
     * Update category
     */
    public function update(Request $request, $id)
    {
        $category = ProductsCategories::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            // 'slug' => 'nullable|string|max:255|unique:products_categories,slug',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'parent_id' => 'nullable|uuid|exists:products_categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'icon' => 'nullable|file|mimes:jpeg,png,jpg,webp,svg|max:1024',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
            'order' => 'nullable|integer|min:0',
            'is_featured' => 'nullable|boolean',
            'show_in_menu' => 'nullable|boolean',
            'show_on_home' => 'nullable|boolean',
            'status_key_code' => 'required|string|exists:system_statuses,key_code',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
        ], [
            // Custom error messages
            'title.required' => 'Category title is required',
            'title.max' => 'Category title cannot exceed 255 characters',
            'slug.unique' => 'This slug is already taken',
            'parent_id.exists' => 'Selected parent category does not exist',
            'image.image' => 'Main image must be a valid image file',
            'image.mimes' => 'Main image must be jpeg, png, jpg, or webp',
            'image.max' => 'Main image size cannot exceed 2MB',
            'banner_image.image' => 'Banner image must be a valid image file',
            'banner_image.max' => 'Banner image size cannot exceed 2MB',
            'icon.max' => 'Icon size cannot exceed 1MB',
            'thumbnail.max' => 'Thumbnail size cannot exceed 1MB',
            'status_key_code.required' => 'Status is required',
            'status_key_code.exists' => 'Selected status is invalid',
        ]);


        DB::beginTransaction();
        try {
            // Handle file uploads
            foreach (['image', 'banner_image', 'icon', 'thumbnail'] as $field) {
                if ($request->hasFile($field)) {
                    // Delete old file
                    if ($category->$field) {
                        Storage::disk('public')->delete($category->$field);
                    }
                    $validated[$field] = $request->file($field)->store('categories/' . str_replace('_', 's/', $field), 'public');
                }
            }
            // Handle default values for fields that might be null
            $validated['order'] = $validated['order'] ?? $category->order ?? 0;
            $validated['is_featured'] = $request->has('is_featured') ? true : false;
            $validated['show_in_menu'] = $request->has('show_in_menu') ? true : false;
            $validated['show_on_home'] = $request->has('show_on_home') ? true : false;

            // Ensure parent_id is null if empty string
            if (empty($validated['parent_id'])) {
                $validated['parent_id'] = null;
            }
            $category->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully!',
                'redirect' => route('admin.categories.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateUrl(Request $request, $id)
    {
        try {
            $category = ProductsCategories::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'slug' => 'required|string|max:255|unique:products_categories,slug,' . $id . ',id',
                'create_redirect' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $oldSlug = $category->slug;
            $newSlug = Str::slug($request->slug);

            // Check if slug actually changed
            if ($oldSlug === $newSlug) {
                return response()->json([
                    'success' => false,
                    'message' => 'New URL is the same as the current URL'
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Update category slug
                $category->update(['slug' => $newSlug]);

                // Create redirect if requested
                if ($request->create_redirect) {
                    $oldUrl = '/category/' . $oldSlug;
                    $newUrl = '/category/' . $newSlug;

                    // Check if redirect already exists
                    $existingRedirect = UrlRedirect::where('old_url', $oldUrl)->first();

                    if ($existingRedirect) {
                        // Update existing redirect
                        $existingRedirect->update([
                            'new_url' => $newUrl,
                            'redirect_type' => '301',
                            'is_active' => true,
                            'notes' => 'Updated redirect due to category slug change on ' . now()->format('Y-m-d H:i:s'),
                            'updated_by' => auth('admin')->id(),
                        ]);
                    } else {
                        // Create new redirect
                        UrlRedirect::create([
                            'old_url' => $oldUrl,
                            'new_url' => $newUrl,
                            'redirect_type' => '301',
                            'entity_type' => 'category',
                            'entity_id' => $category->id,
                            'is_active' => true,
                            'notes' => 'Auto-generated redirect due to category slug change',
                            'created_by' => auth('admin')->id(),
                        ]);
                    }

                    // Update any existing redirects that point to the old URL
                    UrlRedirect::where('new_url', $oldUrl)
                        ->where('is_active', true)
                        ->update([
                            'new_url' => $newUrl,
                            'updated_by' => auth('admin')->id(),
                        ]);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Category URL updated successfully' . ($request->create_redirect ? ' with redirect' : '')
                ]);

            } catch (\Exception $e) {
                DB::rollBack();

                // Check for specific duplicate entry error
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A redirect for this URL already exists. Please contact administrator or try a different URL.'
                    ], 422);
                }

                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update URL. Please try again or contact support.'
            ], 500);
        }
    }

    /**
     * Delete category
     */
    public function destroy($id)
    {
        try {
            $category = ProductsCategories::findOrFail($id);

            // Check if category has products
            if ($category->products_count > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with products. Please move or delete products first.'
                ], 400);
            }

            // Check if category has children
            if ($category->hasChildren()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with subcategories. Please delete subcategories first.'
                ], 400);
            }

            // Delete images
            foreach (['image', 'banner_image', 'icon', 'thumbnail'] as $field) {
                if ($category->$field) {
                    Storage::disk('public')->delete($category->$field);
                }
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle category status
     */
    public function toggleStatus($id)
    {
        try {
            $category = ProductsCategories::findOrFail($id);

            $newStatus = $category->status_key_code === 'CATEGORY_ACTIVE'
                ? 'CATEGORY_INACTIVE'
                : 'CATEGORY_ACTIVE';

            $category->update(['status_key_code' => $newStatus]);

            return response()->json([
                'success' => true,
                'message' => 'Category status updated successfully!',
                'status' => $newStatus
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured($id)
    {
        try {
            $category = ProductsCategories::findOrFail($id);
            $category->update(['is_featured' => !$category->is_featured]);

            return response()->json([
                'success' => true,
                'message' => 'Featured status updated successfully!',
                'is_featured' => $category->is_featured
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update featured status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Category tree view
     */
    public function tree()
    {
        $categories = ProductsCategories::with(['children' => function($query) {
            $query->ordered();
        }])
        ->roots()
        ->ordered()
        ->get();

        return view('admin.categories.tree', compact('categories'));
    }

    /**
     * Get empty categories
     */
    public function empty()
    {
        $categories = ProductsCategories::doesntHave('products')
            ->with(['parent', 'status'])
            ->withCount('products') // This will show 0 for empty categories
            ->ordered()
            ->paginate(20);

        return view('admin.categories.empty', compact('categories'));
    }

    /**
     * Reorder categories
     */
    public function reorder(Request $request)
    {
        try {
            $orders = $request->input('orders', []);

            DB::beginTransaction();
            foreach ($orders as $order => $id) {
                ProductsCategories::where('id', $id)->update(['order' => $order]);
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Categories reordered successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder categories: ' . $e->getMessage()
            ], 500);
        }
    }
}

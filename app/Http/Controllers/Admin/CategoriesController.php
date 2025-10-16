<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductsCategories;
use App\Models\SystemStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

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
                return '<img src="' . $imageUrl . '" alt="' . $category->title . '" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">';
            })
            ->addColumn('title_link', function ($category) {
                $depth = $category->depth;
                $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $depth);
                $icon = $category->hasChildren() ? '<i class="bi bi-folder-fill text-warning"></i>' : '<i class="bi bi-tag-fill text-primary"></i>';

                return $indent . $icon . ' <a href="' . route('admin.categories.show', $category->id) . '" class="fw-semibold">' . $category->title . '</a>';
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
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:1024',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
            'order' => 'nullable|integer|min:0',
            'is_featured' => 'boolean',
            'show_in_menu' => 'boolean',
            'show_on_home' => 'boolean',
            'status_key_code' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:500',
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

        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $category = ProductsCategories::findOrFail($id);
        $statusList = SystemStatus::where('type', 'category')->get();
        $parentCategories = ProductsCategories::where('id', '!=', $id)->get();
        $parentCategories = ProductsCategories::getFlatList();

        return view('admin.categories.edit', compact('category', 'statusList', 'parentCategories'));
    }

    /**
     * Update category
     */
    public function update(Request $request, $id)
    {
        $category = ProductsCategories::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products_categories,slug,' . $id,
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'parent_id' => 'nullable|uuid|exists:products_categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:1024',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
            'order' => 'nullable|integer|min:0',
            'is_featured' => 'boolean',
            'show_in_menu' => 'boolean',
            'show_on_home' => 'boolean',
            'status_key_code' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:500',
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
        $categories = ProductsCategories::where('products_count', 0)
            ->with(['parent', 'status'])
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductsCategories;
use App\Models\ProductTag;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\UrlRedirect;
use App\Models\SystemStatus;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ProductsController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index()
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.read')) {
            abort(403, 'Unauthorized access');
        }

        // Get status list for filters
        $statusList = SystemStatus::where('module', 'products')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get categories for filters
        $categories = ProductsCategories::active()
            ->roots()
            ->ordered()
            ->get();

        // Get all categories (flat list with indentation)
        $allCategories = ProductsCategories::getFlatList();

        // Get vendors for filters
        $vendors = Vendor::active()->ordered()->get();

        // Get tags for filters
        $tags = ProductTag::active()->ordered()->get();

        return view('admin.products.index', compact(
            'statusList',
            'categories',
            'allCategories',
            'vendors',
            'tags'
        ));
    }

    /**
     * Get products data for DataTable (AJAX)
     */
    public function getData(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Product::with([
            'category',
            'status',
            'vendor',
            'images' => function($q) {
                $q->where('is_primary', true);
            }
        ]);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status_key_code', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('product_type')) {
            $query->where('product_type', $request->product_type);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->filled('is_featured')) {
            $query->where('is_featured', $request->is_featured);
        }

        if ($request->filled('show_on_home')) {
            $query->where('show_on_home', $request->show_on_home);
        }

        if ($request->filled('is_available')) {
            $query->where('is_available', $request->is_available);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'in_stock') {
                $query->where('stock_quantity', '>', 0);
            } elseif ($request->stock_status === 'out_of_stock') {
                $query->where('track_inventory', true)
                    ->where('stock_quantity', '<=', 0);
            } elseif ($request->stock_status === 'low_stock') {
                $query->where('track_inventory', true)
                    ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                    ->where('stock_quantity', '>', 0);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('stock_level')) {
            if ($request->stock_level === 'critical') {
                $query->where('track_inventory', true)
                    ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                    ->where('stock_quantity', '<=', 5)
                    ->where('stock_quantity', '>', 0);
            } elseif ($request->stock_level === 'very_low') {
                $query->where('track_inventory', true)
                    ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                    ->whereBetween('stock_quantity', [6, 10]);
            } elseif ($request->stock_level === 'low') {
                $query->where('track_inventory', true)
                    ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                    ->whereBetween('stock_quantity', [11, 20]);
            }
        }

        return DataTables::of($query)
            ->addColumn('checkbox', function($product) {
                if (auth('admin')->user()->hasPermission('products.delete')) {
                    return '<input type="checkbox" class="form-check-input product-checkbox" value="' . $product->id . '">';
                }
                return '';
            })
            ->addColumn('image_preview', function($product) {
                $imageUrl = $product->getMainImageUrl();
                return '<img src="' . $imageUrl . '"
                            alt="' . htmlspecialchars($product->name) . '"
                            class="img-thumbnail product-image-preview"
                            style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;"
                            onclick="viewProductImage(\'' . $imageUrl . '\', \'' . htmlspecialchars($product->name, ENT_QUOTES) . '\')"
                            onerror="this.style.display=\'none\'; this.parentElement.innerHTML+=\'<div class=\\\'img-thumbnail d-flex align-items-center justify-content-center\\\' style=\\\'width: 50px; height: 50px; background-color: #f8f9fa;\\\'><i class=\\\'bi bi-image-fill text-muted\\\'></i></div>\'">';
            })
            ->addColumn('name_link', function($product) {
                $editUrl = route('admin.products.edit', $product->id);
                $viewUrl = route('admin.products.show', $product->id);

                $html = '<div>';
                $html .= '<strong><a href="' . $editUrl . '" class="text-decoration-none">' . $product->name . '</a></strong><br>';
                $html .= '<small class="text-muted">SKU: ' . $product->sku . '</small>';
                if ($product->barcode) {
                    $html .= '<br><small class="text-muted">Barcode: ' . $product->barcode . '</small>';
                }
                $html .= '</div>';

                return $html;
            })
            ->addColumn('category_name', function($product) {
                return $product->category ? $product->category->title : '<span class="text-muted">N/A</span>';
            })
            ->addColumn('price_display', function($product) {
                $html = '<div>';

                if ($product->isOnSale()) {
                    $html .= '<span class="text-decoration-line-through text-muted">' . $product->getFormattedPrice() . '</span><br>';
                    $html .= '<strong class="text-success">' . $product->getFormattedSalePrice() . '</strong>';
                    $html .= ' <span class="badge bg-danger">-' . $product->getDiscountPercentage() . '%</span>';
                } else {
                    $html .= '<strong>' . $product->getFormattedPrice() . '</strong>';
                }

                $html .= '</div>';
                return $html;
            })
            ->addColumn('stock_badge', function($product) {
                $canUpdate = auth('admin')->user()->hasPermission('products.update');

                if (!$product->track_inventory) {
                    $badge = '<span class="badge bg-info">No Tracking</span>';
                } else {
                    $stock = $product->getTotalStock();

                    if ($stock <= 0) {
                        $badge = '<span class="badge bg-danger">Out of Stock (' . $stock . ')</span>';
                    } elseif ($stock < 10) {
                        $badge = '<span class="badge bg-warning">Low Stock (' . $stock . ')</span>';
                    } else {
                        $badge = '<span class="badge bg-success">In Stock (' . $stock . ')</span>';
                    }
                }

                if ($canUpdate && $product->track_inventory) {
                    return '
                        <div class="stock-badge-container">
                            ' . $badge . '
                            <button type="button" class="btn btn-sm btn-link p-0 ms-1 quick-stock-btn"
                                data-id="' . $product->id . '"
                                data-name="' . htmlspecialchars($product->name) . '"
                                data-stock="' . $product->stock_quantity . '"
                                data-threshold="' . $product->low_stock_threshold . '"
                                title="Manage Stock">
                                <i class="bi bi-pencil-square text-primary"></i>
                            </button>
                        </div>
                    ';
                }

                return $badge;
            })
            ->addColumn('status_badge', function($product) {
                return $product->getStatusBadge();
            })
            ->addColumn('badges', function($product) {
                $badges = [];

                if ($product->is_featured) {
                    $badges[] = '<span class="badge bg-warning"><i class="bi bi-star-fill"></i> Featured</span>';
                }

                if ($product->show_on_home) {
                    $badges[] = '<span class="badge bg-info"><i class="bi bi-house-fill"></i> Homepage</span>';
                }

                if ($product->product_type !== 'simple') {
                    $badges[] = '<span class="badge bg-secondary">' . ucfirst($product->product_type) . '</span>';
                }

                return implode(' ', $badges);
            })
            ->addColumn('product_type_badge', function($product) {
                $badges = [
                    'simple' => '<span class="badge bg-primary">Simple</span>',
                    'variable' => '<span class="badge bg-info">Variable</span>',
                    'grouped' => '<span class="badge bg-success">Grouped</span>',
                    'external' => '<span class="badge bg-warning">External</span>',
                ];

                return $badges[$product->product_type] ?? '<span class="badge bg-secondary">' . ucfirst($product->product_type) . '</span>';
            })
            ->addColumn('actions', function($product) {
                $actions = '<div class="btn-group" role="group">';

                // View button
                 if (auth('admin')->user()->hasPermission('products.read')) {
                    $actions .= '<a href="' . route('admin.products.show', $product->id) . '" class="btn btn-sm btn-info" title="View">
                        <i class="bi bi-eye"></i>
                    </a>';
                }

                // Edit button
                if (auth('admin')->user()->hasPermission('products.update')) {
                    $actions .= '<a href="' . route('admin.products.edit', $product->id) . '" class="btn btn-sm btn-primary" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>';

                    // Quick Stock Update button (for low stock view)
                    if (request()->filled('stock_status') && request()->stock_status === 'low_stock') {
                        $actions .= '<button type="button" class="btn btn-sm btn-warning quick-stock-update"
                            data-id="' . $product->id . '"
                            data-name="' . htmlspecialchars($product->name) . '"
                            data-stock="' . $product->stock_quantity . '"
                            title="Quick Stock Update">
                            <i class="bi bi-lightning"></i>
                        </button>';
                    }
                }

                // Delete button
                if (auth('admin')->user()->hasPermission('products.delete')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-danger delete-product" data-id="' . $product->id . '" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>';
                }

                $actions .= '</div>';

                return $actions;
            })
            ->addColumn('current_stock', function($product) {
                $stock = $product->stock_quantity;
                $class = 'text-success';

                if ($stock <= 5) {
                    $class = 'text-danger fw-bold';
                } elseif ($stock <= 10) {
                    $class = 'text-warning fw-bold';
                }

                return '<span class="' . $class . '">' . $stock . '</span>';
            })
            ->addColumn('threshold', function($product) {
                return '<span class="badge bg-secondary">' . $product->low_stock_threshold . '</span>';
            })
            ->rawColumns(['checkbox', 'image_preview', 'name_link', 'category_name', 'current_stock', 'threshold', 'price_display', 'stock_badge', 'product_type_badge', 'status_badge', 'badges', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.create')) {
            abort(403, 'Unauthorized access');
        }

        // Get all necessary data for form
        $categories = ProductsCategories::getFlatList();
        $vendors = Vendor::active()->ordered()->get();
        $tags = ProductTag::active()->ordered()->get();

        $statusList = SystemStatus::where('module', 'products')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $productTypes = [
            'simple' => 'Simple Product',
            'variable' => 'Variable Product',
            'grouped' => 'Grouped Product',
            'external' => 'External/Affiliate Product',
        ];

        $currencies = get_currencies();

        return view('admin.products.create', compact(
            'categories',
            'vendors',
            'tags',
            'statusList',
            'productTypes',
            'currencies'
        ));
    }

    /**
     * Store a newly created product in database
     */
    public function store(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.create')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'sku' => 'required|string|max:255|unique:products,sku',
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
            'category_id' => 'nullable|exists:products_categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'product_type' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'cost_price' => 'nullable|numeric|min:0',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'status_key_code' => 'required|string',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'session_id' => 'nullable|string',
            'is_taxable' => 'nullable',
            'tax_type' => 'nullable|in:inclusive,exclusive',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_class' => 'nullable|string|max:100',
            'features' => 'nullable|json',
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
            // Prepare product data
            $productData = $request->only([
                'name', 'slug', 'sku', 'barcode', 'short_description', 'description',
                'category_id', 'vendor_id', 'product_type', 'curency',
                'price', 'sale_price', 'cost_price', 'status_key_code',
                'is_featured', 'show_on_home', 'is_available', 'track_inventory',
                'stock_quantity', 'low_stock_threshold',
                'meta_title', 'meta_description', 'meta_keywords', 'canonical_url',
                'is_taxable', 'tax_type', 'tax_percentage', 'tax_class',
            ]);

            // Handle main image upload
            if ($request->hasFile('main_image')) {
                $image = $request->file('main_image');
                $imageName = time() . '_' . Str::random(10) . '.' . $image->extension();
                $imagePath = $image->storeAs('products', $imageName, 'public');
                $productData['main_image'] = $imagePath;
            }

            // Convert checkboxes
            $productData['is_featured'] = $request->has('is_featured');
            $productData['show_on_home'] = $request->has('show_on_home');
            $productData['is_available'] = $request->has('is_available') ?? true;
            $productData['track_inventory'] = $request->has('track_inventory') ?? true;
            $productData['is_taxable'] = $request->has('is_taxable');

            if (!isset($productData['tax_type'])) {
                $productData['tax_type'] = 'exclusive';
            }
            if (!isset($productData['tax_percentage'])) {
                $productData['tax_percentage'] = 0;
            }
            // Set default product type if empty
            if (empty($productData['product_type'])) {
                $productData['product_type'] = 'simple';
            }

            // Set default stock values
            if (!isset($productData['stock_quantity'])) {
                $productData['stock_quantity'] = 0;
            }
            if (!isset($productData['low_stock_threshold'])) {
                $productData['low_stock_threshold'] = 10;
            }

            // Set created_by
            $productData['created_by'] = auth('admin')->id();

             // Attach features if provided
            if ($request->filled('features')) {
                $features = json_decode($request->features, true);

                // Validate features structure
                if (is_array($features)) {
                    // Optional: Add server-side validation
                    $richTextCount = count(array_filter($features, fn($f) => ($f['type'] ?? '') === 'rich_text'));
                    if ($richTextCount > 1) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Only one Rich Text feature is allowed'
                        ], 422);
                    }

                    $productData['features'] = $features;
                }
            }

            // Create product
            $product = Product::create($productData);

            // Attach tags if provided
            if ($request->filled('tags')) {
                $tags = $request->tags;

                // If it's a JSON string, decode it
                if (is_string($tags)) {
                    $tags = json_decode($tags, true);
                }

                // Make sure it's an array and not empty
                if (is_array($tags) && !empty($tags)) {
                    // Filter out empty values and ensure all are valid UUIDs
                    $tags = array_filter($tags, function($tag) {
                        return !empty($tag) && is_string($tag);
                    });

                    if (!empty($tags)) {
                        $product->tags()->attach($tags);
                    }
                }
            }

            // Move temp images to product folder
            if ($request->filled('session_id')) {
                $this->moveTempImages($request->session_id, $product->id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'product_id' => $product->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product
     */
    public function show($id)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.read')) {
            abort(403, 'Unauthorized access');
        }

        $product = Product::with([
            'category',
            'vendor',
            'status',
            'images',
            'variants',
            'tags',
            'creator',
            'updater'
        ])->findOrFail($id);

        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product
     */
    public function edit($id)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.update')) {
            abort(403, 'Unauthorized access');
        }

        $product = Product::with([
            'category',
            'vendor',
            'images',
            'variants',
            'tags'
        ])->findOrFail($id);

        $productRedirects = UrlRedirect::forEntity('product', $id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        // Get all necessary data for form
        $categories = ProductsCategories::getFlatList();
        $vendors = Vendor::active()->ordered()->get();
        $tags = ProductTag::active()->ordered()->get();

        $statusList = SystemStatus::where('module', 'products')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $productTypes = [
            'simple' => 'Simple Product',
            'variable' => 'Variable Product',
            'grouped' => 'Grouped Product',
            'external' => 'External/Affiliate Product',
        ];

        $currencies = get_currencies();

        return view('admin.products.edit', compact(
            'product',
            'categories',
            'vendors',
            'tags',
            'statusList',
            'productTypes',
            'currencies',
            'productRedirects'
        ));
    }

    /**
     * Update the specified product in database
     */
    public function update(Request $request, $id)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $product = Product::findOrFail($id);

        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $id,
            'sku' => 'required|string|max:255|unique:products,sku,' . $id,
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $id,
            'category_id' => 'nullable|exists:products_categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'product_type' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'cost_price' => 'nullable|numeric|min:0',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'status_key_code' => 'required|string',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_taxable' => 'nullable',
            'tax_type' => 'nullable|in:inclusive,exclusive',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_class' => 'nullable|string|max:100',
            'features' => 'nullable|json',
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
            // Prepare product data
            $productData = $request->only([
                'name', 'slug', 'sku', 'barcode', 'short_description', 'description',
                'category_id', 'vendor_id', 'product_type', 'curency',
                'price', 'sale_price', 'cost_price', 'status_key_code',
                'is_featured', 'show_on_home', 'is_available', 'track_inventory',
                'meta_title', 'meta_description', 'meta_keywords', 'canonical_url',
                'is_taxable', 'tax_type', 'tax_percentage', 'tax_class'
            ]);

            // Handle main image upload
            if ($request->hasFile('main_image')) {
                // Delete old image
                if ($product->main_image && Storage::disk('public')->exists($product->main_image)) {
                    Storage::disk('public')->delete($product->main_image);
                }

                $image = $request->file('main_image');
                $imageName = time() . '_' . Str::random(10) . '.' . $image->extension();
                $imagePath = $image->storeAs('products', $imageName, 'public');
                $productData['main_image'] = $imagePath;
            }

            // Convert checkboxes
            $productData['is_featured'] = $request->has('is_featured');
            $productData['show_on_home'] = $request->has('show_on_home');
            $productData['is_available'] = $request->has('is_available');
            $productData['track_inventory'] = $request->has('track_inventory');
            $productData['is_taxable'] = $request->has('is_taxable');

            // Set updated_by
            $productData['updated_by'] = auth('admin')->id();
            // Attach features if provided
            if ($request->filled('features')) {
                $features = json_decode($request->features, true);

                // Validate features structure
                if (is_array($features)) {
                    // Optional: Add server-side validation
                    $richTextCount = count(array_filter($features, fn($f) => ($f['type'] ?? '') === 'rich_text'));
                    if ($richTextCount > 1) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Only one Rich Text feature is allowed'
                        ], 422);
                    }

                    $productData['features'] = $features;
                }
            }
            // Update product
            $product->update($productData);

            // Sync tags
            if ($request->has('tags')) {
                $tags = $request->tags;

                // If it's a JSON string, decode it
                if (is_string($tags)) {
                    $tags = json_decode($tags, true);
                }

                // Make sure it's an array
                if (is_array($tags)) {
                    // Filter out empty values
                    $tags = array_filter($tags, function($tag) {
                        return !empty($tag) && is_string($tag);
                    });

                    $product->tags()->sync($tags);
                } else {
                    // If tags is empty, detach all
                    $product->tags()->sync([]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product from database
     */
    public function destroy($id)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();

        try {
            $product = Product::findOrFail($id);

            // Delete main image
            if ($product->main_image && Storage::disk('public')->exists($product->main_image)) {
                Storage::disk('public')->delete($product->main_image);
            }

            // Delete all product images
            foreach ($product->images as $image) {
                if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
                $image->delete();
            }

            // Delete product (soft delete)
            $product->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload product images via AJAX
     */
    public function uploadImages(Request $request, $id)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::findOrFail($id);
        $uploadedImages = [];

        try {
            if ($request->hasFile('images')) {
                $maxOrder = ProductImage::where('product_id', $product->id)->max('sort_order') ?? -1;

                foreach ($request->file('images') as $image) {
                    $imageName = time() . '_' . Str::random(10) . '.' . $image->extension();
                    $imagePath = $image->storeAs('products/gallery', $imageName, 'public');

                    $productImage = ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $imagePath,
                        'image_name' => $image->getClientOriginalName(),
                        'sort_order' => ++$maxOrder,
                        'is_primary' => false,
                    ]);

                    $uploadedImages[] = [
                        'id' => $productImage->id,
                        'url' => $productImage->getImageUrl(),
                        'name' => $productImage->image_name,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Images uploaded successfully',
                'images' => $uploadedImages
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload images: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete product image
     */
    public function deleteImage($productId, $imageId)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $image = ProductImage::where('product_id', $productId)
                ->where('id', $imageId)
                ->firstOrFail();

            // Delete file from storage
            if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }

            $image->delete();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set primary image
     */
    public function setPrimaryImage($productId, $imageId)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $image = ProductImage::where('product_id', $productId)
                ->where('id', $imageId)
                ->firstOrFail();

            $image->setAsPrimary();

            return response()->json([
                'success' => true,
                'message' => 'Primary image set successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set primary image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk status update
     */
    public function bulkStatusUpdate(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'status_key_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            Product::whereIn('id', $request->product_ids)
                ->update([
                    'status_key_code' => $request->status_key_code,
                    'updated_by' => auth('admin')->id()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Products status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export products to CSV
     */
    public function export(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.read')) {
            abort(403, 'Unauthorized access');
        }

        $query = Product::with(['category', 'vendor', 'status']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status_key_code', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->get();

        $filename = 'products_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'ID', 'Name', 'SKU', 'Barcode', 'Category', 'Type', 'Price',
                'Sale Price', 'Stock', 'Status', 'Featured', 'Created At'
            ]);

            // Data
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->sku,
                    $product->barcode,
                    $product->category ? $product->category->title : '',
                    $product->product_type,
                    $product->price,
                    $product->sale_price,
                    $product->getTotalStock(),
                    $product->status ? $product->status->name : '',
                    $product->is_featured ? 'Yes' : 'No',
                    $product->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Duplicate product
     */
    public function duplicate($id)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.create')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();

        try {
            $originalProduct = Product::with(['images', 'tags', 'variants'])->findOrFail($id);

            // Create duplicate product
            $newProduct = $originalProduct->replicate();
            $newProduct->name = $originalProduct->name . ' (Copy)';
            $newProduct->slug = Str::slug($newProduct->name) . '-' . time();
            $newProduct->sku = 'COPY-' . strtoupper(Str::random(10));
            $newProduct->barcode = $originalProduct->barcode ? 'COPY-' . $originalProduct->barcode : null;
            $newProduct->created_by = auth('admin')->id();
            $newProduct->updated_by = null;
            $newProduct->save();

            // Duplicate tags
            if ($originalProduct->tags->count() > 0) {
                $newProduct->tags()->attach($originalProduct->tags->pluck('id'));
            }

            // Duplicate images
            foreach ($originalProduct->images as $image) {
                $newImage = $image->replicate();
                $newImage->product_id = $newProduct->id;

                // Copy image file
                if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                    $extension = pathinfo($image->image_path, PATHINFO_EXTENSION);
                    $newImageName = time() . '_' . Str::random(10) . '.' . $extension;
                    $newImagePath = 'products/gallery/' . $newImageName;

                    Storage::disk('public')->copy($image->image_path, $newImagePath);
                    $newImage->image_path = $newImagePath;
                }

                $newImage->save();
            }

            // Duplicate variants
            foreach ($originalProduct->variants as $variant) {
                $newVariant = $variant->replicate();
                $newVariant->product_id = $newProduct->id;
                $newVariant->sku = 'VAR-' . strtoupper(Str::random(10));
                $newVariant->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product duplicated successfully',
                'product_id' => $newProduct->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick edit product (inline editing)
     */
    public function quickEdit(Request $request, $id)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'field' => 'required|in:name,sku,price,sale_price,stock,status_key_code',
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $field = $request->field;
            $value = $request->value;

            $product->update([
                $field => $value,
                'updated_by' => auth('admin')->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product statistics
     */
    public function statistics(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Product::query();

        // Apply filters if provided
        if ($request->filled('is_featured')) {
            $query->where('is_featured', $request->is_featured);
        }

        if ($request->filled('status')) {
            $query->where('status_key_code', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Low stock filter
        if ($request->filled('stock_status') && $request->stock_status === 'low_stock') {
            $query->where('track_inventory', true)
                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                ->where('stock_quantity', '>', 0);
        }

        $stats = [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('status_key_code', 'PRODUCT_ACTIVE')->count(),
            'draft' => (clone $query)->where('status_key_code', 'PRODUCT_DRAFT')->count(),
            'featured' => (clone $query)->where('is_featured', true)->count(),
            'in_stock' => (clone $query)->where(function($q) {
                $q->where('track_inventory', false)
                ->orWhere('stock_quantity', '>', 0);
            })->count(),
            'out_of_stock' => (clone $query)->where('track_inventory', true)
                                    ->where('stock_quantity', '<=', 0)
                                    ->count(),
            'low_stock' => (clone $query)->where('track_inventory', true)
                                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                                ->where('stock_quantity', '>', 0)
                                ->count(),

            // Additional stats for low stock view
            'critical' => (clone $query)->where('track_inventory', true)
                                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                                ->where('stock_quantity', '<=', 5)
                                ->where('stock_quantity', '>', 0)
                                ->count(),
            'average_stock' => round((clone $query)->avg('stock_quantity'), 0),

            'by_type' => [
                'simple' => (clone $query)->where('product_type', 'simple')->count(),
                'variable' => (clone $query)->where('product_type', 'variable')->count(),
                'grouped' => (clone $query)->where('product_type', 'grouped')->count(),
                'external' => (clone $query)->where('product_type', 'external')->count(),
            ],
            'total_value' => round((clone $query)->sum(\DB::raw('price * stock_quantity')), 2),
            'average_price' => round((clone $query)->avg('price'), 2),
        ];

        return response()->json($stats);
    }

    /**
     * Bulk delete products
     */
    public function bulkDelete(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $products = Product::whereIn('id', $request->product_ids)->get();

            foreach ($products as $product) {
                // Delete main image
                if ($product->main_image && Storage::disk('public')->exists($product->main_image)) {
                    Storage::disk('public')->delete($product->main_image);
                }

                // Delete all product images
                foreach ($product->images as $image) {
                    if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                        Storage::disk('public')->delete($image->image_path);
                    }
                    $image->delete();
                }

                // Delete product
                $product->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($request->product_ids) . ' products deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import products from CSV
     */
    public function import(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.create')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            $headers = array_shift($csvData);

            $imported = 0;
            $failed = 0;
            $errors = [];

            foreach ($csvData as $row) {
                try {
                    $data = array_combine($headers, $row);

                    // Find category by name
                    $category = null;
                    if (!empty($data['Category'])) {
                        $category = ProductsCategories::where('title', $data['Category'])->first();
                    }

                    Product::create([
                        'name' => $data['Name'],
                        'sku' => $data['SKU'],
                        'barcode' => $data['Barcode'] ?? null,
                        'category_id' => $category ? $category->id : null,
                        'product_type' => $data['Type'] ?? 'simple',
                        'price' => $data['Price'],
                        'sale_price' => $data['Sale Price'] ?? null,
                        'short_description' => $data['Short Description'] ?? null,
                        'description' => $data['Description'] ?? null,
                        'status_key_code' => 'PRODUCT_DRAFT',
                        'created_by' => auth('admin')->id(),
                    ]);

                    $imported++;

                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = 'Row ' . ($imported + $failed + 1) . ': ' . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Import completed. Imported: {$imported}, Failed: {$failed}",
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to import products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update product sort order
     */
    public function updateSortOrder(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            foreach ($request->products as $productData) {
                Product::where('id', $productData['id'])->update([
                    'sort_order' => $productData['sort_order'],
                    'updated_by' => auth('admin')->id()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sort order updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sort order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle product feature status
     */
    public function toggleFeatured($id)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $product = Product::findOrFail($id);
            $product->is_featured = !$product->is_featured;
            $product->updated_by = auth('admin')->id();
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Featured status updated',
                'is_featured' => $product->is_featured
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle featured status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle product homepage visibility
     */
    public function toggleHomepage($id)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $product = Product::findOrFail($id);
            $product->show_on_home = !$product->show_on_home;
            $product->updated_by = auth('admin')->id();
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Homepage visibility updated',
                'show_on_home' => $product->show_on_home
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle homepage visibility: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product by SKU (for quick lookup)
     */
    public function getBySku(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'sku' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::where('sku', $request->sku)->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'stock' => $product->getTotalStock(),
                'image' => $product->getMainImageUrl(),
            ]
        ]);
    }

    /**
     * Get product variants
     */
    public function getVariants($id)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $product = Product::with('variants')->findOrFail($id);

        return response()->json([
            'success' => true,
            'variants' => $product->variants->map(function($variant) {
                return [
                    'id' => $variant->id,
                    'name' => $variant->getFullName(),
                    'sku' => $variant->sku,
                    'price' => $variant->price,
                    'sale_price' => $variant->sale_price,
                    'stock' => $variant->getTotalStock(),
                    'is_default' => $variant->is_default,
                    'status' => $variant->status_key_code,
                ];
            })
        ]);
    }

    /**
     * Update product published status
     */
    public function togglePublish($id)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $product = Product::findOrFail($id);

            if ($product->published_at) {
                $product->published_at = null;
                $message = 'Product unpublished';
            } else {
                $product->published_at = now();
                $message = 'Product published';
            }

            $product->updated_by = auth('admin')->id();
            $product->save();

            return response()->json([
                'success' => true,
                'message' => $message,
                'published_at' => $product->published_at
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle publish status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show featured products
     */
    public function featured()
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.read')) {
            abort(403, 'Unauthorized access');
        }

        // Get status list for filters
        $statusList = SystemStatus::where('module', 'products')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get categories for filters
        $categories = ProductsCategories::active()
            ->roots()
            ->ordered()
            ->get();

        // Get all categories (flat list with indentation)
        $allCategories = ProductsCategories::getFlatList();

        // Get vendors for filters
        $vendors = Vendor::active()->ordered()->get();

        // Get tags for filters
        $tags = ProductTag::active()->ordered()->get();

        return view('admin.products.featured', compact(
            'statusList',
            'categories',
            'allCategories',
            'vendors',
            'tags'
        ));
    }

    /**
     * Show low stock products
     */
    public function lowStock()
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.read')) {
            abort(403, 'Unauthorized access');
        }

        // Get status list for filters
        $statusList = SystemStatus::where('module', 'products')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get categories for filters
        $categories = ProductsCategories::active()
            ->roots()
            ->ordered()
            ->get();

        // Get all categories (flat list with indentation)
        $allCategories = ProductsCategories::getFlatList();

        // Get vendors for filters
        $vendors = Vendor::active()->ordered()->get();

        // Get tags for filters
        $tags = ProductTag::active()->ordered()->get();

        return view('admin.products.low-stock', compact(
            'statusList',
            'categories',
            'allCategories',
            'vendors',
            'tags'
        ));
    }

    /**
     * Show inactive products
     */
    public function inactive()
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.read')) {
            abort(403, 'Unauthorized access');
        }

        // Get status list for filters
        $statusList = SystemStatus::where('module', 'products')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get categories for filters
        $categories = ProductsCategories::active()
            ->roots()
            ->ordered()
            ->get();

        // Get all categories (flat list with indentation)
        $allCategories = ProductsCategories::getFlatList();

        // Get vendors for filters
        $vendors = Vendor::active()->ordered()->get();

        // Get tags for filters
        $tags = ProductTag::active()->ordered()->get();

        return view('admin.products.inactive', compact(
            'statusList',
            'categories',
            'allCategories',
            'vendors',
            'tags'
        ));
    }

    /**
     * Bulk toggle featured status
     */
    public function bulkToggleFeatured(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'is_featured' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            Product::whereIn('id', $request->product_ids)
                ->update([
                    'is_featured' => $request->is_featured,
                    'updated_by' => auth('admin')->id()
                ]);

            $message = $request->is_featured
                ? 'Products marked as featured successfully'
                : 'Featured status removed successfully';

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update featured status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique session ID for image uploads
     */
    public function generateSessionId()
    {
        $sessionId = (string) Str::uuid();
        return response()->json([
            'success' => true,
            'session_id' => $sessionId
        ]);
    }

    /**
     * Upload images before form submission
     */
    public function uploadTempImages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $sessionId = $request->session_id;
        $uploadedImages = [];

        try {
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imageName = time() . '_' . Str::random(10) . '.' . $image->extension();
                    $imagePath = $image->storeAs('products/temp/' . $sessionId, $imageName, 'public');

                    $uploadedImages[] = [
                        'path' => $imagePath,
                        'url' => asset('storage/' . $imagePath),
                        'name' => $image->getClientOriginalName(),
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'images' => $uploadedImages
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload images: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete temp image before form submission
     */
    public function deleteTempImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image_path' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $imagePath = $request->image_path;

            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move temp images to product folder and create records
     */
    protected function moveTempImages($sessionId, $productId)
    {
        $tempPath = 'products/temp/' . $sessionId;

        if (!Storage::disk('public')->exists($tempPath)) {
            return;
        }

        $files = Storage::disk('public')->files($tempPath);
        $sortOrder = 0;

        foreach ($files as $file) {
            $fileName = basename($file);
            $newPath = 'products/gallery/' . $fileName;

            // Move file
            Storage::disk('public')->move($file, $newPath);

            // Create product image record
            ProductImage::create([
                'product_id' => $productId,
                'image_path' => $newPath,
                'image_name' => $fileName,
                'sort_order' => $sortOrder++,
                'is_primary' => $sortOrder === 1,
            ]);
        }

        // Delete temp directory
        Storage::disk('public')->deleteDirectory($tempPath);
    }

    /**
     * Quick add category (AJAX)
     */
    public function quickAddCategory(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('categories.create')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:products_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $category = ProductsCategories::create([
                'title' => $request->title,
                'parent_id' => $request->parent_id,
                'status_key_code' => 'CATEGORY_ACTIVE',
                'created_by' => auth('admin')->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'category' => [
                    'id' => $category->id,
                    'title' => $category->title,
                    'full_path' => $category->full_path,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick add vendor (AJAX)
     */
    public function quickAddVendor(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('vendors.create')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $vendor = Vendor::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'status_key_code' => 'VENDOR_ACTIVE',
                'created_by' => auth('admin')->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vendor created successfully',
                'vendor' => [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create vendor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search tags (AJAX for autocomplete)
     */
    public function searchTags(Request $request)
    {
        $search = $request->get('q', '');

        $tags = ProductTag::active()
            ->where('name', 'like', "%{$search}%")
            ->limit(10)
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'tags' => $tags
        ]);
    }

    /**
     * Create tag on the fly (AJAX)
     */
    public function createTag(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('tags.create')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if tag already exists
            $existingTag = ProductTag::where('name', $request->name)->first();

            if ($existingTag) {
                return response()->json([
                    'success' => true,
                    'tag' => [
                        'id' => $existingTag->id,
                        'name' => $existingTag->name,
                    ],
                    'message' => 'Tag already exists'
                ]);
            }

            $tag = ProductTag::create([
                'name' => $request->name,
                'status_key_code' => 'TAG_ACTIVE',
                'created_by' => auth('admin')->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tag created successfully',
                'tag' => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tag: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk stock update
     */
    public function bulkStockUpdate(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'action_type' => 'required|in:set,add,reduce',
            'quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $products = Product::whereIn('id', $request->product_ids)->get();

            foreach ($products as $product) {
                switch ($request->action_type) {
                    case 'set':
                        $product->setStock($request->quantity);
                        break;
                    case 'add':
                        $product->addStock($request->quantity);
                        break;
                    case 'reduce':
                        $product->reduceStock($request->quantity);
                        break;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully for ' . count($products) . ' products'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate single product
     */
    public function activate(Request $request, $id)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $product = Product::findOrFail($id);

            $product->update([
                'status_key_code' => 'PRODUCT_ACTIVE',
                'updated_by' => auth('admin')->id()
            ]);

            // Optionally publish the product
            if ($request->publish) {
                $product->update(['published_at' => now()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product activated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk activate products
     */
    public function bulkActivate(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            Product::whereIn('id', $request->product_ids)
                ->update([
                    'status_key_code' => 'PRODUCT_ACTIVE',
                    'updated_by' => auth('admin')->id()
                ]);

            return response()->json([
                'success' => true,
                'message' => count($request->product_ids) . ' products activated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick stock update from index page
     */
    public function quickStockUpdate(Request $request, $id)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'action_type' => 'required|in:set,add,reduce',
            'quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $product = Product::findOrFail($id);
            $quantity = $request->quantity;

            // Update stock based on action type
            switch ($request->action_type) {
                case 'set':
                    $product->setStock($quantity);
                    $message = 'Stock set to ' . $quantity . ' units';
                    break;
                case 'add':
                    $product->addStock($quantity);
                    $message = 'Added ' . $quantity . ' units to stock';
                    break;
                case 'reduce':
                    $product->reduceStock($quantity);
                    $message = 'Reduced stock by ' . $quantity . ' units';
                    break;
            }

            // Update threshold if provided
            if ($request->filled('low_stock_threshold')) {
                $product->update(['low_stock_threshold' => $request->low_stock_threshold]);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'new_stock' => $product->fresh()->stock_quantity
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update product URL/slug with optional redirect
     */
    public function updateUrl(Request $request, $id)
    {
        if (!auth('admin')->user()->hasPermission('products.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'slug' => 'required|string|max:255|unique:products,slug,' . $id,
            'create_redirect' => 'nullable|in:0,1,true,false',
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
            $product = Product::findOrFail($id);
            $oldSlug = $product->slug;
            $newSlug = $request->slug;

            // Update product slug
            $product->slug = $newSlug;
            $product->updated_by = auth('admin')->id();
            $product->save();

            // Create redirect if requested
            if ($request->create_redirect && $oldSlug !== $newSlug) {
                UrlRedirect::createProductRedirect($oldSlug, $newSlug, $product->id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product URL updated successfully' . ($request->create_redirect ? ' with redirect' : ''),
                'old_slug' => $oldSlug,
                'new_slug' => $newSlug
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update URL: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get feature template for AJAX (optional helper endpoint)
     */
    public function getFeatureTemplate(Request $request)
    {
        $type = $request->get('type');

        if (!in_array($type, ['rich_text', 'single_line', 'multiline_text', 'links_list'])) {
            return response()->json(['error' => 'Invalid feature type'], 400);
        }

        $templates = [
            'rich_text' => [
                'name' => 'Rich Text Editor',
                'icon' => 'bi-file-richtext',
                'description' => 'Full-featured text editor with formatting'
            ],
            'single_line' => [
                'name' => 'Single Line Text',
                'icon' => 'bi-input-cursor-text',
                'description' => 'Simple text input for short values'
            ],
            'multiline_text' => [
                'name' => 'Multiline Text',
                'icon' => 'bi-textarea-t',
                'description' => 'Textarea for longer text content'
            ],
            'links_list' => [
                'name' => 'Links List',
                'icon' => 'bi-link-45deg',
                'description' => 'Add multiple links with titles'
            ]
        ];

        return response()->json([
            'success' => true,
            'template' => $templates[$type] ?? null
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
// Models
use App\Models\Product;
use App\Models\ProductsCategories;
use App\Models\ProductTag;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\UrlRedirect;
use App\Models\SystemStatus;
use App\Models\Vendor;
use App\Models\ProductWarehouseStock;
use App\Models\Warehouse;
use App\Models\InventoryMovement;
use App\Models\StockAlert;
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
        ])->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            // dd($request->status);
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

        // STOCK STATUS FILTER (IN_STOCK, OUT_OF_STOCK, LOW_STOCK)
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'in_stock') {
                $query->where(function($q) {
                    $q->where(function($subQ) {
                        $subQ->where('has_variants', false)
                            ->where('track_inventory', true)
                            ->where('stock_quantity', '>', 0);
                    })
                    ->orWhere(function($subQ) {
                        $subQ->where('has_variants', false)
                            ->where('track_inventory', false);
                    })
                    ->orWhere(function($subQ) {
                        $subQ->where('has_variants', true)
                            ->whereHas('variants', function($varQ) {
                                $varQ->where('stock_quantity', '>', 0);
                            });
                    });
                });
            }
            elseif ($request->stock_status === 'out_of_stock') {
                $query->where(function($q) {
                    $q->where(function($subQ) {
                        $subQ->where('has_variants', false)
                            ->where('track_inventory', true)
                            ->where('stock_quantity', '<=', 0);
                    })
                    ->orWhere(function($subQ) {
                        $subQ->where('has_variants', true)
                            ->whereHas('variants')
                            ->whereDoesntHave('variants', function($varQ) {
                                $varQ->where('stock_quantity', '>', 0);
                            });
                    })
                    ->orWhere(function($subQ) {
                        $subQ->where('has_variants', true)
                            ->whereDoesntHave('variants');
                    });
                });
            }
            elseif ($request->stock_status === 'low_stock') {
                $query->where(function($q) {
                    $q->where(function($subQ) {
                        $subQ->where('has_variants', false)
                            ->where('track_inventory', true)
                            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                            ->where('stock_quantity', '>', 0);
                    })
                    ->orWhere(function($subQ) {
                        $subQ->where('has_variants', true)
                            ->whereHas('variants', function($varQ) {
                                $varQ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                                    ->where('stock_quantity', '>', 0);
                            });
                    });
                });
            }
        }

        // STOCK LEVEL FILTER (CRITICAL, VERY_LOW, LOW)
        if ($request->filled('stock_level')) {
            if ($request->stock_level === 'critical') {
                $query->where(function($q) {
                    $q->where(function($subQ) {
                        $subQ->where('has_variants', false)
                            ->where('track_inventory', true)
                            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                            ->whereBetween('stock_quantity', [1, 5]);
                    })
                    ->orWhere(function($subQ) {
                        $subQ->where('has_variants', true)
                            ->whereHas('variants', function($varQ) {
                                $varQ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                                    ->whereBetween('stock_quantity', [1, 5]);
                            });
                    });
                });
            }
            elseif ($request->stock_level === 'very_low') {
                $query->where(function($q) {
                    $q->where(function($subQ) {
                        $subQ->where('has_variants', false)
                            ->where('track_inventory', true)
                            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                            ->whereBetween('stock_quantity', [6, 10]);
                    })
                    ->orWhere(function($subQ) {
                        $subQ->where('has_variants', true)
                            ->whereHas('variants', function($varQ) {
                                $varQ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                                    ->whereBetween('stock_quantity', [6, 10]);
                            });
                    });
                });
            }
            elseif ($request->stock_level === 'low') {
                $query->where(function($q) {
                    $q->where(function($subQ) {
                        $subQ->where('has_variants', false)
                            ->where('track_inventory', true)
                            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                            ->whereBetween('stock_quantity', [11, 20]);
                    })
                    ->orWhere(function($subQ) {
                        $subQ->where('has_variants', true)
                            ->whereHas('variants', function($varQ) {
                                $varQ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                                    ->whereBetween('stock_quantity', [11, 20]);
                            });
                    });
                });
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
                            onclick="viewProductImage(\'' . $imageUrl . '\', \'' . htmlspecialchars($product->name, ENT_QUOTES) . '\')">';
            })
            ->addColumn('name_link', function($product) {
                $editUrl = route('admin.products.edit', $product->id);
                $viewUrl = route('admin.products.show', $product->id);

                $html = '<div class="product-info">';

                // Product name with edit link
                $html .= '<div class="product-title">';
                $html .= '<strong><a href="' . $editUrl . '" class="text-decoration-none product-link">'
                     . htmlspecialchars($product->name) . '</a></strong>';
                if ($product->has_variants && $product->variants()->count() > 0) {
                    $html .= ' <button type="button" class="btn btn-xs btn-outline-primary view-variants-btn"
                                data-id="' . $product->id . '"
                                data-name="' . htmlspecialchars($product->name) . '"
                                title="View Variants">
                                <i class="bi bi-grid-3x3-gap"></i> ' . $product->variants()->count() . ' variants
                            </button>';
                }
                if ($product->is_featured) {
                    $html .= ' <i class="bi bi-star-fill text-warning" title="Featured Product"></i>';
                }

                $html .= '</div>';

                // Product details
                $html .= '<div class="product-details">';
                $html .= '<span class="text-muted me-2">SKU: ' . htmlspecialchars($product->sku) . '</span>';

                if ($product->barcode) {
                    $html .= '<span class="text-muted">| Barcode: ' . htmlspecialchars($product->barcode) . '</span>';
                }
                $html .= '</div>';

                // Creation date with icon
                $html .= '<div class="product-meta">';
                $html .= '<small class="text-muted"><i class="bi bi-calendar3"></i> Created on: '
                     . $product->created_at->format('M d, Y h:i:s A') . '</small>';
                $html .= '</div>';

                $html .= '</div>';

                return $html;
            })
            ->addColumn('category_name', function($product) {
                if (!$product->category) {
                    return '<span class="text-muted"><i class="bi bi-dash-circle"></i> Uncategorized</span>';
                }

                return '<span class="category-badge">'
                     . '<i class="bi bi-folder"></i> '
                     . htmlspecialchars($product->category->title)
                     . '</span>';
            })
            ->addColumn('price_display', function($product) {
                $html = '<div class="price-container">';

                // ✅ IMPROVED: Handle variant products with price range
                if ($product->has_variants) {
                    $variants = $product->variants()->active()->get();

                    if ($variants->count() > 0) {
                        // Get min and max prices considering sale prices
                        $prices = $variants->map(function($variant) {
                            return $variant->getFinalPrice();
                        });

                        $minPrice = $prices->min();
                        $maxPrice = $prices->max();

                        // Get default variant if exists
                        $defaultVariant = $variants->where('is_default', true)->first();

                        if ($minPrice == $maxPrice) {
                            // All variants same price
                            $html .= '<strong>' . format_store_price($minPrice) . '</strong>';

                            // Show if on sale
                            $anyOnSale = $variants->filter(function($v) {
                                return $v->isOnSale();
                            })->count();

                            if ($anyOnSale > 0) {
                                $html .= ' <span class="badge bg-danger">Sale</span>';
                            }
                        } else {
                            // Price range
                            $html .= '<div class="price-range">';
                            $html .= '<strong>' . format_store_price($minPrice) . '</strong>';
                            $html .= ' <span class="text-muted">-</span> ';
                            $html .= '<strong>' . format_store_price($maxPrice) . '</strong>';
                            $html .= '</div>';

                            // Show default variant price if available
                            if ($defaultVariant) {
                                $html .= '<div class="default-price" style="font-size: 0.75rem;">';
                                $html .= '<span class="text-muted">Default: ' . $defaultVariant->getFormattedFinalPrice() . '</span>';
                                $html .= '</div>';
                            }
                        }

                        // Add hover tooltip with all variant prices
                        $tooltipContent = '<div class="text-start">';
                        foreach ($variants->sortBy('price') as $variant) {
                            $tooltipContent .= '<div class="mb-1">';
                            $tooltipContent .= '<strong>' . htmlspecialchars($variant->variant_value) . ':</strong> ';

                            if ($variant->isOnSale()) {
                                $tooltipContent .= '<span class="text-decoration-line-through">' . format_store_price($variant->price) . '</span> ';
                                $tooltipContent .= '<span class="text-success">' . format_store_price($variant->sale_price) . '</span>';
                            } else {
                                $tooltipContent .= format_store_price($variant->price);
                            }

                            if ($variant->is_default) {
                                $tooltipContent .= ' <span class="badge bg-primary" style="font-size: 0.6rem;">Default</span>';
                            }

                            $tooltipContent .= '</div>';
                        }
                        $tooltipContent .= '</div>';

                        $html = '<div class="price-container"
                                    data-bs-toggle="tooltip"
                                    data-bs-html="true"
                                    data-bs-placement="right"
                                    title="' . htmlspecialchars($tooltipContent) . '">'
                                . $html .
                                '</div>';
                    } else {
                        $html .= '<span class="text-muted">No variants</span>';
                    }
                } else {
                    // Simple product pricing (original logic)
                    if ($product->isOnSale()) {
                        $html .= '<div class="original-price">';
                        $html .= '<span class="text-decoration-line-through text-muted">'
                            . $product->getFormattedPrice() . '</span>';
                        $html .= '</div>';
                        $html .= '<strong class="text-success">' . $product->getFormattedSalePrice() . '</strong>';
                        $html .= ' <span class="badge bg-danger">-' . $product->getDiscountPercentage() . '%</span>';
                    } else {
                        $html .= '<strong>' . $product->getFormattedPrice() . '</strong>';
                    }
                }

                $html .= '</div>';
                return $html;
            })
            ->addColumn('stock_badge', function($product) {
                $canUpdate = auth('admin')->user()->hasPermission('products.update');

                if (!$product->track_inventory) {
                    $badge = '<span class="badge bg-info">No Tracking</span>';
                } else {
                    // ✅ IMPROVED: Handle variant products with detailed info
                    if ($product->has_variants) {
                        $variants = $product->variants;
                        $variantCount = $variants->count();

                        if ($variantCount === 0) {
                            $badge = '<span class="badge bg-secondary">No Variants</span>';
                        } else {
                            $totalStock = $variants->sum('stock_quantity');
                            $inStockVariants = $variants->where('stock_quantity', '>', 0)->count();
                            $outOfStockVariants = $variants->where('stock_quantity', '<=', 0)->count();
                            $lowStockVariants = $variants->filter(function($v) {
                                return $v->stock_quantity > 0 && $v->stock_quantity <= $v->low_stock_threshold;
                            })->count();

                            // Build detailed tooltip
                            $tooltip = 'Total Stock: ' . $totalStock . ' units<br>';
                            $tooltip .= 'In Stock: ' . $inStockVariants . ' variants<br>';
                            if ($lowStockVariants > 0) {
                                $tooltip .= 'Low Stock: ' . $lowStockVariants . ' variants<br>';
                            }
                            if ($outOfStockVariants > 0) {
                                $tooltip .= 'Out of Stock: ' . $outOfStockVariants . ' variants';
                            }

                            // Determine badge based on priority
                            if ($outOfStockVariants == $variantCount) {
                                // All variants out of stock
                                $badge = '<span class="badge bg-danger"
                                                data-bs-toggle="tooltip"
                                                data-bs-html="true"
                                                title="' . htmlspecialchars($tooltip) . '">
                                            All Out (0/' . $variantCount . ')
                                        </span>';
                            } elseif ($outOfStockVariants > 0) {
                                // Some variants out of stock
                                $badge = '<span class="badge bg-warning"
                                                data-bs-toggle="tooltip"
                                                data-bs-html="true"
                                                title="' . htmlspecialchars($tooltip) . '">
                                            ' . $inStockVariants . '/' . $variantCount . ' In Stock
                                            <small class="d-block" style="font-size: 0.65rem;">Total: ' . $totalStock . '</small>
                                        </span>';
                            } elseif ($lowStockVariants > 0) {
                                // Some variants low on stock
                                $badge = '<span class="badge bg-warning"
                                                data-bs-toggle="tooltip"
                                                data-bs-html="true"
                                                title="' . htmlspecialchars($tooltip) . '">
                                            ⚠ ' . $lowStockVariants . ' Low Stock
                                            <small class="d-block" style="font-size: 0.65rem;">Total: ' . $totalStock . '</small>
                                        </span>';
                            } else {
                                // All variants in good stock
                                $badge = '<span class="badge bg-success"
                                                data-bs-toggle="tooltip"
                                                data-bs-html="true"
                                                title="' . htmlspecialchars($tooltip) . '">
                                            ✓ All In Stock
                                            <small class="d-block" style="font-size: 0.65rem;">Total: ' . $totalStock . '</small>
                                        </span>';
                            }
                        }
                    } else {
                        // Simple product logic
                        $stock = $product->stock_quantity;

                        if ($stock <= 0) {
                            $badge = '<span class="badge bg-danger">Out of Stock</span>';
                        } elseif ($stock <= $product->low_stock_threshold) {
                            $badge = '<span class="badge bg-warning">Low Stock (' . $stock . ')</span>';
                        } else {
                            $badge = '<span class="badge bg-success">In Stock (' . $stock . ')</span>';
                        }
                    }
                }

                // Manage stock button
                if ($canUpdate && $product->track_inventory && !$product->has_variants) {
                    return '
                        <div class="stock-badge-container">
                            ' . $badge . '
                            <button type="button" class="btn btn-sm btn-link p-0 ms-1 quick-stock-btn"
                                data-id="' . $product->id . '"
                                data-name="' . htmlspecialchars($product->name, ENT_QUOTES) . '"
                                data-stock="' . $product->stock_quantity . '"
                                data-threshold="' . ($product->low_stock_threshold ?? 10) . '"
                                data-has-variants="' . ($product->has_variants ? '1' : '0') . '"
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
     * Get variants for a product (AJAX)
     */
    public function getModalVariants(Request $request, $productId)
    {
        if (!auth('admin')->user()->hasPermission('products.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $product = Product::with(['variants.status', 'variants.warehouseStock'])
            ->findOrFail($productId);

        $variants = $product->variants()->ordered()->get()->map(function($variant) {
            return [
                'id' => $variant->id,
                'name' => $variant->getFullName(),
                'sku' => $variant->sku,
                'price' => $variant->getFormattedFinalPrice(),
                'stock' => $variant->stock_quantity,
                'stock_badge' => $variant->getStockBadge(),
                'status_badge' => $variant->getStatusBadge(),
                'image' => $variant->getImageUrl(),
                'is_default' => $variant->is_default,
            ];
        });

        return response()->json([
            'success' => true,
            'product_name' => $product->name,
            'variants' => $variants
        ]);
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
            'low_stock_threshold' => 'nullable|integer|min:0',
            'session_id' => 'nullable|string',
            // 'is_taxable' => 'nullable|boolean',
            'tax_type' => 'nullable|in:inclusive,exclusive',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_class' => 'nullable|string|max:100',
            'features' => 'nullable|json',
            //
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'has_variants' => 'sometimes|accepted',
        ], [
            // Name validation messages
            'name.required' => 'Product name is required',
            'name.string' => 'Product name must be a valid text',
            'name.max' => 'Product name cannot exceed 255 characters',

            // Slug validation messages
            'slug.string' => 'Slug must be a valid text',
            'slug.max' => 'Slug cannot exceed 255 characters',
            'slug.unique' => 'This slug is already taken by another product',

            // SKU validation messages
            'sku.required' => 'SKU is required',
            'sku.string' => 'SKU must be a valid text',
            'sku.max' => 'SKU cannot exceed 255 characters',
            'sku.unique' => 'This SKU is already in use',

            // Barcode validation messages
            'barcode.string' => 'Barcode must be a valid text',
            'barcode.max' => 'Barcode cannot exceed 255 characters',
            'barcode.unique' => 'This barcode is already in use',

            // Category validation messages
            'category_id.exists' => 'Selected category does not exist',

            // Vendor validation messages
            'vendor_id.exists' => 'Selected vendor does not exist',

            // Product type validation messages
            'product_type.string' => 'Product type must be a valid text',
            'product_type.max' => 'Product type cannot exceed 100 characters',

            // Price validation messages
            'price.required' => 'Product price is required',
            'price.numeric' => 'Price must be a valid number',
            'price.min' => 'Price cannot be negative',

            // Sale price validation messages
            'sale_price.numeric' => 'Sale price must be a valid number',
            'sale_price.min' => 'Sale price cannot be negative',
            'sale_price.lt' => 'Sale price must be less than regular price',

            // Cost price validation messages
            'cost_price.numeric' => 'Cost price must be a valid number',
            'cost_price.min' => 'Cost price cannot be negative',

            // Description validation messages
            'short_description.string' => 'Short description must be valid text',
            'description.string' => 'Description must be valid text',

            // Status validation messages
            'status_key_code.required' => 'Product status is required',
            'status_key_code.string' => 'Status must be a valid text',

            // Image validation messages
            'main_image.image' => 'Main image must be a valid image file',
            'main_image.mimes' => 'Main image must be jpeg, png, jpg, gif, or webp format',
            'main_image.max' => 'Main image size cannot exceed 2MB',

            // Stock threshold validation messages
            'low_stock_threshold.integer' => 'Low stock threshold must be a whole number',
            'low_stock_threshold.min' => 'Low stock threshold cannot be negative',

            // Session validation messages
            'session_id.string' => 'Session ID must be valid text',

            // Tax validation messages
            // 'is_taxable.boolean' => 'Taxable field must be true or false',
            'tax_type.in' => 'Tax type must be either inclusive or exclusive',
            'tax_percentage.numeric' => 'Tax percentage must be a valid number',
            'tax_percentage.min' => 'Tax percentage cannot be negative',
            'tax_percentage.max' => 'Tax percentage cannot exceed 100',
            'tax_class.string' => 'Tax class must be valid text',
            'tax_class.max' => 'Tax class cannot exceed 100 characters',

            // Features validation messages
            'features.json' => 'Features must be in valid JSON format',
            // Weight validation messages
            'weight.numeric' => 'Weight must be a valid number',
            'weight.min' => 'Weight cannot be negative',
            'length.numeric' => 'Length must be a valid number',
            'length.min' => 'Length cannot be negative',
            'width.numeric' => 'Width must be a valid number',
            'width.min' => 'Width cannot be negative',
            'height.numeric' => 'Height must be a valid number',
            'height.min' => 'Height cannot be negative',
            'has_variants.boolean' => 'Has variants field must be true or false',
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
                'low_stock_threshold',
                'meta_title', 'meta_description', 'meta_keywords', 'canonical_url',
                'is_taxable', 'tax_type', 'tax_percentage', 'tax_class',
                'weight', 'length', 'width', 'height',
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
            $productData['has_variants'] =  false;

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
                    if ($richTextCount > 100) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Only 100 Rich Text feature is allowed'
                        ], 422);
                    }

                    $productData['features'] = $features;
                }
            }

            // Create product
            $product = Product::create($productData);
            // Handle initial stock AFTER product is created
            if ($request->filled('stock_quantity') && $request->stock_quantity > 0 && $request->has('track_inventory')) {
                $product->setStock(
                    (int) $request->stock_quantity,  // quantity (positional parameter)
                    null,                             // warehouseId (positional parameter)
                    'Initial stock on product creation' // reason (positional parameter)
                );
            }

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

                    // ✅ ADD THIS LINE: Remove duplicates
                    $tags = array_unique($tags);

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
            // 'slug' => 'nullable|string|max:255|unique:products,slug,'. $id,
            'sku' => 'required|string|max:255|unique:products,sku,'. $id,
            'barcode' => 'nullable|string|max:255|unique:products,barcode,'. $id,
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
            'low_stock_threshold' => 'nullable|integer|min:0',
            'session_id' => 'nullable|string',
            // 'is_taxable' => 'sometimes|boolean',
            'tax_type' => 'nullable|in:inclusive,exclusive',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_class' => 'nullable|string|max:100',
            'features' => 'nullable|json',
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'has_variants' => 'sometimes|accepted',
        ], [
            // Name validation messages
            'name.required' => 'Product name is required',
            'name.string' => 'Product name must be a valid text',
            'name.max' => 'Product name cannot exceed 255 characters',

            // SKU validation messages
            'sku.required' => 'SKU is required',
            'sku.string' => 'SKU must be a valid text',
            'sku.max' => 'SKU cannot exceed 255 characters',
            'sku.unique' => 'This SKU is already in use',

            // Barcode validation messages
            'barcode.string' => 'Barcode must be a valid text',
            'barcode.max' => 'Barcode cannot exceed 255 characters',
            'barcode.unique' => 'This barcode is already in use',

            // Category validation messages
            'category_id.exists' => 'Selected category does not exist',

            // Vendor validation messages
            'vendor_id.exists' => 'Selected vendor does not exist',

            // Product type validation messages
            'product_type.string' => 'Product type must be a valid text',
            'product_type.max' => 'Product type cannot exceed 100 characters',

            // Price validation messages
            'price.required' => 'Product price is required',
            'price.numeric' => 'Price must be a valid number',
            'price.min' => 'Price cannot be negative',

            // Sale price validation messages
            'sale_price.numeric' => 'Sale price must be a valid number',
            'sale_price.min' => 'Sale price cannot be negative',
            'sale_price.lt' => 'Sale price must be less than regular price',

            // Cost price validation messages
            'cost_price.numeric' => 'Cost price must be a valid number',
            'cost_price.min' => 'Cost price cannot be negative',

            // Description validation messages
            'short_description.string' => 'Short description must be valid text',
            'description.string' => 'Description must be valid text',

            // Status validation messages
            'status_key_code.required' => 'Product status is required',
            'status_key_code.string' => 'Status must be a valid text',

            // Image validation messages
            'main_image.image' => 'Main image must be a valid image file',
            'main_image.mimes' => 'Main image must be jpeg, png, jpg, gif, or webp format',
            'main_image.max' => 'Main image size cannot exceed 2MB',

            // Stock threshold validation messages
            'low_stock_threshold.integer' => 'Low stock threshold must be a whole number',
            'low_stock_threshold.min' => 'Low stock threshold cannot be negative',

            // Session validation messages
            'session_id.string' => 'Session ID must be valid text',

            // Tax validation messages
            // 'is_taxable.boolean' => 'Taxable field must be true or false',
            'tax_type.in' => 'Tax type must be either inclusive or exclusive',
            'tax_percentage.numeric' => 'Tax percentage must be a valid number',
            'tax_percentage.min' => 'Tax percentage cannot be negative',
            'tax_percentage.max' => 'Tax percentage cannot exceed 100',
            'tax_class.string' => 'Tax class must be valid text',
            'tax_class.max' => 'Tax class cannot exceed 100 characters',

            // Features validation messages
            'features.json' => 'Features must be in valid JSON format',

            'weight.numeric' => 'Weight must be a valid number',
            'weight.min' => 'Weight cannot be negative',
            'length.numeric' => 'Length must be a valid number',
            'length.min' => 'Length cannot be negative',
            'width.numeric' => 'Width must be a valid number',
            'width.min' => 'Width cannot be negative',
            'height.numeric' => 'Height must be a valid number',
            'height.min' => 'Height cannot be negative',
            'has_variants.boolean' => 'Has variants field must be true or false',
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
                'is_featured', 'show_on_home', 'is_available', 'track_inventory','low_stock_threshold',
                'meta_title', 'meta_description', 'meta_keywords', 'canonical_url',
                'is_taxable', 'tax_type', 'tax_percentage', 'tax_class',
                'weight', 'length', 'width', 'height',
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
                    if ($richTextCount > 100) {
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
            //
            $variantCount = $product->variants()->count();
            if ($variantCount > 0 && !$product->has_variants) {
                $product->update(['has_variants' => true]);
            } elseif ($variantCount === 0 && $product->has_variants) {
                $product->update(['has_variants' => false]);
            }
            $product->syncHasVariantsFlag();
            // Handle stock update AFTER product is updated
            if ($request->filled('stock_quantity') && $productData['track_inventory']) {
                $newStock = (int) $request->stock_quantity;
                $currentStock = $product->fresh()->stock_quantity;

                if ($newStock != $currentStock) {
                    $product->setStock(
                        $newStock,
                        null,
                        'Stock updated from admin panel'
                    );
                }
            }
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

                    // ✅ ADD THIS LINE: Remove duplicates
                    $tags = array_unique($tags);

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
            $product->syncHasVariantsFlag();
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
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ], [
            'csv_file.required' => 'Please select a CSV file to upload',
            'csv_file.file' => 'The uploaded file is not valid',
            'csv_file.mimes' => 'Only CSV or TXT files are allowed',
            'csv_file.max' => 'File size cannot exceed 10MB',
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
            $file = $request->file('csv_file');

            // Validate file exists
            if (!$file || !$file->isValid()) {
                throw new \Exception('Invalid file upload. Please try again.');
            }

            // Check if file is readable
            if (!is_readable($file->getRealPath())) {
                throw new \Exception('Unable to read the uploaded file. Please check file permissions.');
            }

            // Read CSV with proper encoding handling
            $handle = fopen($file->getRealPath(), 'r');

            if (!$handle) {
                throw new \Exception('Failed to open CSV file. The file might be corrupted.');
            }

            // Skip BOM if present (Excel compatibility)
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            // Read headers
            $headers = fgetcsv($handle);

            // Validate headers exist
            if (!$headers || empty($headers)) {
                fclose($handle);
                throw new \Exception('CSV file is empty or has no headers. Please use the template format.');
            }

            // Normalize headers (trim whitespace and handle encoding)
            $headers = array_map(function($header) {
                return trim($header);
            }, $headers);

            // Validate required headers
            $requiredHeaders = ['Name', 'SKU', 'Price'];
            $missingHeaders = array_diff($requiredHeaders, $headers);

            if (!empty($missingHeaders)) {
                fclose($handle);
                throw new \Exception('Missing required columns: ' . implode(', ', $missingHeaders) . '. Please use the template format.');
            }

            $imported = 0;
            $failed = 0;
            $errors = [];
            $warnings = [];
            $rowNumber = 1; // Start from 1 (header is row 0)

            // Read data rows
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    // Check if row has correct number of columns
                    if (count($row) !== count($headers)) {
                        throw new \Exception('Column count mismatch. Expected ' . count($headers) . ' columns, found ' . count($row));
                    }

                    // Combine headers with row data
                    $data = array_combine($headers, $row);

                    if ($data === false) {
                        throw new \Exception('Failed to parse row data. Check for formatting issues.');
                    }

                    // Validate required fields
                    $missingFields = [];
                    if (empty($data['Name']) || trim($data['Name']) === '') {
                        $missingFields[] = 'Name';
                    }
                    if (empty($data['SKU']) || trim($data['SKU']) === '') {
                        $missingFields[] = 'SKU';
                    }
                    if (empty($data['Price']) || trim($data['Price']) === '') {
                        $missingFields[] = 'Price';
                    }

                    if (!empty($missingFields)) {
                        throw new \Exception('Missing required fields: ' . implode(', ', $missingFields));
                    }

                    // Validate price is numeric
                    $priceValue = str_replace(',', '', trim($data['Price']));
                    if (!is_numeric($priceValue) || $priceValue < 0) {
                        throw new \Exception('Invalid price format. Price must be a positive number (found: "' . $data['Price'] . '")');
                    }

                    // Validate sale price if provided
                    if (!empty($data['Sale Price'])) {
                        $salePriceValue = str_replace(',', '', trim($data['Sale Price']));
                        if (!is_numeric($salePriceValue) || $salePriceValue < 0) {
                            throw new \Exception('Invalid sale price format. Must be a positive number (found: "' . $data['Sale Price'] . '")');
                        }
                        if ($salePriceValue >= $priceValue) {
                            throw new \Exception('Sale price (' . $salePriceValue . ') must be less than regular price (' . $priceValue . ')');
                        }
                    }

                    // Validate stock quantity if provided
                    if (!empty($data['Stock Quantity'])) {
                        $stockValue = trim($data['Stock Quantity']);
                        if (!is_numeric($stockValue) || $stockValue < 0) {
                            throw new \Exception('Invalid stock quantity. Must be a positive number (found: "' . $data['Stock Quantity'] . '")');
                        }
                    }

                    // Check if SKU already exists
                    $trimmedSku = trim($data['SKU']);
                    if (Product::where('sku', $trimmedSku)->exists()) {
                        throw new \Exception('SKU "' . $trimmedSku . '" already exists in database');
                    }

                    // Check if barcode already exists (if provided)
                    if (!empty($data['Barcode'])) {
                        $trimmedBarcode = trim($data['Barcode']);
                        if (Product::where('barcode', $trimmedBarcode)->exists()) {
                            throw new \Exception('Barcode "' . $trimmedBarcode . '" already exists in database');
                        }
                    }

                    // Find category by name
                    $category = null;
                    if (!empty($data['Category'])) {
                        $categoryName = trim($data['Category']);
                        $category = ProductsCategories::where('title', $categoryName)->first();

                        if (!$category) {
                            $warnings[] = "Row {$rowNumber}: Category '{$categoryName}' not found. Product created without category.";
                        }
                    }

                    // Create product
                    $product = Product::create([
                        'name' => trim($data['Name']),
                        'slug' => Str::slug($data['Name']),
                        'sku' => $trimmedSku,
                        'barcode' => !empty($data['Barcode']) ? trim($data['Barcode']) : null,
                        'category_id' => $category ? $category->id : null,
                        'product_type' => 'simple',
                        'price' => (float) $priceValue,
                        'sale_price' => !empty($data['Sale Price']) ? (float) str_replace(',', '', $data['Sale Price']) : null,
                        'stock_quantity' => !empty($data['Stock Quantity']) ? (int) $data['Stock Quantity'] : 0,
                        'description' => !empty($data['Description']) ? trim($data['Description']) : null,
                        'status_key_code' => 'PRODUCT_DRAFT',
                        'track_inventory' => false,
                        'is_available' => true,
                        'is_taxable'=> false,
                        'low_stock_threshold' => 10,
                        'created_by' => auth('admin')->id(),
                    ]);

                    $imported++;

                } catch (\Exception $e) {
                    $failed++;
                    $errorMessage = $e->getMessage();

                    // Make error messages more user-friendly
                    if (strpos($errorMessage, 'SQLSTATE') !== false) {
                        if (strpos($errorMessage, 'Duplicate entry') !== false) {
                            $errorMessage = 'Duplicate entry detected in database';
                        } else {
                            $errorMessage = 'Database error: ' . $errorMessage;
                        }
                    }

                    $errors[] = "Row {$rowNumber}: " . $errorMessage;
                }
            }

            fclose($handle);

            // Check if any products were imported
            if ($imported === 0 && $failed === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No valid data found in CSV file. Please check the file format and try again.'
                ], 400);
            }

            DB::commit();

            $message = "Import completed successfully!";
            if ($imported > 0) {
                $message .= " {$imported} product(s) imported.";
            }
            if ($failed > 0) {
                $message .= " {$failed} product(s) failed.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors,
                'warnings' => $warnings,
                'total_processed' => $imported + $failed
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error for debugging
            \Log::error('Product import failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return user-friendly error message
            $errorMessage = $e->getMessage();

            // Check for common error patterns and provide helpful messages
            if (strpos($errorMessage, 'memory') !== false) {
                $errorMessage = 'File is too large to process. Please split into smaller files and try again.';
            } elseif (strpos($errorMessage, 'disk') !== false || strpos($errorMessage, 'storage') !== false) {
                $errorMessage = 'Server storage issue. Please contact administrator.';
            } elseif (strpos($errorMessage, 'permission') !== false) {
                $errorMessage = 'File permission error. Please contact administrator.';
            }

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $errorMessage,
                'technical_details' => config('app.debug') ? $e->getTraceAsString() : null
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
            $tagName = trim($request->name);
            $slug = Str::slug($tagName);

            // Check if tag already exists by slug
            $existingTag = ProductTag::where('slug', $slug)->first();

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
                'name' => $tagName,
                'slug' => $slug,
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
     * Quick stock update from index page with warehouse support
     */
    public function quickStockUpdate(Request $request, $id)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required|exists:warehouses,id',
            'action_type' => 'required|in:set,add,reduce',
            'quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
        ], [
            'warehouse_id.required' => 'Please select a warehouse',
            'warehouse_id.exists' => 'Selected warehouse does not exist',
            'action_type.required' => 'Action type is required',
            'action_type.in' => 'Invalid action type',
            'quantity.required' => 'Quantity is required',
            'quantity.integer' => 'Quantity must be a number',
            'quantity.min' => 'Quantity cannot be negative',
            'low_stock_threshold.integer' => 'Threshold must be a number',
            'low_stock_threshold.min' => 'Threshold cannot be negative',
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

            // Check if product has variants
            if ($product->has_variants) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update stock for products with variants. Please manage stock at variant level.'
                ], 422);
            }

            $warehouseId = $request->warehouse_id;
            $actionType = $request->action_type;
            $quantity = (int) $request->quantity;

            // Get warehouse info
            $warehouse = Warehouse::findOrFail($warehouseId);

            // Get or create warehouse stock record
            $warehouseStock = ProductWarehouseStock::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouseId,
                    'variant_id' => null,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                ]
            );

            $previousQuantity = $warehouseStock->quantity;
            $newQuantity = 0;
            $reason = '';
            $message = '';

            // Calculate new quantity based on action type
            switch ($actionType) {
                case 'set':
                    $newQuantity = $quantity;
                    $reason = "Stock set to {$quantity} units via quick stock management";
                    $message = "Stock in {$warehouse->name} set to {$quantity} units";
                    break;

                case 'add':
                    $newQuantity = $previousQuantity + $quantity;
                    $reason = "Added {$quantity} units via quick stock management";
                    $message = "Added {$quantity} units to {$warehouse->name}";
                    break;

                case 'reduce':
                    if ($quantity > $warehouseStock->available_quantity) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Cannot reduce by {$quantity} units. Only {$warehouseStock->available_quantity} units available (excluding reserved stock)."
                        ], 422);
                    }
                    $newQuantity = max(0, $previousQuantity - $quantity);
                    $reason = "Reduced {$quantity} units via quick stock management";
                    $message = "Reduced {$quantity} units from {$warehouse->name}";
                    break;
            }

            // Update warehouse stock
            $warehouseStock->update([
                'quantity' => $newQuantity,
                'available_quantity' => $newQuantity - $warehouseStock->reserved_quantity,
            ]);

            // Create inventory movement record
            InventoryMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'type' => 'adjustment',
                'quantity' => $newQuantity - $previousQuantity,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $newQuantity,
                'reason' => $reason,
                'created_by' => auth('admin')->id(),
            ]);

            // Update product total stock (syncs from all warehouses)
            $product->updateTotalStock();

            // Update low stock threshold if provided
            if ($request->filled('low_stock_threshold')) {
                $product->update(['low_stock_threshold' => $request->low_stock_threshold]);
            }

            // Refresh product to get updated stock
            $product = $product->fresh();
            $totalStock = $product->getTotalWarehouseStock();

            // Check and create stock alerts if needed
            if ($newQuantity <= 0) {
                StockAlert::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouseId,
                        'alert_type' => 'out_of_stock',
                        'is_resolved' => false,
                    ],
                    [
                        'current_quantity' => $newQuantity,
                        'threshold_quantity' => 0,
                    ]
                );
            } elseif ($newQuantity <= $product->low_stock_threshold) {
                StockAlert::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouseId,
                        'alert_type' => 'low_stock',
                        'is_resolved' => false,
                    ],
                    [
                        'current_quantity' => $newQuantity,
                        'threshold_quantity' => $product->low_stock_threshold,
                    ]
                );
            } else {
                // Resolve alerts if stock is now sufficient
                StockAlert::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseId)
                    ->where('is_resolved', false)
                    ->update(['is_resolved' => true, 'resolved_at' => now()]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'new_stock' => $newQuantity,
                'warehouse_stock' => $newQuantity,
                'total_stock' => $totalStock,
                'available_stock' => $warehouseStock->fresh()->available_quantity,
                'reserved_stock' => $warehouseStock->fresh()->reserved_quantity,
                'warehouse_name' => $warehouse->name,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quick stock update failed: ' . $e->getMessage(), [
                'product_id' => $id,
                'warehouse_id' => $request->warehouse_id ?? null,
                'action_type' => $request->action_type ?? null,
                'quantity' => $request->quantity ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get warehouse stock for quick stock modal
     */
    public function getWarehouseStock($productId)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $product = Product::with(['warehouseStock.warehouse'])->findOrFail($productId);

            // Check if product has variants
            if ($product->has_variants) {
                return response()->json([
                    'success' => false,
                    'message' => 'This product has variants. Please manage stock at variant level.',
                    'has_variants' => true,
                ], 422);
            }

            // Get warehouses that have stock records for this product
            $productWarehouses = $product->warehouseStock()
                ->whereNull('variant_id')
                ->with('warehouse')
                ->get()
                ->map(function($stock) {
                    return [
                        'id' => $stock->warehouse_id,
                        'name' => $stock->warehouse->name,
                        'code' => $stock->warehouse->code,
                        'current_stock' => $stock->quantity,
                        'reserved' => $stock->reserved_quantity,
                        'available' => $stock->available_quantity,
                        'is_default' => $stock->warehouse->is_default,
                        'is_active' => $stock->warehouse->is_active,
                        'location' => $stock->location,
                        'has_stock' => true,
                        'priority' => $stock->warehouse->priority ?? 0,
                    ];
                })
                ->sortByDesc('priority')
                ->values();

            // If no warehouses found, optionally include all active warehouses
            // Remove this section if you want ONLY warehouses with existing stock
            if ($productWarehouses->isEmpty()) {
                $productWarehouses = Warehouse::active()
                    ->byPriority()
                    ->get()
                    ->map(function($warehouse) {
                        return [
                            'id' => $warehouse->id,
                            'name' => $warehouse->name,
                            'code' => $warehouse->code,
                            'current_stock' => 0,
                            'reserved' => 0,
                            'available' => 0,
                            'is_default' => $warehouse->is_default,
                            'is_active' => $warehouse->is_active,
                            'location' => null,
                            'has_stock' => false,
                            'priority' => $warehouse->priority ?? 0,
                        ];
                    });
            }

            // Get total stock across all warehouses
            $totalStock = $product->getTotalWarehouseStock();

            return response()->json([
                'success' => true,
                'warehouses' => $productWarehouses,
                'total_stock' => $totalStock,
                'track_inventory' => $product->track_inventory,
                'low_stock_threshold' => $product->low_stock_threshold,
                'product_name' => $product->name,
                'sku' => $product->sku,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get warehouse stock: ' . $e->getMessage(), [
                'product_id' => $productId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load warehouse stock: ' . $e->getMessage()
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

    //
    /**
     * Get products list for AJAX requests (for dropdowns, etc.)
     */
    public function getProductsList(Request $request)
    {
        if (!auth('admin')->user()->hasPermission('products.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Product::where('track_inventory', true)
                       ->active()
                       ->select('id', 'name', 'sku', 'stock_quantity');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Limit results
        $limit = $request->get('limit', 100);
        $products = $query->orderBy('name', 'asc')->limit($limit)->get();

        return response()->json([
            'success' => true,
            'products' => $products,
            'count' => $products->count()
        ]);
    }

    /**
     * Get single product details for AJAX
     */
    public function getProductDetails(string $id)
    {
        if (!auth('admin')->user()->hasPermission('products.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $product = Product::with([
            'warehouseStock.warehouse',
            'category',
            'variants.warehouseStock.warehouse',
            'variants.status'
        ])->findOrFail($id);

        $response = [
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'stock_quantity' => $product->stock_quantity,
                'low_stock_threshold' => $product->low_stock_threshold,
                'track_inventory' => $product->track_inventory,
                'has_variants' => $product->has_variants,
                'price' => $product->price,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'title' => $product->category->title
                ] : null,
            ]
        ];

        // If product has variants, include variant details with their warehouse stock
        if ($product->has_variants && $product->variants->count() > 0) {
            $response['product']['variants'] = $product->variants->map(function($variant) {
                return [
                    'id' => $variant->id,
                    'variant_name' => $variant->variant_name,
                    'variant_value' => $variant->variant_value,
                    'full_name' => $variant->getFullName(),
                    'sku' => $variant->sku,
                    'price' => $variant->price,
                    'sale_price' => $variant->sale_price,
                    'final_price' => $variant->getFinalPrice(),
                    'stock_quantity' => $variant->stock_quantity,
                    'low_stock_threshold' => $variant->low_stock_threshold,
                    'is_default' => $variant->is_default,
                    'status' => $variant->status ? [
                        'key_code' => $variant->status_key_code,
                        'name' => $variant->status->name
                    ] : null,
                    'image_url' => $variant->getImageUrl(),
                    'warehouse_stock' => $variant->warehouseStock->map(function($stock) {
                        return [
                            'warehouse_id' => $stock->warehouse_id,
                            'warehouse_name' => $stock->warehouse->name,
                            'quantity' => $stock->quantity,
                            'available_quantity' => $stock->available_quantity,
                            'reserved_quantity' => $stock->reserved_quantity,
                            'location' => $stock->location,
                        ];
                    }),
                ];
            });

            // Total stock across all variants
            $response['product']['total_variant_stock'] = $product->variants->sum('stock_quantity') + $product->stock_quantity;
        } else {
            // Simple product - include warehouse stock directly
            $response['product']['warehouse_stock'] = $product->warehouseStock
                ->whereNull('variant_id')  // Only simple product stock
                ->map(function($stock) {
                    return [
                        'warehouse_id' => $stock->warehouse_id,
                        'warehouse_name' => $stock->warehouse->name,
                        'quantity' => $stock->quantity,
                        'available_quantity' => $stock->available_quantity,
                        'reserved_quantity' => $stock->reserved_quantity,
                        'location' => $stock->location,
                    ];
                });
        }

        return response()->json($response);
    }

    // varients
    /**
     * Get all variants for a product (AJAX)
     */
    public function getVariants($productId)
    {
        $product = Product::findOrFail($productId);
        $variants = $product->variants()->ordered()->get();

        $html = '';
        foreach ($variants as $variant) {
            $html .= view('admin.products.partials.variant-card', compact('variant'))->render();
        }

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Store a new variant
     */
    public function storeVariant(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $validator = Validator::make($request->all(), [
            'variant_name' => 'required|string|max:255',
            'variant_value' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:product_variants,sku',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'variant_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status_key_code' => 'required|string',
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
            $variantData = $request->only([
                'variant_name', 'variant_value', 'sku', 'price', 'sale_price',
                'weight', 'length', 'width', 'height',
                'low_stock_threshold', 'status_key_code'
            ]);

            $variantData['product_id'] = $product->id;
            $isDefaultInput = $request->input('is_default');
            if ($isDefaultInput === 'false' || $isDefaultInput === false || $isDefaultInput === '0' || $isDefaultInput === 0 || is_null($isDefaultInput)) {
                $variantData['is_default'] = false;
            } else {
                $variantData['is_default'] = (bool) $isDefaultInput;
            }
            $variantData['stock_quantity'] = 0; // ← Start with 0

            // Handle image upload
            if ($request->hasFile('variant_image')) {
                $image = $request->file('variant_image');
                $imageName = time() . '_' . Str::random(10) . '.' . $image->extension();
                $imagePath = $image->storeAs('variants', $imageName, 'public');
                $variantData['image_path'] = $imagePath;
            }

            $variant = ProductVariant::create($variantData);

            // ✅ ADD STOCK IF PROVIDED
            if ($request->filled('stock_quantity') && $request->stock_quantity > 0) {
                $defaultWarehouse = Warehouse::where('is_default', true)->first();

                if (!$defaultWarehouse) {
                    throw new \Exception('No default warehouse found. Please set a default warehouse first.');
                }

                $stockQty = (int) $request->stock_quantity;

                // Create warehouse stock
                $warehouseStock = ProductWarehouseStock::create([
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'warehouse_id' => $defaultWarehouse->id,
                    'quantity' => $stockQty,
                    'reserved_quantity' => 0,
                    'available_quantity' => $stockQty,
                ]);

                // Create inventory movement
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'warehouse_id' => $defaultWarehouse->id,
                    'type' => 'adjustment',
                    'quantity' => $stockQty,
                    'previous_quantity' => 0,
                    'new_quantity' => $stockQty,
                    'reason' => 'Initial stock on variant creation',
                ]);

                // Update variant stock_quantity
                $variant->update(['stock_quantity' => $stockQty]);

                // Update product total stock
                $product->updateTotalStock();
            }
            $product->syncHasVariantsFlag();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Variant created successfully',
                'variant' => $variant
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create variant: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create variant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single variant data
     */
    public function getVariant($productId, $variantId)
    {
        $variant = ProductVariant::where('product_id', $productId)
                                ->findOrFail($variantId);

        return response()->json([
            'success' => true,
            'variant' => [
                'id' => $variant->id,
                'variant_name' => $variant->variant_name,
                'variant_value' => $variant->variant_value,
                'sku' => $variant->sku,
                'price' => $variant->price,
                'sale_price' => $variant->sale_price,
                'weight' => $variant->weight,
                'length' => $variant->length,
                'width' => $variant->width,
                'height' => $variant->height,
                'stock_quantity' => $variant->stock_quantity,
                'low_stock_threshold' => $variant->low_stock_threshold,
                'status_key_code' => $variant->status_key_code,
                'is_default' => $variant->is_default,
                'image_path' => $variant->image_path,
                'image_url' => $variant->getImageUrl(),
            ]
        ]);
    }

    /**
     * Update variant - DEBUG VERSION
     */
    public function updateVariant(Request $request, $productId, $variantId)
    {

        $product = Product::findOrFail($productId);
        $variant = ProductVariant::where('product_id', $productId)->findOrFail($variantId);

        $validator = Validator::make($request->all(), [
            'variant_name' => 'required|string|max:255',
            'variant_value' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:product_variants,sku,' . $variant->id,
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'variant_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status_key_code' => 'required|string',
            // REMOVE validation for is_default temporarily to see raw value
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
            $variantData = $request->only([
                'variant_name', 'variant_value', 'sku', 'price', 'sale_price',
                'weight', 'length', 'width', 'height',
                'low_stock_threshold', 'status_key_code'
            ]);

            // DEBUG: Try multiple conversion methods
            $isDefaultValue = $request->input('is_default');

            \Log::info('is_default conversion attempts:', [
                'raw_value' => $isDefaultValue,
                'has_check' => $request->has('is_default'),
                'boolean_cast' => (bool) $isDefaultValue,
                'filter_var' => filter_var($isDefaultValue, FILTER_VALIDATE_BOOLEAN),
                'strict_check' => $isDefaultValue === true || $isDefaultValue === 'true' || $isDefaultValue === '1' || $isDefaultValue === 1,
            ]);

            // Try this approach
            if ($request->has('is_default')) {
                $value = $request->input('is_default');
                // Convert "on", "1", "true", true to boolean true
                $variantData['is_default'] = in_array($value, [true, 'true', '1', 1, 'on'], true);
            } else {
                $variantData['is_default'] = false;
            }

            \Log::info('Final is_default value:', [
                'value' => $variantData['is_default'],
                'type' => gettype($variantData['is_default'])
            ]);

            // Handle image upload
            if ($request->hasFile('variant_image')) {
                // Delete old image
                $variant->deleteImage();

                $image = $request->file('variant_image');
                $imageName = time() . '_' . Str::random(10) . '.' . $image->extension();
                $imagePath = $image->storeAs('variants', $imageName, 'public');
                $variantData['image_path'] = $imagePath;
            }

            $variant->update($variantData);

            // Handle stock update if changed
            if ($request->filled('stock_quantity')) {
                $newStock = (int) $request->stock_quantity;
                $currentStock = $variant->stock_quantity;

                if ($newStock != $currentStock) {
                    $defaultWarehouse = Warehouse::where('is_default', true)->first();
                    if ($defaultWarehouse) {
                        $warehouseStock = ProductWarehouseStock::where('product_id', $product->id)
                                                            ->where('variant_id', $variant->id)
                                                            ->where('warehouse_id', $defaultWarehouse->id)
                                                            ->first();

                        if ($warehouseStock) {
                            $difference = $newStock - $currentStock;
                            if ($difference > 0) {
                                $variant->addWarehouseStock($defaultWarehouse->id, $difference, 'Stock updated from admin panel');
                            } elseif ($difference < 0) {
                                $variant->reduceWarehouseStock($defaultWarehouse->id, abs($difference), 'Stock updated from admin panel');
                            }
                        } else {
                            $variant->addWarehouseStock($defaultWarehouse->id, $newStock, 'Stock set from admin panel');
                        }
                    }
                }
            }

            // ✅ SYNC has_variants flag (in case this was the last/first variant)
            // This shouldn't change anything during update, but it's a safety check
            $product->syncHasVariantsFlag();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Variant updated successfully',
                'variant' => $variant,
                'debug_is_default' => $variantData['is_default']
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update variant failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update variant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set variant as default
     */
    public function setDefaultVariant($productId, $variantId)
    {
        $product = Product::findOrFail($productId);
        $variant = ProductVariant::where('product_id', $productId)->findOrFail($variantId);

        if ($variant->setAsDefault()) {
            $product->syncHasVariantsFlag();
            return response()->json([
                'success' => true,
                'message' => 'Default variant updated successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to set default variant'
        ], 500);
    }

    /**
     * Delete variant
     */
    public function deleteVariant($productId, $variantId)
    {
        $product = Product::findOrFail($productId);
        $variant = ProductVariant::where('product_id', $productId)->findOrFail($variantId);

        DB::beginTransaction();

        try {
            // Delete warehouse stock records for this variant
            ProductWarehouseStock::where('product_id', $product->id)
                                ->where('variant_id', $variant->id)
                                ->delete();

            // Delete inventory movement records for this variant
            InventoryMovement::where('product_id', $product->id)
                            ->where('variant_id', $variant->id)
                            ->delete();

            // Delete the variant
            $variant->delete();

            // ✅ CRITICAL: Update product's total stock after variant deletion
            $product->updateTotalStock();

            // ✅ CRITICAL: Sync has_variants flag (might be false now if last variant was deleted)
            $product->syncHasVariantsFlag();

            DB::commit();

            $remainingVariants = $product->variants()->count();

            return response()->json([
                'success' => true,
                'message' => 'Variant deleted successfully',
                'remaining_variants' => $remainingVariants,
                'product_has_variants' => $product->fresh()->has_variants,
                'warning' => $remainingVariants === 0 ? 'This was the last variant. Product is now a simple product.' : null
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Delete variant failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete variant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete variants
     */
    public function bulkDeleteVariants(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $validator = Validator::make($request->all(), [
            'variant_ids' => 'required|array',
            'variant_ids.*' => 'required|string|exists:product_variants,id'
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
            $variantIds = $request->variant_ids;

            // Verify all variants belong to this product
            $variants = ProductVariant::where('product_id', $productId)
                                    ->whereIn('id', $variantIds)
                                    ->get();

            if ($variants->count() !== count($variantIds)) {
                throw new \Exception('Some variants do not belong to this product');
            }

            // Delete warehouse stock
            ProductWarehouseStock::where('product_id', $product->id)
                                ->whereIn('variant_id', $variantIds)
                                ->delete();

            // Delete inventory movements
            InventoryMovement::where('product_id', $product->id)
                            ->whereIn('variant_id', $variantIds)
                            ->delete();

            // Delete variants
            ProductVariant::where('product_id', $productId)
                        ->whereIn('id', $variantIds)
                        ->delete();

            // ✅ Update product's total stock
            $product->updateTotalStock();

            // ✅ CRITICAL: Sync has_variants flag
            $product->syncHasVariantsFlag();

            DB::commit();

            $remainingVariants = $product->variants()->count();

            return response()->json([
                'success' => true,
                'message' => count($variantIds) . ' variant(s) deleted successfully',
                'deleted_count' => count($variantIds),
                'remaining_variants' => $remainingVariants,
                'product_has_variants' => $product->fresh()->has_variants,
                'warning' => $remainingVariants === 0 ? 'All variants deleted. Product is now a simple product.' : null
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Bulk delete variants failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete variants: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download CSV import template
     */
    public function downloadTemplate()
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('products.create')) {
            abort(403, 'Unauthorized access');
        }

        // Define CSV headers (8 fields only)
        $headers = [
            'Name',
            'SKU',
            'Price',
            'Barcode',
            'Category',
            'Sale Price',
            'Stock Quantity',
            'Description',
        ];

        // Create CSV content
        $csvContent = [];

        // Add headers
        $csvContent[] = $headers;

        // Sample data row 1 - Coffee Machine
        $csvContent[] = [
            'Premium Coffee Maker',
            'PRD-CM-001',
            '299.99',
            '1234567890123',
            'Coffee Machines',
            '249.99',
            '50',
            'High-quality coffee maker with advanced features and stainless steel design',
        ];

        // Sample data row 2 - Coffee Beans
        $csvContent[] = [
            'Arabica Coffee Beans 1kg',
            'PRD-CB-001',
            '24.99',
            '9876543210987',
            'Coffee Beans',
            '',
            '100',
            'Premium Arabica coffee beans sourced from Colombia',
        ];

        // Sample data row 3 - Spare Parts
        $csvContent[] = [
            'Coffee Machine Filter',
            'PRD-SP-001',
            '9.99',
            '5555555555555',
            'Spare Parts',
            '7.99',
            '200',
            'Replacement filter compatible with multiple coffee machine models',
        ];

        // Generate filename with timestamp
        $filename = 'products_import_template_' . date('Y-m-d_His') . '.csv';

        // Create callback for streaming
        $callback = function() use ($csvContent) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            foreach ($csvContent as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        // Return streaming response
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }
}

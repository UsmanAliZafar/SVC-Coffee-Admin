<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class SwagerApiController extends Controller
{
    /**
     * ============================================
     * PUBLIC ENDPOINTS
     * ============================================
     */

    /**
     * @OA\Get(
     *     path="/api/demo",
     *     tags={"Public"},
     *     operationId="demoEndpoint",
     *     summary="Test endpoint to verify Swagger setup",
     *     description="This endpoint is just for testing Swagger generation.",
     *     @OA\Response(
     *         response=200,
     *         description="Swagger is working fine!"
     *     )
     * )
    */
    public function demo()
    {
        return response()->json(['message' => 'Swagger is working fine!']);
    }

    /**
     * @OA\Get(
     *     path="/api/public/health",
     *     operationId="healthCheck",
     *     tags={"Public"},
     *     summary="API Health Check",
     *     description="Check if API is running and healthy",
     *     @OA\Response(
     *         response=200,
     *         description="API is healthy",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="ok"),
     *             @OA\Property(property="timestamp", type="string", format="date-time"),
     *             @OA\Property(property="version", type="string", example="1.0")
     *         )
     *     )
     * )
     */
    public function health() {}

    /**
     * @OA\Get(
     *     path="/api/public/status",
     *     operationId="apiStatus",
     *     tags={"Public"},
     *     summary="Get API Status",
     *     description="Get current API operational status",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="api", type="string", example="Coffee Ecommerce API"),
     *             @OA\Property(property="status", type="string", example="operational")
     *         )
     *     )
     * )
    */
    public function status() {}

    /**
     * ============================================
     * CATEGORIES ENDPOINTS
     * ============================================
     */

    /**
     * @OA\Get(
     *     path="/api/categories",
     *     operationId="getAllCategories",
     *     tags={"Categories"},
     *     summary="Get all categories",
     *     description="Retrieve paginated list of categories with optional filters",
     *     security={{"apiKey": {}}},
     *     @OA\Parameter(
     *         name="parent_id",
     *         in="query",
     *         description="Filter by parent ID (use 'root' for root categories)",
     *         required=false,
     *         @OA\Schema(type="string", example="root")
     *     ),
     *     @OA\Parameter(
     *         name="is_featured",
     *         in="query",
     *         description="Filter featured categories",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="show_in_menu",
     *         in="query",
     *         description="Filter menu categories",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="show_on_home",
     *         in="query",
     *         description="Filter homepage categories",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by title or description",
     *         required=false,
     *         @OA\Schema(type="string", example="coffee")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page (max 100)",
     *         required=false,
     *         @OA\Schema(type="integer", default=15, example=15)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1, example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Categories retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="title", type="string", example="Coffee Machines"),
     *                 @OA\Property(property="slug", type="string", example="coffee-machines"),
     *                 @OA\Property(property="products_count", type="integer", example=45)
     *             )),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="total", type="integer", example=100),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="current_page", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized - Missing API key"),
     *     @OA\Response(response=403, description="Forbidden - Invalid API key")
     * )
     */
    public function getAllCategories() {}

    /**
     * @OA\Get(
     *     path="/api/categories/{id}",
     *     operationId="getCategoryById",
     *     tags={"Categories"},
     *     summary="Get category by ID",
     *     description="Get detailed information about a specific category including children and SEO data",
     *     security={{"apiKey": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category UUID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="9d4f5678-1234-5678-9abc-def123456789")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="seo", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function getCategoryById() {}

    /**
     * @OA\Get(
     *     path="/api/categories/slug/{slug}",
     *     operationId="getCategoryBySlug",
     *     tags={"Categories"},
     *     summary="Get category by slug",
     *     description="Get category information by slug (also increments view count)",
     *     security={{"apiKey": {}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Category slug",
     *         required=true,
     *         @OA\Schema(type="string", example="coffee-machines")
     *     ),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function getCategoryBySlug() {}

    /**
     * @OA\Get(
     *     path="/api/categories/tree",
     *     operationId="getCategoryTree",
     *     tags={"Categories"},
     *     summary="Get category tree",
     *     description="Get hierarchical tree structure of all categories",
     *     security={{"apiKey": {}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function getCategoryTree() {}

    /**
     * @OA\Get(
     *     path="/api/categories/roots",
     *     operationId="getRootCategories",
     *     tags={"Categories"},
     *     summary="Get root categories",
     *     description="Get only top-level categories (no parent)",
     *     security={{"apiKey": {}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function getRootCategories() {}

    /**
     * @OA\Get(
     *     path="/api/categories/featured",
     *     operationId="getFeaturedCategories",
     *     tags={"Categories"},
     *     summary="Get featured categories",
     *     description="Get featured categories (limited to 10)",
     *     security={{"apiKey": {}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function getFeaturedCategories() {}

    /**
     * @OA\Get(
     *     path="/api/categories/menu",
     *     operationId="getMenuCategories",
     *     tags={"Categories"},
     *     summary="Get menu categories",
     *     description="Get categories for navigation menu",
     *     security={{"apiKey": {}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function getMenuCategories() {}

    /**
     * @OA\Get(
     *     path="/api/categories/homepage",
     *     operationId="getHomepageCategories",
     *     tags={"Categories"},
     *     summary="Get homepage categories",
     *     description="Get categories to display on homepage (limited to 8)",
     *     security={{"apiKey": {}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function getHomepageCategories() {}

    /**
     * @OA\Get(
     *     path="/api/categories/{id}/breadcrumbs",
     *     operationId="getCategoryBreadcrumbs",
     *     tags={"Categories"},
     *     summary="Get category breadcrumbs",
     *     description="Get breadcrumb trail for a category",
     *     security={{"apiKey": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function getCategoryBreadcrumbs() {}

    /**
     * ============================================
     * CATEGORY PRODUCTS ENDPOINTS
     * ============================================
     */

    /**
     * @OA\Get(
     *     path="/api/categories/{id}/products",
     *     operationId="getCategoryProducts",
     *     tags={"Category Products"},
     *     summary="Get products by category",
     *     description="Get all products in a category with advanced filtering options",
     *     security={{"apiKey": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(name="is_featured", in="query", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="min_price", in="query", @OA\Schema(type="number", example=100)),
     *     @OA\Parameter(name="max_price", in="query", @OA\Schema(type="number", example=500)),
     *     @OA\Parameter(name="in_stock", in="query", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="on_sale", in="query", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_by", in="query", @OA\Schema(type="string", enum={"price", "name", "newest"})),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=20)),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function getCategoryProducts() {}

    /**
     * @OA\Get(
     *     path="/api/categories/slug/{slug}/products",
     *     operationId="getCategoryProductsBySlug",
     *     tags={"Category Products"},
     *     summary="Get products by category slug",
     *     description="Get products using category slug instead of ID",
     *     security={{"apiKey": {}}},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function getCategoryProductsBySlug() {}

    /**
     * @OA\Get(
     *     path="/api/categories/{id}/featured-products",
     *     operationId="getCategoryFeaturedProducts",
     *     tags={"Category Products"},
     *     summary="Get featured products from category",
     *     description="Get only featured products from a category (limited to 10)",
     *     security={{"apiKey": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function getCategoryFeaturedProducts() {}

    /**
     * ============================================
     * PRODUCTS ENDPOINTS
     * ============================================
     */

    /**
     * @OA\Get(
     *     path="/api/products",
     *     operationId="getAllProducts",
     *     tags={"Products"},
     *     summary="Get all products",
     *     description="Get paginated list of products with filters",
     *     security={{"apiKey": {}}},
     *     @OA\Parameter(name="category_id", in="query", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="is_featured", in="query", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="show_on_home", in="query", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="min_price", in="query", @OA\Schema(type="number")),
     *     @OA\Parameter(name="max_price", in="query", @OA\Schema(type="number")),
     *     @OA\Parameter(name="in_stock", in="query", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="on_sale", in="query", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="product_type", in="query", @OA\Schema(type="string", enum={"simple", "variable", "grouped", "external"})),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_by", in="query", @OA\Schema(type="string", enum={"price", "name", "newest", "sort_order"})),
     *     @OA\Parameter(name="sort_order", in="query", @OA\Schema(type="string", enum={"asc", "desc"}, default="asc")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=20)),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function getAllProducts() {}

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     operationId="getProductById",
     *     tags={"Products"},
     *     summary="Get product by ID",
     *     description="Get full product details including SEO, vendor, variants, related products, and tags",
     *     security={{"apiKey": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function getProductById() {}

    /**
     * @OA\Get(
     *     path="/api/products/slug/{slug}",
     *     operationId="getProductBySlug",
     *     tags={"Products"},
     *     summary="Get product by slug",
     *     description="Get full product details by slug",
     *     security={{"apiKey": {}}},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string", example="professional-espresso-machine")),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function getProductBySlug() {}

    /**
     * @OA\Get(
     *     path="/api/products/sku/{sku}",
     *     operationId="getProductBySku",
     *     tags={"Products"},
     *     summary="Get product by SKU",
     *     description="Get product details by SKU",
     *     security={{"apiKey": {}}},
     *     @OA\Parameter(name="sku", in="path", required=true, @OA\Schema(type="string", example="PRD-ESP-001")),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function getProductBySku() {}

    /**
     * @OA\Get(
     *     path="/api/products/featured",
     *     operationId="getFeaturedProducts",
     *     tags={"Products"},
     *     summary="Get featured products",
     *     description="Get featured products (limited to 12)",
     *     security={{"apiKey": {}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function getFeaturedProducts() {}

    /**
     * @OA\Get(
     *     path="/api/products/homepage",
     *     operationId="getHomepageProducts",
     *     tags={"Products"},
     *     summary="Get homepage products",
     *     description="Get products marked for homepage display (limited to 12)",
     *     security={{"apiKey": {}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function getHomepageProducts() {}

    /**
     * @OA\Get(
     *     path="/api/products/on-sale",
     *     operationId="getProductsOnSale",
     *     tags={"Products"},
     *     summary="Get products on sale",
     *     description="Get all products currently on sale with pagination",
     *     security={{"apiKey": {}}},
     *     @OA\Parameter(name="sort_by", in="query", @OA\Schema(type="string", enum={"price", "discount_percentage"}, default="discount_percentage")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=20)),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function getProductsOnSale() {}

    /**
     * @OA\Get(
     *     path="/api/products/search",
     *     operationId="searchProducts",
     *     tags={"Products"},
     *     summary="Search products",
     *     description="Search products by name, SKU, barcode, or description",
     *     security={{"apiKey": {}}},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         required=true,
     *         description="Search query (min 2 characters)",
     *         @OA\Schema(type="string", example="espresso")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=20)),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function searchProducts() {}

    /**
     * ============================================
     * CART ENDPOINTS
     * ============================================
     */

    /**
     * @OA\Get(
     *     path="/api/cart",
     *     operationId="getCart",
     *     tags={"Cart"},
     *     summary="Get cart contents",
     *     description="Retrieve cart contents with items, totals, and stock validation",
     *     security={{"apiKey": {}}},
     *     @OA\Parameter(
     *         name="cart_id",
     *         in="query",
     *         description="Cart ID (optional, can be passed in header X-Cart-ID)",
     *         required=false,
     *         @OA\Schema(type="string", example="abc123xyz")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Cart retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="cart_id", type="string", example="abc123xyz"),
     *                 @OA\Property(property="items", type="array", @OA\Items(
     *                     @OA\Property(property="product_id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string", example="Espresso Machine"),
     *                     @OA\Property(property="quantity", type="integer", example=2),
     *                     @OA\Property(property="price", type="number", format="float", example=299.99),
     *                     @OA\Property(property="image", type="string")
     *                 )),
     *                 @OA\Property(property="totals", type="object",
     *                     @OA\Property(property="subtotal", type="number", example=599.98),
     *                     @OA\Property(property="tax_amount", type="number", example=59.99),
     *                     @OA\Property(property="total_amount", type="number", example=659.97),
     *                     @OA\Property(property="total_items", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getCart() {}

    /**
     * @OA\Post(
     *     path="/api/cart/items",
     *     operationId="addItemToCart",
     *     tags={"Cart"},
     *     summary="Add item to cart",
     *     description="Add a product to cart. If cart_id is not provided, a new cart will be created.",
     *     security={{"apiKey": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "quantity"},
     *             @OA\Property(property="cart_id", type="string", example="abc123xyz", description="Cart ID (optional for new cart)"),
     *             @OA\Property(property="product_id", type="string", format="uuid", example="9d4f5678-1234-5678-9abc-def123456789"),
     *             @OA\Property(property="quantity", type="integer", example=2, minimum=1),
     *             @OA\Property(property="variant_id", type="string", format="uuid", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Item added to cart"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="cart_id", type="string"),
     *                 @OA\Property(property="items", type="array", @OA\Items()),
     *                 @OA\Property(property="totals", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Insufficient stock available"),
     *     @OA\Response(response=404, description="Product not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function addItemToCart() {}

    /**
     * @OA\Put(
     *     path="/api/cart/items",
     *     operationId="updateCartItem",
     *     tags={"Cart"},
     *     summary="Update cart item quantity",
     *     description="Update quantity of an item in cart. Set quantity to 0 to remove the item.",
     *     security={{"apiKey": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cart_id", "product_id", "quantity"},
     *             @OA\Property(property="cart_id", type="string", example="abc123xyz"),
     *             @OA\Property(property="product_id", type="string", format="uuid"),
     *             @OA\Property(property="variant_id", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="quantity", type="integer", example=3, minimum=0)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cart updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Cart updated successfully")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Insufficient stock available"),
     *     @OA\Response(response=404, description="Item not found in cart")
     * )
     */
    public function updateCartItem() {}

    /**
     * @OA\Delete(
     *     path="/api/cart/items",
     *     operationId="removeCartItem",
     *     tags={"Cart"},
     *     summary="Remove item from cart",
     *     description="Remove a specific item from cart",
     *     security={{"apiKey": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cart_id", "product_id"},
     *             @OA\Property(property="cart_id", type="string", example="abc123xyz"),
     *             @OA\Property(property="product_id", type="string", format="uuid"),
     *             @OA\Property(property="variant_id", type="string", format="uuid", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Item removed from cart")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Item not found in cart")
     * )
     */
    public function removeCartItem() {}

    /**
     * @OA\Delete(
     *     path="/api/cart/clear",
     *     operationId="clearCart",
     *     tags={"Cart"},
     *     summary="Clear cart",
     *     description="Clear all items from cart",
     *     security={{"apiKey": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cart_id"},
     *             @OA\Property(property="cart_id", type="string", example="abc123xyz")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cart cleared successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Cart cleared successfully")
     *         )
     *     )
     * )
     */
    public function clearCart() {}

    /**
     * @OA\Post(
     *     path="/api/cart/coupon",
     *     operationId="applyCoupon",
     *     tags={"Cart"},
     *     summary="Apply coupon code",
     *     description="Apply a coupon code to cart (Not yet implemented - returns 501)",
     *     security={{"apiKey": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cart_id", "coupon_code"},
     *             @OA\Property(property="cart_id", type="string", example="abc123xyz"),
     *             @OA\Property(property="coupon_code", type="string", example="SUMMER2025")
     *         )
     *     ),
     *     @OA\Response(
     *         response=501,
     *         description="Not implemented",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Coupon system not yet implemented")
     *         )
     *     )
     * )
     */
    public function applyCoupon() {}

    /**
     * @OA\Delete(
     *     path="/api/cart/coupon",
     *     operationId="removeCoupon",
     *     tags={"Cart"},
     *     summary="Remove coupon code",
     *     description="Remove an applied coupon code from cart",
     *     security={{"apiKey": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cart_id"},
     *             @OA\Property(property="cart_id", type="string", example="abc123xyz", description="Cart ID to remove coupon from")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Coupon removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Coupon removed successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="cart_id", type="string"),
     *                 @OA\Property(property="items", type="array", @OA\Items()),
     *                 @OA\Property(property="totals", type="object",
     *                     @OA\Property(property="subtotal", type="number", example=599.98),
     *                     @OA\Property(property="discount_amount", type="number", example=0),
     *                     @OA\Property(property="tax_amount", type="number", example=59.99),
     *                     @OA\Property(property="total_amount", type="number", example=659.97)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="No coupon applied to cart",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No coupon applied to this cart")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cart not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cart not found")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function removeCoupon() {}
    /**
     * ============================================
     * CHECKOUT ENDPOINTS
     * ============================================
     */

    /**
     * @OA\Post(
     *     path="/api/checkout/create-order",
     *     operationId="createOrder",
     *     tags={"Checkout"},
     *     summary="Create order from cart",
     *     description="Create a new order from cart. Supports both guest and registered customer checkout.",
     *     security={{"apiKey": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cart_id", "shipping_first_name", "shipping_last_name", "shipping_address_line1", "shipping_city", "shipping_postal_code", "shipping_country", "shipping_phone", "shipping_method", "payment_method"},
     *             @OA\Property(property="cart_id", type="string", example="abc123xyz"),
     *             @OA\Property(property="customer_id", type="string", format="uuid", nullable=true, example=null, description="Customer UUID (null for guest checkout)"),
     *             @OA\Property(property="guest_email", type="string", format="email", example="john.doe@example.com", description="Required if customer_id is null"),
     *             @OA\Property(property="guest_name", type="string", example="John Doe", description="Required if customer_id is null"),
     *             @OA\Property(property="guest_phone", type="string", example="+1234567890", nullable=true),
     *             @OA\Property(property="shipping_first_name", type="string", example="John", maxLength=100),
     *             @OA\Property(property="shipping_last_name", type="string", example="Doe", maxLength=100),
     *             @OA\Property(property="shipping_address_line1", type="string", example="123 Main Street", maxLength=255),
     *             @OA\Property(property="shipping_address_line2", type="string", example="Apt 4B", nullable=true, maxLength=255),
     *             @OA\Property(property="shipping_city", type="string", example="New York", maxLength=100),
     *             @OA\Property(property="shipping_state", type="string", example="NY", nullable=true, maxLength=100),
     *             @OA\Property(property="shipping_postal_code", type="string", example="10001", maxLength=20),
     *             @OA\Property(property="shipping_country", type="string", example="United States", maxLength=100),
     *             @OA\Property(property="shipping_phone", type="string", example="+1234567890", maxLength=20),
     *             @OA\Property(property="billing_same_as_shipping", type="boolean", example=true),
     *             @OA\Property(property="billing_first_name", type="string", example="John", nullable=true, description="Required if billing_same_as_shipping is false"),
     *             @OA\Property(property="billing_last_name", type="string", example="Doe", nullable=true),
     *             @OA\Property(property="billing_address_line1", type="string", example="456 Business Ave", nullable=true),
     *             @OA\Property(property="billing_address_line2", type="string", example="Suite 100", nullable=true),
     *             @OA\Property(property="billing_city", type="string", example="Los Angeles", nullable=true),
     *             @OA\Property(property="billing_state", type="string", example="CA", nullable=true),
     *             @OA\Property(property="billing_postal_code", type="string", example="90001", nullable=true),
     *             @OA\Property(property="billing_country", type="string", example="United States", nullable=true),
     *             @OA\Property(property="shipping_method", type="string", example="standard", enum={"standard", "express", "overnight", "free"}),
     *             @OA\Property(property="payment_method", type="string", example="credit_card", enum={"credit_card", "paypal", "stripe", "cash_on_delivery"}),
     *             @OA\Property(property="customer_notes", type="string", example="Please ring doorbell", nullable=true),
     *             @OA\Property(property="coupon_code", type="string", example="SUMMER2025", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="order_id", type="string", format="uuid"),
     *                 @OA\Property(property="order_number", type="string", example="ORD-2025-00001"),
     *                 @OA\Property(property="total_amount", type="number", format="float", example=659.97),
     *                 @OA\Property(property="currency", type="string", example="USD"),
     *                 @OA\Property(property="payment_required", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cart is empty or insufficient stock",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cart is empty")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Failed to create order")
     * )
     */
    public function createOrder() {}

    /**
     * ============================================
     * ORDERS ENDPOINTS
     * ============================================
     */

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     operationId="getCustomerOrders",
     *     tags={"Orders"},
     *     summary="Get customer orders",
     *     description="Get list of customer's orders with optional filtering and pagination",
     *     security={{"apiKey": {}}},
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="query",
     *         required=true,
     *         description="Customer UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by order status",
     *         @OA\Schema(type="string", enum={"ORDER_PENDING", "ORDER_CONFIRMED", "ORDER_PROCESSING", "ORDER_SHIPPED", "ORDER_DELIVERED", "ORDER_CANCELLED"})
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filter from date (Y-m-d)",
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filter to date (Y-m-d)",
     *         @OA\Schema(type="string", format="date", example="2025-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort field",
     *         @OA\Schema(type="string", default="created_at", example="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Sort order",
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page (max 100)",
     *         @OA\Schema(type="integer", default=20, maximum=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Orders retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="order_number", type="string", example="ORD-2025-00001"),
     *                 @OA\Property(property="status", type="object",
     *                     @OA\Property(property="code", type="string", example="ORDER_CONFIRMED"),
     *                     @OA\Property(property="label", type="string", example="Confirmed")
     *                 ),
     *                 @OA\Property(property="totals", type="object",
     *                     @OA\Property(property="total_amount", type="number", example=659.97),
     *                     @OA\Property(property="currency", type="string", example="USD")
     *                 ),
     *                 @OA\Property(property="items_count", type="integer", example=3),
     *                 @OA\Property(property="order_date", type="string", format="date-time")
     *             )),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="per_page", type="integer", example=20),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=3)
     *             ),
     *             @OA\Property(property="timestamp", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Customer ID is required")
     * )
     */
    public function getCustomerOrders() {}

    /**
     * @OA\Get(
     *     path="/api/orders/{orderNumber}",
     *     operationId="getOrderDetails",
     *     tags={"Orders"},
     *     summary="Get order details",
     *     description="Get detailed information about a specific order",
     *     security={{"apiKey": {}}},
     *     @OA\Parameter(
     *         name="orderNumber",
     *         in="path",
     *         required=true,
     *         description="Order number (e.g., ORD-2025-00001)",
     *         @OA\Schema(type="string", example="ORD-2025-00001")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="order_number", type="string", example="ORD-2025-00001"),
     *                 @OA\Property(property="status", type="object"),
     *                 @OA\Property(property="payment_status", type="object"),
     *                 @OA\Property(property="items", type="array", @OA\Items(
     *                     @OA\Property(property="product_name", type="string"),
     *                     @OA\Property(property="quantity", type="integer"),
     *                     @OA\Property(property="unit_price", type="number"),
     *                     @OA\Property(property="total", type="number")
     *                 )),
     *                 @OA\Property(property="shipping_address", type="object"),
     *                 @OA\Property(property="billing_address", type="object"),
     *                 @OA\Property(property="totals", type="object"),
     *                 @OA\Property(property="dates", type="object")
     *             ),
     *             @OA\Property(property="timestamp", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function getOrderDetails() {}

    /**
     * @OA\Get(
     *     path="/api/orders/{orderNumber}/track",
     *     operationId="trackOrder",
     *     tags={"Orders"},
     *     summary="Track order status",
     *     description="Track order status with timeline and shipping information",
     *     security={{"apiKey": {}}},
     *     @OA\Parameter(
     *         name="orderNumber",
     *         in="path",
     *         required=true,
     *         description="Order number",
     *         @OA\Schema(type="string", example="ORD-2025-00001")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order tracking retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="order_number", type="string", example="ORD-2025-00001"),
     *                 @OA\Property(property="status", type="object",
     *                     @OA\Property(property="code", type="string"),
     *                     @OA\Property(property="label", type="string")
     *                 ),
     *                 @OA\Property(property="timeline", type="array", @OA\Items(
     *                     @OA\Property(property="status", type="string", example="Ordered"),
     *                     @OA\Property(property="completed", type="boolean", example=true),
     *                     @OA\Property(property="date", type="string", format="date-time")
     *                 )),
     *                 @OA\Property(property="shipping", type="object",
     *                     @OA\Property(property="carrier", type="string", example="FedEx"),
     *                     @OA\Property(property="tracking_number", type="string", example="123456789"),
     *                     @OA\Property(property="tracking_url", type="string")
     *                 ),
     *                 @OA\Property(property="estimated_delivery", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function trackOrder() {}

    /**
     * @OA\Post(
     *     path="/api/orders/{orderNumber}/cancel",
     *     operationId="cancelOrder",
     *     tags={"Orders"},
     *     summary="Cancel order",
     *     description="Cancel an order and restore stock",
     *     security={{"apiKey": {}}},
     *     @OA\Parameter(
     *         name="orderNumber",
     *         in="path",
     *         required=true,
     *         description="Order number",
     *         @OA\Schema(type="string", example="ORD-2025-00001")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reason"},
     *             @OA\Property(property="reason", type="string", maxLength=500, example="Changed my mind, want to order different products")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order cancelled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order cancelled successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Order cannot be cancelled at this stage",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order cannot be cancelled at this stage")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function cancelOrder() {}

    /**
     * @OA\Post(
     *     path="/api/orders/{orderNumber}/return",
     *     operationId="requestReturn",
     *     tags={"Orders"},
     *     summary="Request return/refund",
     *     description="Request return or refund for order items",
     *     security={{"apiKey": {}}},
     *     @OA\Parameter(
     *         name="orderNumber",
     *         in="path",
     *         required=true,
     *         description="Order number",
     *         @OA\Schema(type="string", example="ORD-2025-00001")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"items", "return_method"},
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 @OA\Property(property="item_id", type="string", format="uuid", description="Order item UUID"),
     *                 @OA\Property(property="quantity", type="integer", minimum=1, example=1),
     *                 @OA\Property(property="reason", type="string", example="Product damaged during shipping")
     *             )),
     *             @OA\Property(property="return_method", type="string", enum={"refund", "replacement"}, example="refund"),
     *             @OA\Property(property="additional_notes", type="string", maxLength=1000, nullable=true, example="Please process refund to original payment method")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Return request submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Return request submitted successfully. Our team will review and contact you soon.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Order is not eligible for return/refund",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order is not eligible for return/refund")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function requestReturn() {}
}

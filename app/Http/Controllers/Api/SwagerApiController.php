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
}

<?php

namespace App\Http\Controllers\Api;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="SVC Ecommerce API",
 *     description="API documentation for SVC Coffee Ecommerce Platform - Complete e-commerce solution with cart, checkout, and order management",
 *     @OA\Contact(
 *         email="usman@workforcecommerce.com",
 *         name="SVC Development Team"
 *     ),
 *     @OA\License(
 *         name="Proprietary",
 *         url="https://workforcecommerce.com/license"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local Development Server"
 * )
 *
 * @OA\Server(
 *     url="https://staging-dashboard.svcarabia.com",
 *     description="Staging Server"
 * )
 *
 * @OA\Server(
 *     url="https://api.workforcecommerce.com",
 *     description="Production Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="apiKey",
 *     type="apiKey",
 *     in="header",
 *     name="X-API-Key",
 *     description="API Key Authentication - Required for all authenticated endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Public",
 *     description="Public endpoints (no authentication required)"
 * )
 *
 * @OA\Tag(
 *     name="Categories",
 *     description="Category management - Browse and filter product categories"
 * )
 *
 * @OA\Tag(
 *     name="Category Products",
 *     description="Products by category - Get products filtered by specific categories"
 * )
 *
 * @OA\Tag(
 *     name="Products",
 *     description="Product management - Browse, search, and view product details"
 * )
 *
 * @OA\Tag(
 *     name="Cart",
 *     description="Shopping cart management - Add, update, remove items, apply coupons, and calculate shipping"
 * )
 *
 * @OA\Tag(
 *     name="Checkout",
 *     description="Checkout process - Create orders from cart with guest or registered customer"
 * )
 *
 * @OA\Tag(
 *     name="Orders",
 *     description="Order management - View, track, cancel orders and request returns"
 * )
 *
 * @OA\Tag(
 *    name="Store Settings",
 *    description="Store settings and configuration endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Contact Us",
 *     description="Contact form submissions - Submit inquiries and messages to the store"
 * )
 *
 * @OA\Response(
 *     response="Success",
 *     description="Successful operation",
 *     @OA\JsonContent(
 *         @OA\Property(property="success", type="boolean", example=true),
 *         @OA\Property(property="message", type="string", example="Operation completed successfully"),
 *         @OA\Property(property="data", type="object"),
 *         @OA\Property(property="timestamp", type="string", format="date-time")
 *     )
 * )
 *
 * @OA\Response(
 *     response="BadRequest",
 *     description="Bad request - Invalid parameters",
 *     @OA\JsonContent(
 *         @OA\Property(property="success", type="boolean", example=false),
 *         @OA\Property(property="message", type="string", example="Invalid request parameters"),
 *         @OA\Property(property="error", type="string")
 *     )
 * )
 *
 * @OA\Response(
 *     response="Unauthorized",
 *     description="Unauthorized - Missing or invalid API key",
 *     @OA\JsonContent(
 *         @OA\Property(property="success", type="boolean", example=false),
 *         @OA\Property(property="message", type="string", example="Unauthorized - Missing API key"),
 *         @OA\Property(property="error", type="string", example="API key is required")
 *     )
 * )
 *
 * @OA\Response(
 *     response="Forbidden",
 *     description="Forbidden - Invalid API key",
 *     @OA\JsonContent(
 *         @OA\Property(property="success", type="boolean", example=false),
 *         @OA\Property(property="message", type="string", example="Forbidden - Invalid API key"),
 *         @OA\Property(property="error", type="string", example="Invalid API key provided")
 *     )
 * )
 *
 * @OA\Response(
 *     response="NotFound",
 *     description="Resource not found",
 *     @OA\JsonContent(
 *         @OA\Property(property="success", type="boolean", example=false),
 *         @OA\Property(property="message", type="string", example="Resource not found"),
 *         @OA\Property(property="error", type="string", example="The requested resource does not exist")
 *     )
 * )
 *
 * @OA\Response(
 *     response="UnprocessableEntity",
 *     description="Validation error",
 *     @OA\JsonContent(
 *         @OA\Property(property="success", type="boolean", example=false),
 *         @OA\Property(property="message", type="string", example="Validation failed"),
 *         @OA\Property(property="errors", type="object",
 *             @OA\Property(property="field_name", type="array", @OA\Items(type="string", example="The field is required"))
 *         )
 *     )
 * )
 *
 * @OA\Response(
 *     response="InternalServerError",
 *     description="Internal server error",
 *     @OA\JsonContent(
 *         @OA\Property(property="success", type="boolean", example=false),
 *         @OA\Property(property="message", type="string", example="Internal server error"),
 *         @OA\Property(property="error", type="string", example="An unexpected error occurred")
 *     )
 * )
 *
 * @OA\Response(
 *     response="NotImplemented",
 *     description="Feature not yet implemented",
 *     @OA\JsonContent(
 *         @OA\Property(property="success", type="boolean", example=false),
 *         @OA\Property(property="message", type="string", example="Feature not yet implemented")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Pagination",
 *     type="object",
 *     title="Pagination",
 *     description="Standard pagination object",
 *     @OA\Property(property="total", type="integer", example=100, description="Total number of items"),
 *     @OA\Property(property="per_page", type="integer", example=20, description="Items per page"),
 *     @OA\Property(property="current_page", type="integer", example=1, description="Current page number"),
 *     @OA\Property(property="last_page", type="integer", example=5, description="Last page number"),
 *     @OA\Property(property="from", type="integer", example=1, description="First item on current page"),
 *     @OA\Property(property="to", type="integer", example=20, description="Last item on current page")
 * )
 *
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     title="Category",
 *     description="Product category object",
 *     @OA\Property(property="id", type="string", format="uuid", example="9d4f5678-1234-5678-9abc-def123456789"),
 *     @OA\Property(property="parent_id", type="string", format="uuid", nullable=true, example=null, description="Parent category ID for subcategories"),
 *     @OA\Property(property="title", type="string", example="Coffee Machines", description="Category name"),
 *     @OA\Property(property="slug", type="string", example="coffee-machines", description="URL-friendly slug"),
 *     @OA\Property(property="description", type="string", example="Professional coffee machines for home and office", description="Category description"),
 *     @OA\Property(property="short_description", type="string", nullable=true, example="Best coffee machines", description="Short description"),
 *     @OA\Property(property="image", type="string", nullable=true, example="https://example.com/storage/categories/coffee-machines.jpg", description="Category image URL"),
 *     @OA\Property(property="icon", type="string", nullable=true, example="bi-cup-hot", description="Icon class"),
 *     @OA\Property(property="is_featured", type="boolean", example=true, description="Featured on homepage"),
 *     @OA\Property(property="show_in_menu", type="boolean", example=true, description="Show in navigation menu"),
 *     @OA\Property(property="show_on_home", type="boolean", example=true, description="Show on homepage"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Active status"),
 *     @OA\Property(property="sort_order", type="integer", example=1, description="Display order"),
 *     @OA\Property(property="products_count", type="integer", example=45, description="Number of products in category"),
 *     @OA\Property(property="children_count", type="integer", example=5, description="Number of subcategories"),
 *     @OA\Property(property="level", type="integer", example=0, description="Nesting level (0 = root)"),
 *     @OA\Property(property="seo", type="object", ref="#/components/schemas/SEO"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="CategoryTree",
 *     type="object",
 *     title="Category Tree",
 *     description="Hierarchical category structure",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Category")
 *     },
 *     @OA\Property(
 *         property="children",
 *         type="array",
 *         description="Child categories",
 *         @OA\Items(ref="#/components/schemas/CategoryTree")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="CategorySummary",
 *     type="object",
 *     title="Category Summary",
 *     description="Simplified category object for listings",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="title", type="string", example="Coffee Machines"),
 *     @OA\Property(property="slug", type="string", example="coffee-machines"),
 *     @OA\Property(property="image", type="string", nullable=true),
 *     @OA\Property(property="products_count", type="integer", example=45)
 * )
 *
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product",
 *     description="Complete product object with all details",
 *     @OA\Property(property="id", type="string", format="uuid", example="9d4f5678-1234-5678-9abc-def123456789"),
 *     @OA\Property(property="category_id", type="string", format="uuid", description="Primary category ID"),
 *     @OA\Property(property="name", type="string", example="Professional Espresso Machine", description="Product name"),
 *     @OA\Property(property="slug", type="string", example="professional-espresso-machine", description="URL-friendly slug"),
 *     @OA\Property(property="sku", type="string", example="PRD-ESP-001", description="Stock Keeping Unit"),
 *     @OA\Property(property="barcode", type="string", nullable=true, example="1234567890123", description="Product barcode"),
 *     @OA\Property(property="short_description", type="string", nullable=true, example="High-quality espresso machine", description="Brief description"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Full product description with features...", description="Full HTML description"),
 *     @OA\Property(property="price", type="number", format="float", example=299.99, description="Regular price"),
 *     @OA\Property(property="sale_price", type="number", format="float", nullable=true, example=249.99, description="Sale price if on sale"),
 *     @OA\Property(property="cost_price", type="number", format="float", nullable=true, example=150.00, description="Cost price"),
 *     @OA\Property(property="compare_at_price", type="number", format="float", nullable=true, example=349.99, description="Compare at price"),
 *     @OA\Property(property="is_on_sale", type="boolean", example=true, description="Currently on sale"),
 *     @OA\Property(property="discount_percentage", type="number", format="float", example=16.67, description="Discount percentage"),
 *     @OA\Property(property="product_type", type="string", enum={"simple", "variable", "grouped", "external"}, example="simple", description="Product type"),
 *     @OA\Property(property="is_featured", type="boolean", example=true, description="Featured product"),
 *     @OA\Property(property="show_on_home", type="boolean", example=true, description="Show on homepage"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Active status"),
 *     @OA\Property(property="track_inventory", type="boolean", example=true, description="Track stock levels"),
 *     @OA\Property(property="stock_quantity", type="integer", example=50, description="Available stock quantity"),
 *     @OA\Property(property="low_stock_threshold", type="integer", nullable=true, example=10, description="Low stock alert threshold"),
 *     @OA\Property(property="stock_status", type="string", enum={"in_stock", "out_of_stock", "on_backorder"}, example="in_stock"),
 *     @OA\Property(property="is_taxable", type="boolean", example=true, description="Subject to tax"),
 *     @OA\Property(property="tax_percentage", type="number", format="float", nullable=true, example=10.0, description="Tax rate percentage"),
 *     @OA\Property(property="weight", type="number", format="float", nullable=true, example=5.5, description="Product weight in kg"),
 *     @OA\Property(property="length", type="number", format="float", nullable=true, example=30.0, description="Length in cm"),
 *     @OA\Property(property="width", type="number", format="float", nullable=true, example=20.0, description="Width in cm"),
 *     @OA\Property(property="height", type="number", format="float", nullable=true, example=25.0, description="Height in cm"),
 *     @OA\Property(property="shipping_class", type="string", nullable=true, example="standard", description="Shipping class"),
 *     @OA\Property(property="main_image", type="string", nullable=true, example="https://example.com/storage/products/espresso-machine.jpg", description="Main product image URL"),
 *     @OA\Property(property="gallery_images", type="array", description="Product gallery images", @OA\Items(type="string", example="https://example.com/storage/products/gallery-1.jpg")),
 *     @OA\Property(property="sort_order", type="integer", example=1, description="Display order"),
 *     @OA\Property(property="view_count", type="integer", example=1250, description="Number of views"),
 *     @OA\Property(property="purchase_count", type="integer", example=85, description="Number of purchases"),
 *     @OA\Property(property="rating_average", type="number", format="float", nullable=true, example=4.5, description="Average rating (0-5)"),
 *     @OA\Property(property="rating_count", type="integer", example=32, description="Number of ratings"),
 *     @OA\Property(property="category", type="object", ref="#/components/schemas/CategorySummary", description="Product category"),
 *     @OA\Property(property="images", type="array", description="Product images", @OA\Items(ref="#/components/schemas/ProductImage")),
 *     @OA\Property(property="variants", type="array", description="Product variants", @OA\Items(ref="#/components/schemas/ProductVariant")),
 *     @OA\Property(property="related_products", type="array", description="Related products", @OA\Items(ref="#/components/schemas/ProductSummary")),
 *     @OA\Property(property="tags", type="array", description="Product tags", @OA\Items(type="string", example="espresso")),
 *     @OA\Property(property="seo", type="object", ref="#/components/schemas/SEO"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="ProductSummary",
 *     type="object",
 *     title="Product Summary",
 *     description="Simplified product object for listings",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="name", type="string", example="Professional Espresso Machine"),
 *     @OA\Property(property="slug", type="string", example="professional-espresso-machine"),
 *     @OA\Property(property="sku", type="string", example="PRD-ESP-001"),
 *     @OA\Property(property="short_description", type="string", nullable=true, example="High-quality espresso machine"),
 *     @OA\Property(property="price", type="number", format="float", example=299.99),
 *     @OA\Property(property="sale_price", type="number", format="float", nullable=true, example=249.99),
 *     @OA\Property(property="is_on_sale", type="boolean", example=true),
 *     @OA\Property(property="discount_percentage", type="number", format="float", example=16.67),
 *     @OA\Property(property="main_image", type="string", nullable=true),
 *     @OA\Property(property="is_featured", type="boolean", example=true),
 *     @OA\Property(property="stock_status", type="string", example="in_stock"),
 *     @OA\Property(property="stock_quantity", type="integer", example=50),
 *     @OA\Property(property="rating_average", type="number", format="float", nullable=true, example=4.5),
 *     @OA\Property(property="rating_count", type="integer", example=32),
 *     @OA\Property(property="category", type="object", ref="#/components/schemas/CategorySummary")
 * )
 *
 * @OA\Schema(
 *     schema="ProductImage",
 *     type="object",
 *     title="Product Image",
 *     description="Product image object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="image_path", type="string", example="products/espresso-machine-1.jpg", description="Storage path"),
 *     @OA\Property(property="image_url", type="string", example="https://example.com/storage/products/espresso-machine-1.jpg", description="Full URL"),
 *     @OA\Property(property="thumbnail_url", type="string", example="https://example.com/storage/products/thumbs/espresso-machine-1.jpg", description="Thumbnail URL"),
 *     @OA\Property(property="alt_text", type="string", nullable=true, example="Professional Espresso Machine - Front View"),
 *     @OA\Property(property="is_main", type="boolean", example=false, description="Is main product image"),
 *     @OA\Property(property="sort_order", type="integer", example=1, description="Display order")
 * )
 *
 * @OA\Schema(
 *     schema="ProductVariant",
 *     type="object",
 *     title="Product Variant",
 *     description="Product variant (size, color, etc.)",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="product_id", type="string", format="uuid"),
 *     @OA\Property(property="sku", type="string", example="PRD-ESP-001-BLK", description="Variant SKU"),
 *     @OA\Property(property="name", type="string", example="Black", description="Variant name"),
 *     @OA\Property(property="price", type="number", format="float", nullable=true, example=299.99, description="Variant price (if different)"),
 *     @OA\Property(property="sale_price", type="number", format="float", nullable=true, example=249.99),
 *     @OA\Property(property="stock_quantity", type="integer", example=25, description="Variant stock"),
 *     @OA\Property(property="image", type="string", nullable=true, example="https://example.com/storage/variants/black.jpg"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="attributes", type="object",
 *         description="Variant attributes",
 *         @OA\Property(property="color", type="string", example="Black"),
 *         @OA\Property(property="size", type="string", example="Large")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="SEO",
 *     type="object",
 *     title="SEO",
 *     description="SEO metadata object",
 *     @OA\Property(property="meta_title", type="string", nullable=true, example="Professional Espresso Machine - Best Coffee Makers", description="SEO meta title"),
 *     @OA\Property(property="meta_description", type="string", nullable=true, example="Buy the best professional espresso machine for home and office use. High quality, affordable prices.", description="SEO meta description"),
 *     @OA\Property(property="meta_keywords", type="string", nullable=true, example="espresso machine, coffee maker, professional", description="SEO meta keywords"),
 *     @OA\Property(property="canonical_url", type="string", nullable=true, example="https://example.com/products/professional-espresso-machine", description="Canonical URL"),
 *     @OA\Property(property="og_title", type="string", nullable=true, example="Professional Espresso Machine", description="Open Graph title"),
 *     @OA\Property(property="og_description", type="string", nullable=true, example="Best espresso machine for coffee lovers", description="Open Graph description"),
 *     @OA\Property(property="og_image", type="string", nullable=true, example="https://example.com/storage/products/og-image.jpg", description="Open Graph image")
 * )
 *
 * @OA\Schema(
 *     schema="CartItem",
 *     type="object",
 *     title="Cart Item",
 *     description="Shopping cart item",
 *     @OA\Property(property="product_id", type="string", format="uuid", example="9d4f5678-1234-5678-9abc-def123456789"),
 *     @OA\Property(property="variant_id", type="string", format="uuid", nullable=true, example=null),
 *     @OA\Property(property="name", type="string", example="Professional Espresso Machine"),
 *     @OA\Property(property="slug", type="string", example="professional-espresso-machine"),
 *     @OA\Property(property="sku", type="string", example="PRD-ESP-001"),
 *     @OA\Property(property="image", type="string", example="https://example.com/storage/products/espresso-machine.jpg"),
 *     @OA\Property(property="price", type="number", format="float", example=299.99, description="Current price"),
 *     @OA\Property(property="regular_price", type="number", format="float", example=349.99, description="Regular price before discount"),
 *     @OA\Property(property="quantity", type="integer", example=2, description="Item quantity"),
 *     @OA\Property(property="is_taxable", type="boolean", example=true),
 *     @OA\Property(property="tax_rate", type="number", format="float", example=10.0),
 *     @OA\Property(property="max_quantity", type="integer", example=50, description="Maximum available quantity"),
 *     @OA\Property(property="added_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="CartTotals",
 *     type="object",
 *     title="Cart Totals",
 *     description="Cart or order totals",
 *     @OA\Property(property="subtotal", type="number", format="float", example=599.98, description="Sum of all item prices"),
 *     @OA\Property(property="tax_amount", type="number", format="float", example=59.99, description="Total tax amount"),
 *     @OA\Property(property="shipping_amount", type="number", format="float", example=15.00, description="Shipping cost"),
 *     @OA\Property(property="discount_amount", type="number", format="float", example=0.00, description="Discount from coupons"),
 *     @OA\Property(property="total_amount", type="number", format="float", example=674.97, description="Final total amount"),
 *     @OA\Property(property="total_items", type="integer", example=2, description="Total number of items"),
 *     @OA\Property(property="currency", type="string", example="USD", description="Currency code")
 * )
 *
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     title="Order",
 *     description="Order summary object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="order_number", type="string", example="ORD-2025-00001"),
 *     @OA\Property(property="status", type="object",
 *         @OA\Property(property="code", type="string", example="ORDER_CONFIRMED"),
 *         @OA\Property(property="label", type="string", example="Confirmed")
 *     ),
 *     @OA\Property(property="payment_status", type="object",
 *         @OA\Property(property="code", type="string", example="PAYMENT_PAID"),
 *         @OA\Property(property="label", type="string", example="Paid")
 *     ),
 *     @OA\Property(property="totals", ref="#/components/schemas/CartTotals"),
 *     @OA\Property(property="items_count", type="integer", example=3),
 *     @OA\Property(property="order_date", type="string", format="date-time"),
 *     @OA\Property(property="can_cancel", type="boolean", example=true, description="Can order be cancelled"),
 *     @OA\Property(property="can_return", type="boolean", example=false, description="Can order be returned")
 * )
 *
 * @OA\Schema(
 *     schema="OrderDetails",
 *     type="object",
 *     title="Order Details",
 *     description="Complete order details",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Order")
 *     },
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/OrderItem")),
 *     @OA\Property(property="shipping_address", ref="#/components/schemas/Address"),
 *     @OA\Property(property="billing_address", ref="#/components/schemas/Address"),
 *     @OA\Property(property="payment", type="object",
 *         @OA\Property(property="method", type="string", example="credit_card"),
 *         @OA\Property(property="status", type="string", example="PAYMENT_PAID")
 *     ),
 *     @OA\Property(property="shipping", type="object",
 *         @OA\Property(property="method", type="string", example="standard"),
 *         @OA\Property(property="carrier", type="string", nullable=true, example="FedEx"),
 *         @OA\Property(property="tracking_number", type="string", nullable=true, example="123456789"),
 *         @OA\Property(property="tracking_url", type="string", nullable=true)
 *     ),
 *     @OA\Property(property="dates", type="object",
 *         @OA\Property(property="created_at", type="string", format="date-time"),
 *         @OA\Property(property="confirmed_at", type="string", format="date-time", nullable=true),
 *         @OA\Property(property="shipped_at", type="string", format="date-time", nullable=true),
 *         @OA\Property(property="delivered_at", type="string", format="date-time", nullable=true),
 *         @OA\Property(property="cancelled_at", type="string", format="date-time", nullable=true)
 *     ),
 *     @OA\Property(property="notes", type="object",
 *         @OA\Property(property="customer_notes", type="string", nullable=true, example="Please ring doorbell")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="OrderItem",
 *     type="object",
 *     title="Order Item",
 *     description="Individual order item",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="product_id", type="string", format="uuid"),
 *     @OA\Property(property="product_name", type="string", example="Professional Espresso Machine"),
 *     @OA\Property(property="product_sku", type="string", example="PRD-ESP-001"),
 *     @OA\Property(property="product_image", type="string", nullable=true),
 *     @OA\Property(property="quantity", type="integer", example=1),
 *     @OA\Property(property="unit_price", type="number", format="float", example=299.99),
 *     @OA\Property(property="subtotal", type="number", format="float", example=299.99),
 *     @OA\Property(property="tax_amount", type="number", format="float", example=29.99),
 *     @OA\Property(property="total", type="number", format="float", example=329.98),
 *     @OA\Property(property="status", type="string", example="ITEM_PENDING")
 * )
 *
 * @OA\Schema(
 *     schema="Address",
 *     type="object",
 *     title="Address",
 *     description="Shipping or billing address",
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="address_line1", type="string", example="123 Main Street"),
 *     @OA\Property(property="address_line2", type="string", nullable=true, example="Apt 4B"),
 *     @OA\Property(property="city", type="string", example="New York"),
 *     @OA\Property(property="state", type="string", nullable=true, example="NY"),
 *     @OA\Property(property="postal_code", type="string", example="10001"),
 *     @OA\Property(property="country", type="string", example="United States"),
 *     @OA\Property(property="phone", type="string", example="+1234567890")
 * )
 *
 * @OA\Schema(
 *     schema="OrderTracking",
 *     type="object",
 *     title="Order Tracking",
 *     description="Order tracking information with timeline",
 *     @OA\Property(property="order_number", type="string", example="ORD-2025-00001"),
 *     @OA\Property(property="status", type="object",
 *         @OA\Property(property="code", type="string", example="ORDER_SHIPPED"),
 *         @OA\Property(property="label", type="string", example="Shipped")
 *     ),
 *     @OA\Property(property="timeline", type="array", @OA\Items(
 *         @OA\Property(property="status", type="string", example="Ordered"),
 *         @OA\Property(property="completed", type="boolean", example=true),
 *         @OA\Property(property="date", type="string", format="date-time", nullable=true)
 *     )),
 *     @OA\Property(property="shipping", type="object",
 *         @OA\Property(property="carrier", type="string", nullable=true, example="FedEx"),
 *         @OA\Property(property="tracking_number", type="string", nullable=true, example="123456789"),
 *         @OA\Property(property="tracking_url", type="string", nullable=true, example="https://fedex.com/track/123456789")
 *     ),
 *     @OA\Property(property="estimated_delivery", type="string", format="date-time", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="StoreSettings",
 *     type="object",
 *     title="Store Settings",
 *     description="Complete store settings and configuration",
 *     @OA\Property(property="basic_info", ref="#/components/schemas/BasicInfo"),
 *     @OA\Property(property="branding", ref="#/components/schemas/Branding"),
 *     @OA\Property(property="regional", ref="#/components/schemas/RegionalSettings"),
 *     @OA\Property(property="order_settings", ref="#/components/schemas/OrderSettings"),
 *     @OA\Property(property="tax_settings", ref="#/components/schemas/TaxSettings"),
 *     @OA\Property(property="shipping_settings", ref="#/components/schemas/ShippingSettings"),
 *     @OA\Property(property="inventory_settings", ref="#/components/schemas/InventorySettings"),
 *     @OA\Property(property="email_settings", ref="#/components/schemas/EmailSettings"),
 *     @OA\Property(property="checkout_settings", ref="#/components/schemas/CheckoutSettings"),
 *     @OA\Property(property="social_media", ref="#/components/schemas/SocialMedia"),
 *     @OA\Property(property="seo", ref="#/components/schemas/SeoSettings"),
 *     @OA\Property(property="maintenance", ref="#/components/schemas/MaintenanceMode"),
 *     @OA\Property(property="business_hours", type="object", nullable=true, description="Business hours for each day of week")
 * )
 *
 * @OA\Schema(
 *     schema="BasicInfo",
 *     type="object",
 *     title="Basic Store Information",
 *     description="Store basic information and contact details",
 *     @OA\Property(property="store_name", type="string", example="My Store"),
 *     @OA\Property(property="store_email", type="string", format="email", example="store@example.com"),
 *     @OA\Property(property="store_phone", type="string", nullable=true, example="+92-300-1234567"),
 *     @OA\Property(property="store_address", type="string", nullable=true, example="123 Main Street"),
 *     @OA\Property(property="store_city", type="string", nullable=true, example="Lahore"),
 *     @OA\Property(property="store_state", type="string", nullable=true, example="Punjab"),
 *     @OA\Property(property="store_zip", type="string", nullable=true, example="54000"),
 *     @OA\Property(property="store_country", type="string", nullable=true, example="PK"),
 *     @OA\Property(property="store_tagline", type="string", nullable=true, example="Your One-Stop Shop"),
 *     @OA\Property(property="store_description", type="string", nullable=true, example="Welcome to our online store"),
 *     @OA\Property(property="full_address", type="string", nullable=true, example="123 Main Street, Lahore, Punjab, 54000, PK")
 * )
 *
 * @OA\Schema(
 *     schema="Branding",
 *     type="object",
 *     title="Store Branding",
 *     description="Store branding assets (logo, favicon, banner)",
 *     @OA\Property(property="logo_url", type="string", format="uri", example="https://example.com/storage/branding/logos/logo.png"),
 *     @OA\Property(property="favicon_url", type="string", format="uri", example="https://example.com/storage/branding/favicons/favicon.png"),
 *     @OA\Property(property="banner_url", type="string", format="uri", example="https://example.com/storage/branding/banners/banner.jpg")
 * )
 *
 * @OA\Schema(
 *     schema="RegionalSettings",
 *     type="object",
 *     title="Regional Settings",
 *     description="Regional configuration (timezone, currency, formats)",
 *     @OA\Property(property="timezone", type="string", example="Asia/Karachi"),
 *     @OA\Property(property="date_format", type="string", example="Y-m-d"),
 *     @OA\Property(property="time_format", type="string", example="H:i:s"),
 *     @OA\Property(property="currency_code", type="string", example="PKR"),
 *     @OA\Property(property="currency_symbol", type="string", example="Rs."),
 *     @OA\Property(property="currency_position", type="string", enum={"left", "right"}, example="left"),
 *     @OA\Property(property="decimal_places", type="integer", example=2),
 *     @OA\Property(property="thousand_separator", type="string", example=","),
 *     @OA\Property(property="decimal_separator", type="string", example=".")
 * )
 *
 * @OA\Schema(
 *     schema="OrderSettings",
 *     type="object",
 *     title="Order Settings",
 *     description="Order numbering and configuration",
 *     @OA\Property(property="order_prefix", type="string", example="ORD-"),
 *     @OA\Property(property="order_number_start", type="integer", example=1000),
 *     @OA\Property(property="order_number_length", type="integer", example=6),
 *     @OA\Property(property="order_auto_confirm", type="boolean", example=false),
 *     @OA\Property(property="order_notification_email", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *     schema="TaxSettings",
 *     type="object",
 *     title="Tax Settings",
 *     description="Tax configuration and rates",
 *     @OA\Property(property="tax_enabled", type="boolean", example=false),
 *     @OA\Property(property="tax_rate", type="number", format="float", example=0.00),
 *     @OA\Property(property="tax_name", type="string", example="VAT"),
 *     @OA\Property(property="tax_included_in_price", type="boolean", example=false)
 * )
 *
 * @OA\Schema(
 *     schema="ShippingSettings",
 *     type="object",
 *     title="Shipping Settings",
 *     description="Shipping configuration and rates",
 *     @OA\Property(property="shipping_enabled", type="boolean", example=true),
 *     @OA\Property(property="free_shipping_threshold", type="number", format="float", nullable=true, example=5000.00),
 *     @OA\Property(property="default_shipping_cost", type="number", format="float", example=200.00),
 *     @OA\Property(property="shipping_calculation_type", type="string", enum={"flat_rate", "per_kg", "per_liter", "per_item", "tiered"}, example="flat_rate"),
 *     @OA\Property(property="shipping_rate_per_kg", type="number", format="float", nullable=true, example=null),
 *     @OA\Property(property="shipping_rate_per_liter", type="number", format="float", nullable=true, example=null),
 *     @OA\Property(property="shipping_rate_per_item", type="number", format="float", nullable=true, example=null),
 *     @OA\Property(property="enable_nationwide_flat_rate", type="boolean", example=true),
 *     @OA\Property(property="nationwide_flat_rate", type="number", format="float", nullable=true, example=200.00),
 *     @OA\Property(property="enable_regional_rates", type="boolean", example=false),
 *     @OA\Property(property="minimum_order_for_shipping", type="number", format="float", nullable=true, example=null),
 *     @OA\Property(property="max_weight_standard_shipping", type="number", format="float", nullable=true, example=null),
 *     @OA\Property(property="max_volume_standard_shipping", type="number", format="float", nullable=true, example=null),
 *     @OA\Property(property="handling_fee", type="number", format="float", example=0.00),
 *     @OA\Property(property="tiered_shipping_rates", type="array", nullable=true, @OA\Items(ref="#/components/schemas/TieredShippingRate")),
 *     @OA\Property(property="estimated_delivery_days_min", type="integer", nullable=true, example=3),
 *     @OA\Property(property="estimated_delivery_days_max", type="integer", nullable=true, example=7),
 *     @OA\Property(property="estimated_delivery", type="string", nullable=true, example="3-7 business days")
 * )
 *
 * @OA\Schema(
 *     schema="TieredShippingRate",
 *     type="object",
 *     title="Tiered Shipping Rate",
 *     description="Tiered shipping rate tier",
 *     @OA\Property(property="type", type="string", enum={"order_total", "weight"}, example="order_total"),
 *     @OA\Property(property="threshold", type="number", format="float", example=1000.00),
 *     @OA\Property(property="rate", type="number", format="float", example=150.00)
 * )
 *
 * @OA\Schema(
 *     schema="InventorySettings",
 *     type="object",
 *     title="Inventory Settings",
 *     description="Inventory tracking configuration",
 *     @OA\Property(property="track_inventory", type="boolean", example=true),
 *     @OA\Property(property="allow_backorders", type="boolean", example=false),
 *     @OA\Property(property="low_stock_threshold", type="integer", example=10),
 *     @OA\Property(property="low_stock_notifications", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *     schema="EmailSettings",
 *     type="object",
 *     title="Email Settings",
 *     description="Email configuration and notifications",
 *     @OA\Property(property="email_from_name", type="string", example="My Store"),
 *     @OA\Property(property="email_from_address", type="string", format="email", example="noreply@example.com"),
 *     @OA\Property(property="customer_registration_email", type="boolean", example=true),
 *     @OA\Property(property="order_confirmation_email", type="boolean", example=true),
 *     @OA\Property(property="order_shipped_email", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *     schema="CheckoutSettings",
 *     type="object",
 *     title="Checkout Settings",
 *     description="Checkout and payment configuration",
 *     @OA\Property(property="payment_methods", type="object",
 *         @OA\Property(property="cod", ref="#/components/schemas/PaymentMethodCOD"),
 *         @OA\Property(property="online_payment", ref="#/components/schemas/PaymentMethodOnline"),
 *         @OA\Property(property="bank_transfer", ref="#/components/schemas/PaymentMethodBankTransfer")
 *     ),
 *     @OA\Property(property="available_payment_methods", type="array", @OA\Items(ref="#/components/schemas/AvailablePaymentMethod")),
 *     @OA\Property(property="checkout_options", ref="#/components/schemas/CheckoutOptions"),
 *     @OA\Property(property="order_confirmation", ref="#/components/schemas/OrderConfirmation")
 * )
 *
 * @OA\Schema(
 *     schema="PaymentMethodCOD",
 *     type="object",
 *     title="Cash on Delivery Payment Method",
 *     @OA\Property(property="enabled", type="boolean", example=true),
 *     @OA\Property(property="instructions", type="string", nullable=true, example="Please keep exact change ready")
 * )
 *
 * @OA\Schema(
 *     schema="PaymentMethodOnline",
 *     type="object",
 *     title="Online Payment Method",
 *     @OA\Property(property="enabled", type="boolean", example=false),
 *     @OA\Property(property="gateway", type="string", nullable=true, example="stripe"),
 *     @OA\Property(property="mode", type="string", enum={"sandbox", "live"}, example="sandbox"),
 *     @OA\Property(property="instructions", type="string", nullable=true, example="You will be redirected to payment gateway")
 * )
 *
 * @OA\Schema(
 *     schema="PaymentMethodBankTransfer",
 *     type="object",
 *     title="Bank Transfer Payment Method",
 *     @OA\Property(property="enabled", type="boolean", example=true),
 *     @OA\Property(property="instructions", type="string", nullable=true, example="Transfer to our bank account"),
 *     @OA\Property(property="bank_details", type="object", nullable=true,
 *         @OA\Property(property="bank_name", type="string", example="HBL Bank"),
 *         @OA\Property(property="account_name", type="string", example="My Store"),
 *         @OA\Property(property="account_number", type="string", example="1234567890"),
 *         @OA\Property(property="iban", type="string", nullable=true, example="PK36HBLB0000001234567890"),
 *         @OA\Property(property="swift_code", type="string", nullable=true, example="HBLBPKKAXXX"),
 *         @OA\Property(property="branch", type="string", nullable=true, example="Main Branch, Lahore"),
 *         @OA\Property(property="formatted", type="string", example="Bank Name: HBL Bank\nAccount Name: My Store...")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="AvailablePaymentMethod",
 *     type="object",
 *     title="Available Payment Method",
 *     @OA\Property(property="key", type="string", example="cod"),
 *     @OA\Property(property="name", type="string", example="Cash on Delivery"),
 *     @OA\Property(property="icon", type="string", example="bi-cash-coin"),
 *     @OA\Property(property="instructions", type="string", nullable=true, example="Pay when you receive")
 * )
 *
 * @OA\Schema(
 *     schema="CheckoutOptions",
 *     type="object",
 *     title="Checkout Options",
 *     @OA\Property(property="require_phone", type="boolean", example=true),
 *     @OA\Property(property="require_address", type="boolean", example=true),
 *     @OA\Property(property="enable_guest_checkout", type="boolean", example=true),
 *     @OA\Property(property="terms_required", type="boolean", example=true),
 *     @OA\Property(property="terms_text", type="string", nullable=true, example="By placing order, you agree to our terms")
 * )
 *
 * @OA\Schema(
 *     schema="OrderConfirmation",
 *     type="object",
 *     title="Order Confirmation Settings",
 *     @OA\Property(property="message", type="string", nullable=true, example="Thank you for your order!"),
 *     @OA\Property(property="show_bank_details", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *     schema="SocialMedia",
 *     type="object",
 *     title="Social Media Links",
 *     description="Store social media profiles",
 *     @OA\Property(property="facebook", type="string", format="uri", nullable=true, example="https://facebook.com/mystore"),
 *     @OA\Property(property="twitter", type="string", format="uri", nullable=true, example="https://twitter.com/mystore"),
 *     @OA\Property(property="instagram", type="string", format="uri", nullable=true, example="https://instagram.com/mystore"),
 *     @OA\Property(property="linkedin", type="string", format="uri", nullable=true, example="https://linkedin.com/company/mystore"),
 *     @OA\Property(property="youtube", type="string", format="uri", nullable=true, example="https://youtube.com/mystore"),
 *     @OA\Property(property="social_links", type="object", description="Filtered non-null social links")
 * )
 *
 * @OA\Schema(
 *     schema="SeoSettings",
 *     type="object",
 *     title="SEO Settings",
 *     description="SEO and analytics configuration",
 *     @OA\Property(property="meta_title", type="string", nullable=true, example="My Store - Best Products"),
 *     @OA\Property(property="meta_description", type="string", nullable=true, example="Shop the best products at great prices"),
 *     @OA\Property(property="meta_keywords", type="string", nullable=true, example="online store, shopping, products"),
 *     @OA\Property(property="google_analytics_id", type="string", nullable=true, example="G-XXXXXXXXXX"),
 *     @OA\Property(property="facebook_pixel_id", type="string", nullable=true, example="123456789012345")
 * )
 *
 * @OA\Schema(
 *     schema="MaintenanceMode",
 *     type="object",
 *     title="Maintenance Mode",
 *     description="Store maintenance mode status",
 *     @OA\Property(property="maintenance_mode", type="boolean", example=false),
 *     @OA\Property(property="maintenance_message", type="string", nullable=true, example="We are performing maintenance"),
 *     @OA\Property(property="is_in_maintenance", type="boolean", example=false)
 * )
 *
 * @OA\Schema(
 *     schema="ShippingCalculation",
 *     type="object",
 *     title="Shipping Calculation Result",
 *     description="Calculated shipping cost details",
 *     @OA\Property(property="shipping_cost", type="number", format="float", example=200.00),
 *     @OA\Property(property="formatted_cost", type="string", example="Rs.200.00"),
 *     @OA\Property(property="is_free_shipping", type="boolean", example=false),
 *     @OA\Property(property="calculation_type", type="string", example="flat_rate"),
 *     @OA\Property(property="estimated_delivery", type="string", nullable=true, example="3-7 business days"),
 *     @OA\Property(property="currency_symbol", type="string", example="Rs.")
 * )
 *
 * @OA\Schema(
 *     schema="CurrencyFormat",
 *     type="object",
 *     title="Currency Format Result",
 *     description="Formatted currency amount",
 *     @OA\Property(property="amount", type="number", format="float", example=1234.56),
 *     @OA\Property(property="formatted", type="string", example="Rs.1,234.56"),
 *     @OA\Property(property="currency_code", type="string", example="PKR"),
 *     @OA\Property(property="currency_symbol", type="string", example="Rs.")
 * )
 *
 * @OA\Schema(
 *     schema="BusinessHours",
 *     type="object",
 *     title="Business Hours",
 *     description="Store business hours information",
 *     @OA\Property(property="business_hours", type="object", description="Hours for each day"),
 *     @OA\Property(property="is_open_today", type="boolean", example=true),
 *     @OA\Property(property="today_hours", type="object", nullable=true,
 *         @OA\Property(property="is_open", type="boolean", example=true),
 *         @OA\Property(property="open", type="string", example="09:00"),
 *         @OA\Property(property="close", type="string", example="18:00")
 *     ),
 *     @OA\Property(property="timezone", type="string", example="Asia/Karachi")
 * )
 *
 * @OA\Schema(
 *     schema="ContactForm",
 *     type="object",
 *     title="Contact Form",
 *     description="Contact form submission data",
 *     required={"name", "email", "subject", "message"},
 *     @OA\Property(property="name", type="string", maxLength=255, example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", maxLength=255, example="john.doe@example.com"),
 *     @OA\Property(property="phone", type="string", maxLength=20, nullable=true, example="+1234567890"),
 *     @OA\Property(property="subject", type="string", maxLength=500, example="Product Inquiry - Coffee Machine"),
 *     @OA\Property(property="message", type="string", maxLength=5000, example="I'm interested in your professional espresso machine..."),
 *     @OA\Property(property="priority", type="string", enum={"low", "normal", "high", "urgent"}, example="normal")
 * )
 *
 * @OA\Schema(
 *     schema="ContactResponse",
 *     type="object",
 *     title="Contact Response",
 *     description="Successful contact form submission response",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Your message has been sent successfully! We will get back to you soon."),
 *     @OA\Property(property="data", type="object",
 *         @OA\Property(property="id", type="string", format="uuid", example="9d4f5678-1234-5678-9abc-def123456789"),
 *         @OA\Property(property="name", type="string", example="John Doe"),
 *         @OA\Property(property="email", type="string", example="john.doe@example.com"),
 *         @OA\Property(property="subject", type="string", example="Product Inquiry - Coffee Machine"),
 *         @OA\Property(property="created_at", type="string", format="date-time")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ContactValidationError",
 *     type="object",
 *     title="Contact Validation Error",
 *     description="Contact form validation error response",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Validation failed"),
 *     @OA\Property(property="errors", type="object",
 *         @OA\Property(property="field_name", type="array", @OA\Items(type="string", example="The field is required"))
 *     )
 * )
 *
 * ============================================================
 * CART SHIPPING CALCULATION SCHEMAS
 * ============================================================
 *
 * @OA\Schema(
 *     schema="ShippingCalculationRequest",
 *     type="object",
 *     title="Shipping Calculation Request",
 *     description="Request payload for calculating shipping cost",
 *     required={"cart_id"},
 *     @OA\Property(
 *         property="cart_id",
 *         type="string",
 *         format="uuid",
 *         description="Cart UUID",
 *         example="9d4e8f2a-1b3c-4d5e-6f7a-8b9c0d1e2f3a"
 *     ),
 *     @OA\Property(
 *         property="shipping_method",
 *         type="string",
 *         enum={"standard", "express", "overnight", "free"},
 *         description="Shipping method (optional, defaults to 'standard')",
 *         example="standard",
 *         nullable=true
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ShippingCalculationResponse",
 *     type="object",
 *     title="Shipping Calculation Response",
 *     description="Successful shipping calculation response",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Shipping cost calculated successfully"),
 *     @OA\Property(property="data", ref="#/components/schemas/ShippingCalculationData")
 * )
 *
 * @OA\Schema(
 *     schema="ShippingCalculationData",
 *     type="object",
 *     title="Shipping Calculation Data",
 *     description="Detailed shipping cost calculation data",
 *     @OA\Property(
 *         property="shipping_cost",
 *         type="number",
 *         format="float",
 *         description="Calculated shipping cost",
 *         example=15.50
 *     ),
 *     @OA\Property(
 *         property="formatted_cost",
 *         type="string",
 *         description="Formatted shipping cost with currency",
 *         example="Rs. 15.50"
 *     ),
 *     @OA\Property(
 *         property="free_shipping",
 *         type="boolean",
 *         description="Whether free shipping applies",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="calculation_type",
 *         type="string",
 *         enum={"flat_rate", "per_kg", "per_liter", "per_item", "tiered"},
 *         description="Method used to calculate shipping",
 *         example="per_kg"
 *     ),
 *     @OA\Property(
 *         property="shipping_method",
 *         type="string",
 *         description="Selected shipping method",
 *         example="standard"
 *     ),
 *     @OA\Property(
 *         property="currency",
 *         type="string",
 *         description="Currency symbol",
 *         example="Rs."
 *     ),
 *     @OA\Property(
 *         property="estimated_delivery",
 *         type="string",
 *         nullable=true,
 *         description="Estimated delivery time",
 *         example="3-5 business days"
 *     ),
 *     @OA\Property(
 *         property="breakdown",
 *         ref="#/components/schemas/ShippingBreakdown",
 *         description="Detailed cost breakdown"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ShippingBreakdown",
 *     type="object",
 *     title="Shipping Cost Breakdown",
 *     description="Detailed breakdown of shipping cost calculation",
 *     @OA\Property(
 *         property="base_cost",
 *         type="number",
 *         format="float",
 *         description="Base shipping cost before fees",
 *         example=12.50
 *     ),
 *     @OA\Property(
 *         property="handling_fee",
 *         type="number",
 *         format="float",
 *         description="Additional handling fee",
 *         example=3.00
 *     ),
 *     @OA\Property(
 *         property="total_weight",
 *         type="number",
 *         format="float",
 *         description="Total weight in kg",
 *         example=2.5
 *     ),
 *     @OA\Property(
 *         property="total_volume",
 *         type="number",
 *         format="float",
 *         description="Total volume in liters",
 *         example=1.2
 *     ),
 *     @OA\Property(
 *         property="item_count",
 *         type="integer",
 *         description="Total number of items",
 *         example=3
 *     ),
 *     @OA\Property(
 *         property="cart_subtotal",
 *         type="number",
 *         format="float",
 *         description="Cart subtotal amount",
 *         example=150.00
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ShippingFreeFromCoupon",
 *     type="object",
 *     title="Free Shipping from Coupon",
 *     description="Response when free shipping is applied via coupon",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Free shipping applied from coupon"),
 *     @OA\Property(property="data", type="object",
 *         @OA\Property(property="shipping_cost", type="number", format="float", example=0.00),
 *         @OA\Property(property="formatted_cost", type="string", example="Rs. 0.00"),
 *         @OA\Property(property="free_shipping", type="boolean", example=true),
 *         @OA\Property(property="free_shipping_reason", type="string", example="coupon"),
 *         @OA\Property(property="coupon_code", type="string", nullable=true, example="FREESHIP"),
 *         @OA\Property(property="currency", type="string", example="Rs."),
 *         @OA\Property(property="estimated_delivery", type="string", nullable=true, example="3-5 business days")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ShippingFreeFromThreshold",
 *     type="object",
 *     title="Free Shipping from Threshold",
 *     description="Response when free shipping threshold is met",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Free shipping threshold met"),
 *     @OA\Property(property="data", type="object",
 *         @OA\Property(property="shipping_cost", type="number", format="float", example=0.00),
 *         @OA\Property(property="formatted_cost", type="string", example="Rs. 0.00"),
 *         @OA\Property(property="free_shipping", type="boolean", example=true),
 *         @OA\Property(property="free_shipping_reason", type="string", example="threshold"),
 *         @OA\Property(property="threshold_amount", type="number", format="float", example=500.00),
 *         @OA\Property(property="formatted_threshold", type="string", example="Rs. 500.00"),
 *         @OA\Property(property="currency", type="string", example="Rs."),
 *         @OA\Property(property="estimated_delivery", type="string", nullable=true, example="3-5 business days")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ShippingMinimumNotMet",
 *     type="object",
 *     title="Minimum Order Not Met",
 *     description="Error when minimum order value for shipping is not met",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Minimum order value not met for shipping"),
 *     @OA\Property(property="data", type="object",
 *         @OA\Property(property="minimum_required", type="number", format="float", example=100.00),
 *         @OA\Property(property="formatted_minimum", type="string", example="Rs. 100.00"),
 *         @OA\Property(property="current_subtotal", type="number", format="float", example=75.00),
 *         @OA\Property(property="formatted_subtotal", type="string", example="Rs. 75.00"),
 *         @OA\Property(property="amount_needed", type="number", format="float", example=25.00),
 *         @OA\Property(property="formatted_amount_needed", type="string", example="Rs. 25.00"),
 *         @OA\Property(property="currency", type="string", example="Rs.")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ShippingLimitExceeded",
 *     type="object",
 *     title="Shipping Limit Exceeded",
 *     description="Error when order exceeds shipping weight or volume limits",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Order exceeds maximum weight limit"),
 *     @OA\Property(property="data", type="object",
 *         @OA\Property(property="limit_type", type="string", enum={"weight", "volume"}, example="weight"),
 *         @OA\Property(property="limit_value", type="number", format="float", example=50.0),
 *         @OA\Property(property="current_value", type="number", format="float", example=55.5),
 *         @OA\Property(property="unit", type="string", example="kg")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ShippingCartEmpty",
 *     type="object",
 *     title="Empty Cart Error",
 *     description="Error when attempting to calculate shipping for empty cart",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Cart is empty")
 * )
 */
class SwaggerAnnotations
{
    // This class is just for Swagger annotations
}

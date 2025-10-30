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
 *     url="https://staging.workforcecommerce.com",
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
 *     description="Shopping cart management - Add, update, remove items from cart"
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
 */
class SwaggerAnnotations
{
    // This class is just for Swagger annotations
}

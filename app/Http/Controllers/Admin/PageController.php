<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PageController extends Controller
{
    /**
     * Display a listing of pages.
     */
    public function index(Request $request)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.read')) {
            abort(403, 'Unauthorized action.');
        }

        // Get statistics
        $stats = [
            'total' => Page::count(),
            'published' => Page::where('status', 'published')->count(),
            'draft' => Page::where('status', 'draft')->count(),
            'archived' => Page::where('status', 'archived')->count(),
        ];

        // Get parent pages for filter
        $parentPages = Page::whereNull('parent_id')
                          ->orderBy('title')
                          ->get();

        // Available templates
        $templates = [
            'default' => 'Default Template',
            'full-width' => 'Full Width',
            'sidebar-left' => 'Sidebar Left',
            'sidebar-right' => 'Sidebar Right',
            'landing' => 'Landing Page',
        ];

        return view('admin.pages.index', compact('stats', 'parentPages', 'templates'));
    }

    /**
     * Get pages data for DataTable (AJAX)
     */
    public function getData(Request $request)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Page::with(['creator', 'updater', 'parent', 'children'])
                    ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('visibility')) {
            $query->where('visibility', $request->visibility);
        }

        if ($request->filled('parent_filter')) {
            if ($request->parent_filter === 'parent') {
                $query->whereNull('parent_id');
            } elseif ($request->parent_filter === 'child') {
                $query->whereNotNull('parent_id');
            }
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        if ($request->filled('template')) {
            $query->where('template', $request->template);
        }

        if ($request->filled('navigation')) {
            if ($request->navigation === 'header') {
                $query->where('show_in_header', true);
            } elseif ($request->navigation === 'footer') {
                $query->where('show_in_footer', true);
            } elseif ($request->navigation === 'none') {
                $query->where('show_in_header', false)
                      ->where('show_in_footer', false);
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('meta_title', 'like', "%{$search}%")
                  ->orWhere('meta_description', 'like', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addColumn('checkbox', function($page) {
                if (Auth::guard('admin')->user()->hasPermission('content.delete')) {
                    return '<input type="checkbox" class="form-check-input page-checkbox" value="' . $page->id . '">';
                }
                return '';
            })
            ->addColumn('page_info', function($page) {
                $viewUrl = route('admin.pages.show', $page->id);

                $html = '<div class="d-flex align-items-center">';

                // Featured Image or Placeholder
                if ($page->featured_image && Storage::disk('public')->exists($page->featured_image)) {
                    $html .= '<img src="' . Storage::url($page->featured_image) . '"
                             alt="' . htmlspecialchars($page->title) . '"
                             class="rounded me-2"
                             style="width: 50px; height: 50px; object-fit: cover;">';
                } else {
                    $html .= '<div class="bg-light rounded me-2 d-flex align-items-center justify-content-center"
                             style="width: 50px; height: 50px;">
                             <i class="bi bi-file-earmark text-muted fs-4"></i>
                             </div>';
                }

                // Title and Excerpt
                $html .= '<div class="page-details">';
                $html .= '<div class="page-title">';
                $html .= '<strong><a href="' . $viewUrl . '" class="text-decoration-none page-link">'
                     . htmlspecialchars(Str::limit($page->title, 50)) . '</a></strong>';
                $html .= '</div>';

                // Slug
                $html .= '<div class="page-slug">';
                $html .= '<code class="small text-muted">' . htmlspecialchars($page->slug) . '</code>';
                $html .= '</div>';

                // Excerpt if available
                if ($page->excerpt) {
                    $html .= '<div class="page-excerpt">';
                    $html .= '<small class="text-muted">' . htmlspecialchars(Str::limit($page->excerpt, 60)) . '</small>';
                    $html .= '</div>';
                }

                $html .= '</div>';
                $html .= '</div>';

                return $html;
            })
            ->addColumn('parent_badge', function($page) {
                if ($page->parent) {
                    return '<span class="badge bg-light text-dark">
                            <i class="bi bi-arrow-return-right me-1"></i>'
                            . htmlspecialchars($page->parent->title) .
                            '</span>';
                }

                // Show child count if it has children
                if ($page->children && $page->children->count() > 0) {
                    return '<span class="badge bg-info">
                            <i class="bi bi-diagram-3 me-1"></i>'
                            . $page->children->count() . ' child(ren)
                            </span>';
                }

                return '<span class="text-muted small">—</span>';
            })
            ->addColumn('status_badge', function($page) {
                $badges = [
                    'published' => '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Published</span>',
                    'draft' => '<span class="badge bg-warning"><i class="bi bi-pencil-square me-1"></i>Draft</span>',
                    'archived' => '<span class="badge bg-secondary"><i class="bi bi-archive me-1"></i>Archived</span>',
                ];

                return $badges[$page->status] ?? '<span class="badge bg-secondary">' . ucfirst($page->status) . '</span>';
            })
            ->addColumn('visibility_badge', function($page) {
                if ($page->visibility == 'public') {
                    return '<span class="badge bg-info"><i class="bi bi-eye me-1"></i>Public</span>';
                }
                return '<span class="badge bg-secondary"><i class="bi bi-eye-slash me-1"></i>Private</span>';
            })
            ->addColumn('navigation_badges', function($page) {
                $html = '<div class="d-flex gap-1">';

                if ($page->show_in_header) {
                    $html .= '<span class="badge bg-primary" title="In Header">
                             <i class="bi bi-layout-text-window-reverse"></i> Header
                             </span>';
                }

                if ($page->show_in_footer) {
                    $html .= '<span class="badge bg-dark" title="In Footer">
                             <i class="bi bi-layout-text-window"></i> Footer
                             </span>';
                }

                if (!$page->show_in_header && !$page->show_in_footer) {
                    $html .= '<span class="text-muted small">None</span>';
                }

                $html .= '</div>';

                return $html;
            })
            ->addColumn('template_badge', function($page) {
                $templates = [
                    'default' => 'Default',
                    'full-width' => 'Full Width',
                    'sidebar-left' => 'Sidebar Left',
                    'sidebar-right' => 'Sidebar Right',
                    'landing' => 'Landing',
                ];

                $templateName = $templates[$page->template] ?? ucfirst(str_replace('-', ' ', $page->template));

                return '<span class="badge bg-light text-dark">' . $templateName . '</span>';
            })
            ->addColumn('created_at_formatted', function($page) {
                $html = '<div class="created-at-container">';
                $html .= '<div>' . $page->created_at->format('M d, Y') . '</div>';
                $html .= '<small class="text-muted">' . $page->created_at->format('h:i A') . '</small>';

                if ($page->creator) {
                    $html .= '<div><small class="text-muted">by ' . htmlspecialchars($page->creator->name) . '</small></div>';
                }

                $html .= '<div><small class="text-muted">' . $page->created_at->diffForHumans() . '</small></div>';
                $html .= '</div>';

                return $html;
            })
            ->addColumn('updated_at_formatted', function($page) {
                if (!$page->updated_at || $page->updated_at->eq($page->created_at)) {
                    return '<span class="text-muted small">Never</span>';
                }

                $html = '<div class="updated-at-container">';
                $html .= '<div>' . $page->updated_at->format('M d, Y') . '</div>';
                $html .= '<small class="text-muted">' . $page->updated_at->format('h:i A') . '</small>';

                if ($page->updater) {
                    $html .= '<div><small class="text-muted">by ' . htmlspecialchars($page->updater->name) . '</small></div>';
                }

                $html .= '</div>';

                return $html;
            })
            ->addColumn('actions', function($page) {
                $actions = '<div class="btn-group" role="group">';

                // View button
                if (Auth::guard('admin')->user()->hasPermission('content.read')) {
                    $actions .= '<a href="' . route('admin.pages.show', $page->id) . '"
                                class="btn btn-sm btn-info" title="View">
                                <i class="bi bi-eye"></i>
                                </a>';
                }

                // Edit button
                if (Auth::guard('admin')->user()->hasPermission('content.update')) {
                    $actions .= '<a href="' . route('admin.pages.edit', $page->id) . '"
                                class="btn btn-sm btn-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                                </a>';
                }

                // Dropdown for more actions
                $actions .= '<div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-sm btn-secondary dropdown-toggle"
                                    data-bs-toggle="dropdown" title="More Actions">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">';

                // Status change actions
                if (Auth::guard('admin')->user()->hasPermission('content.update')) {
                    if ($page->status == 'draft') {
                        $actions .= '<li><button type="button" class="dropdown-item publish-page"
                                    data-id="' . $page->id . '" data-title="' . htmlspecialchars($page->title) . '">
                                    <i class="bi bi-check-circle text-success me-2"></i>Publish
                                    </button></li>';
                    }

                    if ($page->status == 'published') {
                        $actions .= '<li><button type="button" class="dropdown-item unpublish-page"
                                    data-id="' . $page->id . '" data-title="' . htmlspecialchars($page->title) . '">
                                    <i class="bi bi-pencil-square text-warning me-2"></i>Set to Draft
                                    </button></li>';
                    }

                    if ($page->status != 'archived') {
                        $actions .= '<li><button type="button" class="dropdown-item archive-page"
                                    data-id="' . $page->id . '" data-title="' . htmlspecialchars($page->title) . '">
                                    <i class="bi bi-archive text-secondary me-2"></i>Archive
                                    </button></li>';
                    }

                    $actions .= '<li><hr class="dropdown-divider"></li>';
                }

                // Duplicate
                if (Auth::guard('admin')->user()->hasPermission('content.create')) {
                    $actions .= '<li><button type="button" class="dropdown-item duplicate-page"
                                data-id="' . $page->id . '" data-title="' . htmlspecialchars($page->title) . '">
                                <i class="bi bi-copy text-info me-2"></i>Duplicate
                                </button></li>';
                }

                // Preview
                $actions .= '<li><a href="' . route('admin.pages.preview', $page->id) . '"
                            class="dropdown-item" target="_blank">
                            <i class="bi bi-eye text-primary me-2"></i>Preview
                            </a></li>';

                // Delete
                if (Auth::guard('admin')->user()->hasPermission('content.delete')) {
                    $actions .= '<li><hr class="dropdown-divider"></li>';
                    $actions .= '<li><button type="button" class="dropdown-item text-danger delete-page"
                                data-id="' . $page->id . '" data-title="' . htmlspecialchars($page->title) . '">
                                <i class="bi bi-trash me-2"></i>Delete
                                </button></li>';
                }

                $actions .= '</ul></div>';
                $actions .= '</div>';

                return $actions;
            })
            ->rawColumns([
                'checkbox',
                'page_info',
                'parent_badge',
                'status_badge',
                'visibility_badge',
                'navigation_badges',
                'template_badge',
                'created_at_formatted',
                'updated_at_formatted',
                'actions'
            ])
            ->make(true);
    }

    /**
     * Show the form for creating a new page.
     */
    public function create()
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.create')) {
            abort(403, 'Unauthorized action.');
        }

        // Get parent pages for dropdown
        $parentPages = Page::whereNull('parent_id')
                          ->orderBy('title')
                          ->get();

        // Available templates
        $templates = [
            'default' => 'Default Template',
            'full-width' => 'Full Width',
            'sidebar-left' => 'Sidebar Left',
            'sidebar-right' => 'Sidebar Right',
            'landing' => 'Landing Page',
        ];

        return view('admin.pages.create', compact('parentPages', 'templates'));
    }

    /**
     * Store a newly created page in storage.
     */
    public function store(Request $request)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.create')) {
            abort(403, 'Unauthorized action.');
        }

        // Validate request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'featured_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'featured_image_alt' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived',
            'visibility' => 'required|in:public,private',
            'template' => 'required|string|max:50',
            'display_order' => 'nullable|integer|min:0',
            'show_in_header' => 'nullable|boolean',
            'show_in_footer' => 'nullable|boolean',
            'menu_label' => 'nullable|string|max:100',
            'custom_css_class' => 'nullable|string|max:255',
            'custom_js' => 'nullable|string',
            'custom_css' => 'nullable|string',
            'parent_id' => 'nullable|exists:pages,id',
            'published_at' => 'nullable|date',
        ]);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $image = $request->file('featured_image');
            $imageName = time() . '_' . Str::slug($request->title) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('pages/images', $imageName, 'public');
            $validated['featured_image'] = $imagePath;
        }

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Ensure unique slug
        $originalSlug = $validated['slug'];
        $count = 1;
        while (Page::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $count;
            $count++;
        }

        // Set checkboxes to false if not present
        $validated['show_in_header'] = $request->has('show_in_header');
        $validated['show_in_footer'] = $request->has('show_in_footer');

        // Set default display order if not provided
        if (empty($validated['display_order'])) {
            $validated['display_order'] = Page::max('display_order') + 1;
        }

        // Set published_at if status is published and not set
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        // Set created_by
        $validated['created_by'] = Auth::guard('admin')->id();

        // Create page
        $page = Page::create($validated);

        // Log activity

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Page created successfully!');
    }

    /**
     * Display the specified page.
     */
    public function show(Page $page)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.read')) {
            abort(403, 'Unauthorized action.');
        }

        $page->load(['creator', 'updater', 'parent', 'children']);

        return view('admin.pages.show', compact('page'));
    }

    /**
     * Show the form for editing the specified page.
     */
    public function edit(Page $page)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.update')) {
            abort(403, 'Unauthorized action.');
        }

        // Get parent pages for dropdown (exclude current page and its children)
        $parentPages = Page::whereNull('parent_id')
                          ->where('id', '!=', $page->id)
                          ->orderBy('title')
                          ->get();

        // Available templates
        $templates = [
            'default' => 'Default Template',
            'full-width' => 'Full Width',
            'sidebar-left' => 'Sidebar Left',
            'sidebar-right' => 'Sidebar Right',
            'landing' => 'Landing Page',
        ];

        return view('admin.pages.edit', compact('page', 'parentPages', 'templates'));
    }

    /**
     * Update the specified page in storage.
     */
    public function update(Request $request, Page $page)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.update')) {
            abort(403, 'Unauthorized action.');
        }

        // Validate request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('pages', 'slug')->ignore($page->id)],
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'featured_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'featured_image_alt' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived',
            'visibility' => 'required|in:public,private',
            'template' => 'required|string|max:50',
            'display_order' => 'nullable|integer|min:0',
            'show_in_header' => 'nullable|boolean',
            'show_in_footer' => 'nullable|boolean',
            'menu_label' => 'nullable|string|max:100',
            'custom_css_class' => 'nullable|string|max:255',
            'custom_js' => 'nullable|string',
            'custom_css' => 'nullable|string',
            'parent_id' => 'nullable|exists:pages,id',
            'published_at' => 'nullable|date',
            'remove_image' => 'nullable|boolean',
        ]);

        // Handle image removal
        if ($request->has('remove_image') && $request->remove_image) {
            if ($page->featured_image && Storage::disk('public')->exists($page->featured_image)) {
                Storage::disk('public')->delete($page->featured_image);
            }
            $validated['featured_image'] = null;
            $validated['featured_image_alt'] = null;
        }

        // Handle new featured image upload
        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($page->featured_image && Storage::disk('public')->exists($page->featured_image)) {
                Storage::disk('public')->delete($page->featured_image);
            }

            $image = $request->file('featured_image');
            $imageName = time() . '_' . Str::slug($request->title) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('pages/images', $imageName, 'public');
            $validated['featured_image'] = $imagePath;
        }

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Ensure unique slug (excluding current page)
        if ($validated['slug'] !== $page->slug) {
            $originalSlug = $validated['slug'];
            $count = 1;
            while (Page::where('slug', $validated['slug'])->where('id', '!=', $page->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $count;
                $count++;
            }
        }

        // Set checkboxes to false if not present
        $validated['show_in_header'] = $request->has('show_in_header');
        $validated['show_in_footer'] = $request->has('show_in_footer');

        // Set published_at if status changed to published and not set
        if ($validated['status'] === 'published' && $page->status !== 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        // Set updated_by
        $validated['updated_by'] = Auth::guard('admin')->id();

        // Update page
        $page->update($validated);

        // Log activity


        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Page updated successfully!');
    }

    /**
     * Remove the specified page from storage.
     */
    public function destroy(Page $page)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.delete')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if page has children
        if ($page->hasChildren()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete page with child pages. Please delete or reassign child pages first.'
            ], 400);
        }

        // Delete featured image
        if ($page->featured_image && Storage::disk('public')->exists($page->featured_image)) {
            Storage::disk('public')->delete($page->featured_image);
        }

        // Delete page
        $page->delete();

        return response()->json([
            'success' => true,
            'message' => 'Page deleted successfully!'
        ]);
    }

    /**
     * Bulk delete pages.
     */
    public function bulkDelete(Request $request)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.delete')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'page_ids' => 'required|array',
            'page_ids.*' => 'exists:pages,id',
        ]);

        $pages = Page::whereIn('id', $request->page_ids)->get();
        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($pages as $page) {
            // Skip if has children
            if ($page->hasChildren()) {
                $skippedCount++;
                continue;
            }

            // Delete featured image
            if ($page->featured_image && Storage::disk('public')->exists($page->featured_image)) {
                Storage::disk('public')->delete($page->featured_image);
            }

            $page->delete();
            $deletedCount++;
        }

        $message = "{$deletedCount} page(s) deleted successfully!";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} page(s) skipped (has child pages).";
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Publish a page.
     */
    public function publish(Page $page)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $page->publish();

        return response()->json([
            'success' => true,
            'message' => 'Page published successfully!'
        ]);
    }

    /**
     * Unpublish a page (set to draft).
     */
    public function unpublish(Page $page)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $page->unpublish();


        return response()->json([
            'success' => true,
            'message' => 'Page unpublished successfully!'
        ]);
    }

    /**
     * Archive a page.
     */
    public function archive(Page $page)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $page->archive();

        return response()->json([
            'success' => true,
            'message' => 'Page archived successfully!'
        ]);
    }

    /**
     * Duplicate a page.
     */
    public function duplicate(Page $page)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.create')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Create duplicate
        $duplicate = $page->replicate();
        $duplicate->title = $page->title . ' (Copy)';
        $duplicate->slug = $page->slug . '-copy-' . time();
        $duplicate->status = 'draft';
        $duplicate->published_at = null;
        $duplicate->created_by = Auth::guard('admin')->id();
        $duplicate->updated_by = null;
        $duplicate->save();

        // Copy featured image if exists
        if ($page->featured_image && Storage::disk('public')->exists($page->featured_image)) {
            $extension = pathinfo($page->featured_image, PATHINFO_EXTENSION);
            $newImageName = time() . '_' . Str::slug($duplicate->title) . '.' . $extension;
            $newImagePath = 'pages/images/' . $newImageName;

            Storage::disk('public')->copy($page->featured_image, $newImagePath);
            $duplicate->featured_image = $newImagePath;
            $duplicate->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Page duplicated successfully!',
            'redirect_url' => route('admin.pages.edit', $duplicate)
        ]);
    }

    /**
     * Update display order.
     */
    public function updateOrder(Request $request)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'required|integer',
        ]);

        foreach ($request->orders as $id => $order) {
            Page::where('id', $id)->update(['display_order' => $order]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Display order updated successfully!'
        ]);
    }

    /**
     * Preview page.
     */
    public function preview(Page $page)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.read')) {
            abort(403, 'Unauthorized action.');
        }

        return view('admin.pages.preview', compact('page'));
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(Request $request)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'page_ids' => 'required|array',
            'page_ids.*' => 'exists:pages,id',
            'status' => 'required|in:draft,published,archived',
        ]);

        Page::whereIn('id', $request->page_ids)
            ->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated for ' . count($request->page_ids) . ' page(s)!'
        ]);
    }
}

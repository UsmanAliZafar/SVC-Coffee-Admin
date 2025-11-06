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

        $query = Page::with(['creator', 'parent']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by visibility
        if ($request->filled('visibility')) {
            $query->where('visibility', $request->visibility);
        }

        // Filter by parent/child
        if ($request->filled('parent_filter')) {
            if ($request->parent_filter === 'parent') {
                $query->whereNull('parent_id');
            } elseif ($request->parent_filter === 'child') {
                $query->whereNotNull('parent_id');
            }
        }

        // Filter by navigation
        if ($request->filled('navigation')) {
            if ($request->navigation === 'header') {
                $query->where('show_in_header', true);
            } elseif ($request->navigation === 'footer') {
                $query->where('show_in_footer', true);
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSorts = ['title', 'created_at', 'updated_at', 'display_order', 'status'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $pages = $query->paginate(15)->withQueryString();

        // Get statistics
        $stats = [
            'total' => Page::count(),
            'published' => Page::where('status', 'published')->count(),
            'draft' => Page::where('status', 'draft')->count(),
            'archived' => Page::where('status', 'archived')->count(),
        ];

        return view('admin.pages.index', compact('pages', 'stats'));
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
        activity()
            ->causedBy(Auth::guard('admin')->user())
            ->performedOn($page)
            ->log('Created page: ' . $page->title);

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
        activity()
            ->causedBy(Auth::guard('admin')->user())
            ->performedOn($page)
            ->log('Updated page: ' . $page->title);

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
            abort(403, 'Unauthorized action.');
        }

        // Check if page has children
        if ($page->hasChildren()) {
            return redirect()
                ->route('admin.pages.index')
                ->with('error', 'Cannot delete page with child pages. Please delete or reassign child pages first.');
        }

        // Delete featured image
        if ($page->featured_image && Storage::disk('public')->exists($page->featured_image)) {
            Storage::disk('public')->delete($page->featured_image);
        }

        // Log activity before deletion
        activity()
            ->causedBy(Auth::guard('admin')->user())
            ->performedOn($page)
            ->log('Deleted page: ' . $page->title);

        // Delete page
        $page->delete();

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Page deleted successfully!');
    }

    /**
     * Bulk delete pages.
     */
    public function bulkDelete(Request $request)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'page_ids' => 'required|array',
            'page_ids.*' => 'exists:pages,id',
        ]);

        $pages = Page::whereIn('id', $request->page_ids)->get();

        foreach ($pages as $page) {
            // Skip if has children
            if ($page->hasChildren()) {
                continue;
            }

            // Delete featured image
            if ($page->featured_image && Storage::disk('public')->exists($page->featured_image)) {
                Storage::disk('public')->delete($page->featured_image);
            }

            // Log activity
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->performedOn($page)
                ->log('Bulk deleted page: ' . $page->title);

            $page->delete();
        }

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Selected pages deleted successfully!');
    }

    /**
     * Publish a page.
     */
    public function publish(Page $page)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.update')) {
            abort(403, 'Unauthorized action.');
        }

        $page->publish();

        // Log activity
        activity()
            ->causedBy(Auth::guard('admin')->user())
            ->performedOn($page)
            ->log('Published page: ' . $page->title);

        return redirect()
            ->back()
            ->with('success', 'Page published successfully!');
    }

    /**
     * Unpublish a page (set to draft).
     */
    public function unpublish(Page $page)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.update')) {
            abort(403, 'Unauthorized action.');
        }

        $page->unpublish();

        // Log activity
        activity()
            ->causedBy(Auth::guard('admin')->user())
            ->performedOn($page)
            ->log('Unpublished page: ' . $page->title);

        return redirect()
            ->back()
            ->with('success', 'Page unpublished successfully!');
    }

    /**
     * Archive a page.
     */
    public function archive(Page $page)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.update')) {
            abort(403, 'Unauthorized action.');
        }

        $page->archive();

        // Log activity
        activity()
            ->causedBy(Auth::guard('admin')->user())
            ->performedOn($page)
            ->log('Archived page: ' . $page->title);

        return redirect()
            ->back()
            ->with('success', 'Page archived successfully!');
    }

    /**
     * Duplicate a page.
     */
    public function duplicate(Page $page)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.create')) {
            abort(403, 'Unauthorized action.');
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

        // Log activity
        activity()
            ->causedBy(Auth::guard('admin')->user())
            ->performedOn($duplicate)
            ->log('Duplicated page: ' . $page->title);

        return redirect()
            ->route('admin.pages.edit', $duplicate)
            ->with('success', 'Page duplicated successfully!');
    }

    /**
     * Update display order.
     */
    public function updateOrder(Request $request)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('content.update')) {
            abort(403, 'Unauthorized action.');
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
}

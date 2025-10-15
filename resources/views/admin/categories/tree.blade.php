@extends('admin.layouts.app')

@section('title', 'Category Tree View')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.15/themes/default/style.min.css">
<style>
    .tree-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .tree-header {
        color: #5B914C;
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #5B914C;
    }
    .jstree-default .jstree-clicked {
        background: #5B914C !important;
        border-color: #5B914C !important;
    }
    .jstree-default .jstree-hovered {
        background: #e8f5e9 !important;
        border-color: #5B914C !important;
    }
    .category-node {
        display: flex;
        align-items: center;
        padding: 5px 0;
    }
    .category-icon {
        margin-right: 8px;
    }
    .category-badge {
        margin-left: 10px;
        font-size: 0.75rem;
    }
    .tree-legend {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .legend-item {
        display: inline-block;
        margin-right: 20px;
        margin-bottom: 5px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-diagram-3"></i> Category Tree View</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categories</a></li>
                    <li class="breadcrumb-item active">Tree View</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth('admin')->user()->hasPermission('categories.create'))
            <a href="{{ route('admin.categories.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Add Category
            </a>
            @endif
            <button type="button" class="btn btn-primary" id="expandAll">
                <i class="bi bi-arrows-expand"></i> Expand All
            </button>
            <button type="button" class="btn btn-primary" id="collapseAll">
                <i class="bi bi-arrows-collapse"></i> Collapse All
            </button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list"></i> List View
            </a>
        </div>
    </div>

    <!-- Legend -->
    <div class="tree-legend">
        <strong><i class="bi bi-info-circle"></i> Legend:</strong>
        <div class="mt-2">
            <span class="legend-item">
                <i class="bi bi-folder-fill text-warning"></i> Root Category
            </span>
            <span class="legend-item">
                <i class="bi bi-folder text-warning"></i> Subcategory
            </span>
            <span class="legend-item">
                <span class="badge bg-success">Active</span> Active Status
            </span>
            <span class="legend-item">
                <span class="badge bg-secondary">Inactive</span> Inactive Status
            </span>
            <span class="legend-item">
                <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i></span> Featured
            </span>
            <span class="legend-item">
                <span class="badge bg-info">0</span> Product Count
            </span>
        </div>
    </div>

    <!-- Category Tree -->
    <div class="tree-card">
        <h3 class="tree-header"><i class="bi bi-diagram-3"></i> Category Hierarchy</h3>
        <div id="categoryTree"></div>
    </div>

    <!-- Context Menu (Hidden) -->
    <div id="contextMenu" class="dropdown-menu" style="display: none; position: absolute;">
        <a class="dropdown-item" href="#" id="viewCategory"><i class="bi bi-eye"></i> View</a>
        <a class="dropdown-item" href="#" id="editCategory"><i class="bi bi-pencil"></i> Edit</a>
        <a class="dropdown-item" href="#" id="addSubcategory"><i class="bi bi-plus"></i> Add Subcategory</a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-danger" href="#" id="deleteCategory"><i class="bi bi-trash"></i> Delete</a>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.15/jstree.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    let selectedCategoryId = null;

    // Build tree data from categories
    function buildTreeData(categories, parentId = null) {
        let treeData = [];

        categories.forEach(function(category) {
            if (category.parent_id === parentId) {
                let badges = '';

                // Status badge
                if (category.status_key_code === 'CATEGORY_ACTIVE') {
                    badges += '<span class="badge bg-success category-badge">Active</span>';
                } else {
                    badges += '<span class="badge bg-secondary category-badge">Inactive</span>';
                }

                // Featured badge
                if (category.is_featured) {
                    badges += '<span class="badge bg-warning text-dark category-badge"><i class="bi bi-star-fill"></i></span>';
                }

                // Product count badge
                badges += '<span class="badge bg-info category-badge">' + category.products_count + ' products</span>';

                let node = {
                    id: category.id,
                    text: category.title + ' ' + badges,
                    icon: category.parent_id === null ? 'bi bi-folder-fill text-warning' : 'bi bi-folder text-warning',
                    data: category,
                    children: buildTreeData(categories, category.id)
                };

                treeData.push(node);
            }
        });

        return treeData;
    }

    // Initialize jsTree
    $.ajax({
        url: '{{ route("admin.categories.data") }}',
        data: { length: -1 },
        success: function(response) {
            const treeData = buildTreeData(response.data);

            $('#categoryTree').jstree({
                core: {
                    data: treeData,
                    check_callback: true,
                    themes: {
                        responsive: true,
                        dots: true
                    }
                },
                plugins: ['contextmenu', 'dnd', 'search', 'types'],
                types: {
                    default: {
                        icon: 'bi bi-folder'
                    }
                },
                contextmenu: {
                    items: function(node) {
                        return {
                            view: {
                                label: "View Details",
                                icon: "bi bi-eye",
                                action: function() {
                                    window.location.href = '{{ route("admin.categories.show", ":id") }}'.replace(':id', node.id);
                                }
                            },
                            edit: {
                                label: "Edit",
                                icon: "bi bi-pencil",
                                action: function() {
                                    window.location.href = '{{ route("admin.categories.edit", ":id") }}'.replace(':id', node.id);
                                }
                            },
                            create: {
                                label: "Add Subcategory",
                                icon: "bi bi-plus",
                                action: function() {
                                    window.location.href = '{{ route("admin.categories.create") }}?parent_id=' + node.id;
                                }
                            },
                            remove: {
                                label: "Delete",
                                icon: "bi bi-trash",
                                action: function() {
                                    deleteCategory(node.id);
                                }
                            }
                        };
                    }
                }
            });

            // Handle node selection
            $('#categoryTree').on('select_node.jstree', function(e, data) {
                selectedCategoryId = data.node.id;
            });
        }
    });

    // Expand all nodes
    $('#expandAll').on('click', function() {
        $('#categoryTree').jstree('open_all');
    });

    // Collapse all nodes
    $('#collapseAll').on('click', function() {
        $('#categoryTree').jstree('close_all');
    });

    // Delete category function
    function deleteCategory(categoryId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.categories.destroy", ":id") }}'.replace(':id', categoryId),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete category', 'error');
                    }
                });
            }
        });
    }
});
</script>
@endpush

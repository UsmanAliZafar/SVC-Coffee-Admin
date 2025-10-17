@extends('admin.layouts.app')

@section('title', 'Categories Management')

@push('styles')
<style>
    .filter-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .filter-card .form-label {
        font-weight: 600;
        font-size: 0.875rem;
        color: #5B914C;
    }
    .btn-filter {
        background-color: #5B914C;
        border-color: #5B914C;
        color: white;
    }
    .btn-filter:hover {
        background-color: #4a7a3d;
        border-color: #4a7a3d;
    }
    .category-stats {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .stat-item {
        text-align: center;
    }
    .stat-item .stat-value {
        font-size: 2rem;
        font-weight: bold;
    }
    .stat-item .stat-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-tags"></i> Categories Management</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Categories</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth('admin')->user()->hasPermission('categories.create'))
            <a href="{{ route('admin.categories.create') }}" class="btn btn-filter">
                <i class="bi bi-plus-circle"></i> Add Category
            </a>
            @endif
            <a href="{{ route('admin.categories.tree') }}" class="btn btn-outline-secondary">
                <i class="bi bi-diagram-3"></i> Tree View
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="category-stats">
        <div class="row">
            <div class="col-md-3 stat-item">
                <div class="stat-value" id="totalCategories">0</div>
                <div class="stat-label">Total Categories</div>
            </div>
            <div class="col-md-3 stat-item">
                <div class="stat-value" id="activeCategories">0</div>
                <div class="stat-label">Active</div>
            </div>
            <div class="col-md-3 stat-item">
                <div class="stat-value" id="featuredCategories">0</div>
                <div class="stat-label">Featured</div>
            </div>
            <div class="col-md-3 stat-item">
                <div class="stat-value" id="emptyCategories">0</div>
                <div class="stat-label">Empty</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card filter-card">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" id="searchFilter" class="form-control" placeholder="Search categories...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach($statusList as $status)
                        <option value="{{ $status->key_code }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Parent Category</label>
                <select id="parentFilter" class="form-select">
                    <option value="">All</option>
                    <option value="root">Root Categories Only</option>
                    @foreach($parentCategories as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->indent }}{{ $parent->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Featured</label>
                <select id="featuredFilter" class="form-select">
                    <option value="">All</option>
                    <option value="1">Featured Only</option>
                    <option value="0">Non-Featured</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Show in Menu</label>
                <select id="menuFilter" class="form-select">
                    <option value="">All</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" id="resetFilters" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- DataTable Card -->
    <div class="card shadow-sm">
        <div class="card-body">
            <!-- Bulk Actions -->
            <div class="mb-3 d-none" id="bulkActionsBar">
                <div class="alert alert-info d-flex justify-content-between align-items-center mb-0">
                    <span><strong id="selectedCount">0</strong> categories selected</span>
                    <div>
                        @if(auth('admin')->user()->hasPermission('categories.delete'))
                        <button type="button" class="btn btn-sm btn-danger" id="bulkDelete">
                            <i class="bi bi-trash"></i> Delete Selected
                        </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-secondary" id="deselectAll">
                            <i class="bi bi-x"></i> Deselect All
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table id="categoriesTable" class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th width="60">Image</th>
                            <th>Category Name</th>
                            <th>Parent</th>
                            <th width="80">Products</th>
                            <th width="100">Status</th>
                            <th>Display Settings</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </card>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    let table = $('#categoriesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.categories.data") }}',
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.parent_id = $('#parentFilter').val();
                d.is_featured = $('#featuredFilter').val();
                d.show_in_menu = $('#menuFilter').val();
                d.search = $('#searchFilter').val();
            }
        },
        columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
            { data: 'image_preview', name: 'image_preview', orderable: false, searchable: false },
            { data: 'title_link', name: 'title' },
            { data: 'parent_name', name: 'parent.title' },
            { data: 'products_count_badge', name: 'products_count' },
            { data: 'status_badge', name: 'status_key_code' },
            { data: 'badges', name: 'badges', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[2, 'asc']],
        pageLength: 25,
        responsive: true,
        language: {
            processing: `
                <div class="datatable-loading-container">
                    <div class="bars-loader">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <div class="datatable-loading-text">Loading Products Categories...</div>
                </div>
            `
        },
        drawCallback: function() {
            updateStatistics();
        }
    });

    // Filter change events
    $('#statusFilter, #parentFilter, #featuredFilter, #menuFilter').on('change', function() {
        table.draw();
    });

    // Search with delay
    let searchDelay;
    $('#searchFilter').on('keyup', function() {
        clearTimeout(searchDelay);
        searchDelay = setTimeout(function() {
            table.draw();
        }, 500);
    });

    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#searchFilter').val('');
        $('#statusFilter').val('');
        $('#parentFilter').val('');
        $('#featuredFilter').val('');
        $('#menuFilter').val('');
        table.draw();
    });

    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.category-checkbox').prop('checked', this.checked);
        updateBulkActions();
    });

    // Individual checkbox
    $(document).on('change', '.category-checkbox', function() {
        updateBulkActions();
    });

    // Update bulk actions visibility
    function updateBulkActions() {
        const selectedCount = $('.category-checkbox:checked').length;
        $('#selectedCount').text(selectedCount);

        if (selectedCount > 0) {
            $('#bulkActionsBar').removeClass('d-none');
        } else {
            $('#bulkActionsBar').addClass('d-none');
        }
    }

    // Deselect all
    $('#deselectAll').on('click', function() {
        $('.category-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        updateBulkActions();
    });

    // Delete category
    $(document).on('click', '.delete-category', function() {
        const categoryId = $(this).data('id');

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
                            Swal.fire('Deleted!', response.message, 'success');
                            table.draw();
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
    });

    // Bulk delete
    $('#bulkDelete').on('click', function() {
        const selectedIds = $('.category-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        Swal.fire({
            title: 'Delete ' + selectedIds.length + ' categories?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete them!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Process bulk delete
                let completed = 0;
                let failed = 0;

                selectedIds.forEach(function(id) {
                    $.ajax({
                        url: '{{ route("admin.categories.destroy", ":id") }}'.replace(':id', id),
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function() { completed++; },
                        error: function() { failed++; },
                        complete: function() {
                            if (completed + failed === selectedIds.length) {
                                table.draw();
                                $('#deselectAll').click();
                                Swal.fire({
                                    title: 'Bulk Delete Complete',
                                    html: `Deleted: ${completed}<br>Failed: ${failed}`,
                                    icon: failed > 0 ? 'warning' : 'success'
                                });
                            }
                        }
                    });
                });
            }
        });
    });

    // Update statistics
    function updateStatistics() {
        $.ajax({
            url: '{{ route("admin.categories.data") }}',
            data: { length: -1 },
            success: function(response) {
                const data = response.data;
                $('#totalCategories').text(data.length);

                const active = data.filter(cat => cat.status_key_code === 'CATEGORY_ACTIVE').length;
                $('#activeCategories').text(active);

                const featured = data.filter(cat => cat.is_featured).length;
                $('#featuredCategories').text(featured);

                const empty = data.filter(cat => cat.products_count === 0).length;
                $('#emptyCategories').text(empty);
            }
        });
    }

    // Initial statistics load
    updateStatistics();
});
// View product image in modal
function viewCategoryImage(imageUrl, categoryTitle) {
    Swal.fire({
        title: categoryTitle,
        imageUrl: imageUrl,
        imageAlt: categoryTitle,
        imageWidth: 600,
        imageHeight: 600,
        showCloseButton: true,
        showConfirmButton: false,
        backdrop: 'rgba(0,0,0,0.8)',
        customClass: {
            image: 'rounded',
            popup: 'border-0'
        }
    });
}
</script>
@endpush

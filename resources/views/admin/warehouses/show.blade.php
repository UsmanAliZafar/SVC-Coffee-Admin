@extends('admin.layouts.app')
{{-- resources/views/admin/warehouses/show.blade.php --}}
@section('title', 'Warehouse Details - ' . $warehouse->name)

@push('styles')
<style>
    .warehouse-header {
        background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 12px rgba(91, 145, 76, 0.2);
    }

    .warehouse-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .warehouse-code {
        background: rgba(255, 255, 255, 0.2);
        padding: 5px 15px;
        border-radius: 20px;
        display: inline-block;
        font-family: 'Courier New', monospace;
        font-weight: 600;
    }

    .info-card {
        background: white;
        border-radius: 8px;
        padding: 25px;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
        transition: all 0.3s;
    }

    .info-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-color: #5B914C;
    }

    .info-card-title {
        color: #5B914C;
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #5B914C;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .stat-box {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        border: 2px solid #e0e0e0;
        transition: all 0.3s;
        height: 100%;
    }

    .stat-box:hover {
        transform: translateY(-3px);
        border-color: #5B914C;
        box-shadow: 0 4px 12px rgba(91, 145, 76, 0.15);
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: bold;
        color: #5B914C;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .info-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #495057;
        width: 180px;
        flex-shrink: 0;
    }

    .info-value {
        color: #6c757d;
        flex: 1;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .action-buttons .btn {
        flex: 1;
        min-width: 150px;
    }

    .priority-indicator {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: #f8f9fa;
        padding: 8px 15px;
        border-radius: 20px;
        border: 2px solid #e0e0e0;
    }

    .priority-bar {
        width: 100px;
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }

    .priority-fill {
        height: 100%;
        background: linear-gradient(90deg, #5B914C 0%, #4a7a3d 100%);
        transition: width 0.3s;
    }

    .alert-card {
        background: #fff3cd;
        border: 2px solid #ffc107;
        border-radius: 8px;
        padding: 15px 20px;
        margin-bottom: 20px;
    }

    .alert-card.danger {
        background: #f8d7da;
        border-color: #dc3545;
    }

    .alert-card.success {
        background: #d1e7dd;
        border-color: #198754;
    }

    .stock-table {
        background: white;
        border-radius: 8px;
        overflow: hidden;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 20px;
    }

    .badge-xl {
        font-size: 1rem;
        padding: 8px 16px;
    }

    .contact-info {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 6px;
        margin-bottom: 10px;
    }

    .contact-info i {
        color: #5B914C;
        font-size: 1.2rem;
    }

    .tab-content-section {
        background: white;
        border-radius: 8px;
        padding: 25px;
        border: 1px solid #e0e0e0;
        margin-top: 20px;
    }

    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 3px solid transparent;
        font-weight: 600;
    }

    .nav-tabs .nav-link:hover {
        border-color: #5B914C;
        color: #5B914C;
    }

    .nav-tabs .nav-link.active {
        color: #5B914C;
        border-bottom-color: #5B914C;
        background: transparent;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="warehouse-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1 class="warehouse-title">
                    <i class="bi bi-building"></i> {{ $warehouse->name }}
                </h1>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <span class="warehouse-code">{{ $warehouse->code }}</span>

                    @if($warehouse->is_active)
                        <span class="badge bg-success badge-xl">Active</span>
                    @else
                        <span class="badge bg-secondary badge-xl">Inactive</span>
                    @endif

                    @if($warehouse->is_default)
                        <span class="badge bg-light text-dark badge-xl">
                            <i class="bi bi-star-fill text-warning"></i> Default Warehouse
                        </span>
                    @endif
                </div>
            </div>

            <div class="action-buttons">
                @if(auth('admin')->user()->hasPermission('inventory.update'))
                <a href="{{ route('admin.warehouses.edit', $warehouse->id) }}" class="btn btn-light">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <button type="button" class="btn btn-light" onclick="toggleStatus()">
                    <i class="bi bi-toggle-{{ $warehouse->is_active ? 'on' : 'off' }}"></i>
                    {{ $warehouse->is_active ? 'Deactivate' : 'Activate' }}
                </button>
                @endif

                <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Cards -->
    @php
        $outOfStock = $warehouse->getOutOfStockCount();
        $lowStock = $warehouse->getLowStockCount();
    @endphp

    @if($outOfStock > 0)
    <div class="alert-card danger">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <strong>Out of Stock Alert:</strong> This warehouse has <strong>{{ $outOfStock }}</strong> product(s) out of stock.
        <a href="#stock-tab" class="btn btn-sm btn-danger ms-2" onclick="$('#stock-tab').tab('show')">View Stock</a>
    </div>
    @endif

    @if($lowStock > 0)
    <div class="alert-card">
        <i class="bi bi-exclamation-circle-fill"></i>
        <strong>Low Stock Warning:</strong> This warehouse has <strong>{{ $lowStock }}</strong> product(s) with low stock.
        <a href="#stock-tab" class="btn btn-sm btn-warning ms-2" onclick="$('#stock-tab').tab('show')">View Stock</a>
    </div>
    @endif

    @if($outOfStock === 0 && $lowStock === 0 && $warehouse->hasStock())
    <div class="alert-card success">
        <i class="bi bi-check-circle-fill"></i>
        <strong>All Good:</strong> All products in this warehouse have adequate stock levels.
    </div>
    @endif

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-box">
                <div class="stat-value">{{ number_format($warehouse->getTotalStock()) }}</div>
                <div class="stat-label">Total Stock Units</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-box">
                <div class="stat-value text-success">{{ store_currency_symbol() }}{{ number_format($warehouse->getTotalStockValue(), 2) }}</div>
                <div class="stat-label">Total Stock Value</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-box">
                <div class="stat-value text-primary">{{ $warehouse->stock()->count() }}</div>
                <div class="stat-label">Total Products</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-box">
                <div class="stat-value text-info">{{ $warehouse->movements()->recent(30)->count() }}</div>
                <div class="stat-label">Movements (30 days)</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab"
                            data-bs-target="#overview" type="button" role="tab">
                        <i class="bi bi-info-circle"></i> Overview
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="stock-tab" data-bs-toggle="tab"
                            data-bs-target="#stock" type="button" role="tab">
                        <i class="bi bi-box-seam"></i> Stock Inventory
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="movements-tab" data-bs-toggle="tab"
                            data-bs-target="#movements" type="button" role="tab">
                        <i class="bi bi-arrow-left-right"></i> Recent Movements
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Overview Tab -->
                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    <div class="tab-content-section">
                        <h5 class="info-card-title">
                            <i class="bi bi-info-circle"></i> Warehouse Information
                        </h5>

                        <div class="info-row">
                            <div class="info-label">Warehouse Name:</div>
                            <div class="info-value">{{ $warehouse->name }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Warehouse Code:</div>
                            <div class="info-value">
                                <code>{{ $warehouse->code }}</code>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Status:</div>
                            <div class="info-value">
                                {!! $warehouse->getStatusBadge() !!}
                                @if($warehouse->is_default)
                                    <span class="badge bg-primary ms-2">Default</span>
                                @endif
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Priority Level:</div>
                            <div class="info-value">
                                <div class="priority-indicator">
                                    <span class="fw-bold">{{ $warehouse->priority }}</span>
                                    <div class="priority-bar">
                                        <div class="priority-fill" style="width: {{ $warehouse->priority }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($warehouse->address || $warehouse->city || $warehouse->state || $warehouse->country)
                        <div class="info-row">
                            <div class="info-label">Location:</div>
                            <div class="info-value">
                                <i class="bi bi-geo-alt text-danger"></i>
                                {{ $warehouse->getFullAddress() }}
                            </div>
                        </div>
                        @endif

                        @if($warehouse->created_at)
                        <div class="info-row">
                            <div class="info-label">Created:</div>
                            <div class="info-value">
                                {{ $warehouse->created_at->format('M d, Y h:i A') }}
                                <small class="text-muted">({{ $warehouse->created_at->diffForHumans() }})</small>
                            </div>
                        </div>
                        @endif

                        @if($warehouse->updated_at && $warehouse->updated_at != $warehouse->created_at)
                        <div class="info-row">
                            <div class="info-label">Last Updated:</div>
                            <div class="info-value">
                                {{ $warehouse->updated_at->format('M d, Y h:i A') }}
                                <small class="text-muted">({{ $warehouse->updated_at->diffForHumans() }})</small>
                            </div>
                        </div>
                        @endif

                        @if($warehouse->notes)
                        <div class="info-row">
                            <div class="info-label">Notes:</div>
                            <div class="info-value">{{ $warehouse->notes }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Stock Inventory Tab -->
                <div class="tab-pane fade" id="stock" role="tabpanel">
                    <div class="tab-content-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="info-card-title mb-0">
                                <i class="bi bi-box-seam"></i> Stock Inventory
                            </h5>
                            <a href="{{ route('admin.warehouses.export-stock', $warehouse->id) }}"
                               class="btn btn-outline-success btn-sm">
                                <i class="bi bi-download"></i> Export CSV
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table id="stockTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Available</th>
                                        <th>Reserved</th>
                                        <th>Location</th>
                                        <th>Value</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Movements Tab -->
                <div class="tab-pane fade" id="movements" role="tabpanel">
                    <div class="tab-content-section">
                        <h5 class="info-card-title">
                            <i class="bi bi-arrow-left-right"></i> Recent Movements (Last 30 Days)
                        </h5>

                        <div class="table-responsive">
                            <table id="movementsTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Product</th>
                                        <th>Type</th>
                                        <th>Quantity</th>
                                        <th>From/To</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($warehouse->movements()->recent(30)->get() as $movement)
                                    <tr>
                                        <td>{{ $movement->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('admin.products.show', $movement->product_id) }}">
                                                {{ $movement->product->name ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td>{!! $movement->getTypeBadge() !!}</td>
                                        <td>
                                            <span class="fw-bold {{ $movement->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $movement->getFormattedQuantity() }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($movement->type === 'transfer')
                                                {{ $movement->fromWarehouse->name ?? 'N/A' }}
                                                <i class="bi bi-arrow-right"></i>
                                                {{ $movement->toWarehouse->name ?? 'N/A' }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($movement->reason ?? 'N/A', 30) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                            <p class="mt-2">No movements found in the last 30 days</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Contact Information -->
            @if($warehouse->email || $warehouse->phone)
            <div class="info-card">
                <h5 class="info-card-title">
                    <i class="bi bi-telephone"></i> Contact Information
                </h5>

                @if($warehouse->email)
                <div class="contact-info">
                    <i class="bi bi-envelope"></i>
                    <div>
                        <small class="text-muted d-block">Email</small>
                        <a href="mailto:{{ $warehouse->email }}">{{ $warehouse->email }}</a>
                    </div>
                </div>
                @endif

                @if($warehouse->phone)
                <div class="contact-info">
                    <i class="bi bi-telephone"></i>
                    <div>
                        <small class="text-muted d-block">Phone</small>
                        <a href="tel:{{ $warehouse->phone }}">{{ $warehouse->phone }}</a>
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Quick Actions -->
            @if(auth('admin')->user()->hasPermission('inventory.update'))
            <div class="info-card">
                <h5 class="info-card-title">
                    <i class="bi bi-lightning"></i> Quick Actions
                </h5>

                <div class="d-grid gap-2">
                    <a href="{{ route('admin.warehouses.edit', $warehouse->id) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit Warehouse
                    </a>

                    @if(!$warehouse->is_default)
                    <button type="button" class="btn btn-outline-primary" onclick="setAsDefault()">
                        <i class="bi bi-star"></i> Set as Default
                    </button>
                    @endif

                    <button type="button" class="btn btn-outline-secondary" onclick="toggleStatus()">
                        <i class="bi bi-toggle-{{ $warehouse->is_active ? 'off' : 'on' }}"></i>
                        {{ $warehouse->is_active ? 'Deactivate' : 'Activate' }}
                    </button>

                    <a href="{{ route('admin.inventory.index') }}?warehouse={{ $warehouse->id }}"
                       class="btn btn-outline-info">
                        <i class="bi bi-boxes"></i> View in Inventory
                    </a>

                    @if(auth('admin')->user()->hasPermission('inventory.delete') && !$warehouse->is_default && !$warehouse->hasStock())
                    <button type="button" class="btn btn-outline-danger" onclick="deleteWarehouse()">
                        <i class="bi bi-trash"></i> Delete Warehouse
                    </button>
                    @endif
                </div>
            </div>
            @endif

            <!-- Stock Summary -->
            <div class="info-card" style="background: linear-gradient(135deg, #f0f7ed 0%, #e8f5e0 100%);">
                <h5 class="info-card-title">
                    <i class="bi bi-graph-up"></i> Stock Summary
                </h5>

                <div class="info-row">
                    <div class="info-label">In Stock:</div>
                    <div class="info-value">
                        <span class="badge bg-success">{{ $warehouse->stock()->where('quantity', '>', 0)->count() }} Products</span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Low Stock:</div>
                    <div class="info-value">
                        <span class="badge bg-warning text-dark">{{ $lowStock }} Products</span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Out of Stock:</div>
                    <div class="info-value">
                        <span class="badge bg-danger">{{ $outOfStock }} Products</span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Reserved Stock:</div>
                    <div class="info-value">
                        <strong>{{ number_format($warehouse->stock()->sum('reserved_quantity')) }}</strong> units
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let stockTable;

$(document).ready(function() {
    // Initialize stock DataTable - but only when the tab is shown for the first time
    let stockTableInitialized = false;

    // Load stock table when tab is shown
    $('button[data-bs-target="#stock"]').on('shown.bs.tab', function() {
        if (!stockTableInitialized) {
            // Initialize DataTable
            stockTable = $('#stockTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("admin.warehouses.stock.data", $warehouse->id) }}',
                columns: [
                    { data: 'product_info', name: 'product_info', orderable: false },
                    { data: 'quantity', name: 'quantity' },
                    { data: 'available', name: 'available', orderable: false },
                    { data: 'reserved', name: 'reserved', orderable: false },
                    { data: 'location', name: 'location', orderable: false },
                    { data: 'value', name: 'value', orderable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[1, 'desc']],
                pageLength: 10,
                language: {
                    processing: '<i class="bi bi-hourglass-split"></i> Loading...',
                    emptyTable: 'No stock found in this warehouse'
                },
                responsive: true,
                autoWidth: false // This is important
            });

            stockTableInitialized = true;
        } else {
            // Just reload data if already initialized
            stockTable.ajax.reload();
            stockTable.columns.adjust().draw(); // Recalculate column widths
        }
    });
});

// Toggle warehouse status
function toggleStatus() {
    const isActive = {{ $warehouse->is_active ? 'true' : 'false' }};
    const action = isActive ? 'deactivate' : 'activate';

    Swal.fire({
        title: `${action.charAt(0).toUpperCase() + action.slice(1)} Warehouse?`,
        text: `This will ${action} the warehouse`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#5B914C',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Yes, ${action} it!`
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.warehouses.toggle-status", $warehouse->id) }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            confirmButtonColor: '#5B914C'
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to toggle status'
                    });
                }
            });
        }
    });
}

// Set as default warehouse
function setAsDefault() {
    Swal.fire({
        title: 'Set as Default Warehouse?',
        text: 'This will be used as the default warehouse for all operations',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#5B914C',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, set as default!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.warehouses.set-default", $warehouse->id) }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            confirmButtonColor: '#5B914C'
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to set default warehouse'
                    });
                }
            });
        }
    });
}

// Delete warehouse
function deleteWarehouse() {
    Swal.fire({
        title: 'Delete Warehouse?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        input: 'checkbox',
        inputPlaceholder: 'I understand this warehouse will be permanently deleted'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            $.ajax({
                url: '{{ route("admin.warehouses.destroy", $warehouse->id) }}',
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                beforeSend: function() {
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message,
                            confirmButtonColor: '#5B914C'
                        }).then(() => {
                            window.location.href = '{{ route("admin.warehouses.index") }}';
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to delete warehouse'
                    });
                }
            });
        } else if (result.isConfirmed && !result.value) {
            Swal.fire('Cancelled', 'Please check the confirmation box', 'info');
        }
    });
}
</script>
@endpush

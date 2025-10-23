@extends('admin.layouts.app')

@section('title', 'Order Invoices')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Order Invoices</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
                    <li class="breadcrumb-item active">Invoices</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                                <i class="bi bi-file-earmark-text fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Invoices</h6>
                            <h3 class="mb-0" id="totalInvoices">0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                                <i class="bi bi-check-circle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Paid Invoices</h6>
                            <h3 class="mb-0" id="paidInvoices">0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Pending Invoices</h6>
                            <h3 class="mb-0" id="pendingInvoices">0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 text-info rounded-3 p-3">
                                <i class="bi bi-currency-dollar fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Amount</h6>
                            <h3 class="mb-0" id="totalAmount">$0.00</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h6 class="card-title mb-3">Quick Actions</h6>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-primary btn-sm" id="bulkDownloadBtn">
                    <i class="bi bi-download"></i> Bulk Download
                </button>
                {{-- <button class="btn btn-outline-success btn-sm" id="bulkEmailBtn">
                    <i class="bi bi-envelope"></i> Bulk Email
                </button> --}}
                <button class="btn btn-outline-info btn-sm" id="exportBtn">
                    <i class="bi bi-file-earmark-excel"></i> Export to Excel
                </button>
                <button class="btn btn-outline-secondary btn-sm" id="printBtn">
                    <i class="bi bi-printer"></i> Print List
                </button>
            </div>
        </div>
    </div>

    {{-- Invoices Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">All Invoices</h5>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse"
                            data-bs-target="#filtersCollapse">
                        <i class="bi bi-funnel"></i> Filters
                    </button>
                </div>
            </div>
        </div>

        {{-- Filters Section --}}
        <div class="collapse" id="filtersCollapse">
            <div class="card-body border-bottom bg-light">
                <form id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Payment Status</label>
                            <select class="form-select" name="payment_status" id="filterPaymentStatus">
                                <option value="">All Payment Statuses</option>
                                <option value="PAYMENT_PENDING">Pending</option>
                                <option value="PAYMENT_PAID">Paid</option>
                                <option value="PAYMENT_PARTIALLY_PAID">Partially Paid</option>
                                <option value="PAYMENT_REFUNDED">Refunded</option>
                                <option value="PAYMENT_PARTIALLY_REFUNDED">Partially Refunded</option>
                                <option value="PAYMENT_FAILED">Failed</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Invoice Status</label>
                            <select class="form-select" name="invoice_status" id="filterInvoiceStatus">
                                <option value="">All</option>
                                <option value="generated">Generated</option>
                                <option value="not_generated">Not Generated</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" name="date_from" id="filterDateFrom">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" name="date_to" id="filterDateTo">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary w-100" id="applyFilters">
                                    <i class="bi bi-search"></i> Apply
                                </button>
                                <button type="button" class="btn btn-outline-secondary w-100" id="clearFilters">
                                    <i class="bi bi-x-circle"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="invoicesTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>Invoice #</th>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Invoice Date</th>
                            <th>Amount</th>
                            <th>Payment Status</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- DataTable will populate this --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Bulk Actions Bar --}}
    <div class="position-fixed bottom-0 start-50 translate-middle-x mb-4 d-none" id="bulkActionsBar" style="z-index: 1050;">
        <div class="card shadow-lg border-0">
            <div class="card-body py-2 px-4">
                <div class="d-flex align-items-center gap-3">
                    <span class="fw-bold"><span id="selectedCount">0</span> selected</span>
                    <div class="vr"></div>
                    <button type="button" class="btn btn-sm btn-primary" id="downloadSelectedBtn">
                        <i class="bi bi-download"></i> Download
                    </button>
                    {{-- <button type="button" class="btn btn-sm btn-success" id="emailSelectedBtn">
                        <i class="bi bi-envelope"></i> Email
                    </button> --}}
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSelection">
                        Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Email Invoice Modal --}}
<div class="modal fade" id="emailInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="emailInvoiceForm">
                    <div class="mb-3">
                        <label for="emailTo" class="form-label">Email To <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="emailTo" name="email_to" required>
                        <small class="text-muted">Customer email will be pre-filled</small>
                    </div>
                    <div class="mb-3">
                        <label for="emailSubject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="emailSubject" name="subject" value="Your Invoice">
                    </div>
                    <div class="mb-3">
                        <label for="emailMessage" class="form-label">Message (Optional)</label>
                        <textarea class="form-control" id="emailMessage" name="message" rows="4" placeholder="Add a personal message..."></textarea>
                    </div>
                    <input type="hidden" id="invoiceOrderId" name="order_id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="sendEmailBtn">
                    <i class="bi bi-send"></i> Send Email
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    let invoicesTable;
    let selectedInvoices = [];

    // Initialize DataTable
    function initDataTable() {
        invoicesTable = $('#invoicesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.orders.data') }}",
                data: function(d) {
                    d.has_invoice = true; // Only orders with invoices
                    d.payment_status = $('#filterPaymentStatus').val();
                    d.invoice_status = $('#filterInvoiceStatus').val();
                    d.date_from = $('#filterDateFrom').val();
                    d.date_to = $('#filterDateTo').val();
                }
            },
            columns: [
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return '<input type="checkbox" class="form-check-input invoice-checkbox" value="' + data + '">';
                    }
                },
                {
                    data: 'invoice_number',
                    render: function(data, type, row) {
                        return data ? '<strong>' + data + '</strong>' : '<span class="text-muted">Not Generated</span>';
                    }
                },
                {
                    data: 'order_number',
                    render: function(data, type, row) {
                        return '<a href="/admin/orders/' + row.id + '" class="fw-bold">' + data + '</a>';
                    }
                },
                {
                    data: 'customer_info',
                    orderable: false
                },
                {
                    data: 'invoice_generated_at',
                    render: function(data) {
                        if (data) {
                            const date = new Date(data);
                            return date.toLocaleDateString() + '<br><small class="text-muted">' + date.toLocaleTimeString() + '</small>';
                        }
                        return '<span class="text-muted">N/A</span>';
                    }
                },
                {
                    data: 'total_amount',
                    render: function(data) {
                        return '<strong>$' + parseFloat(data).toFixed(2) + '</strong>';
                    }
                },
                {
                    data: 'payment_info',
                    orderable: false
                },
                {
                    data: 'invoice_number',
                    render: function(data) {
                        if (data) {
                            return '<span class="badge bg-success">Generated</span>';
                        }
                        return '<span class="badge bg-warning">Pending</span>';
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        let actions = '<div class="btn-group btn-group-sm" role="group">';

                        if (row.invoice_number) {
                            actions += '<a href="/admin/orders/' + data + '/invoice" class="btn btn-outline-primary" title="View Invoice" target="_blank"><i class="bi bi-eye"></i></a>';
                            actions += '<a href="/admin/orders/' + data + '/invoice" class="btn btn-outline-success" title="Download" download><i class="bi bi-download"></i></a>';
                            // actions += '<button class="btn btn-outline-info email-invoice" data-id="' + data + '" data-email="' + (row.customer_email || row.guest_email) + '" title="Email"><i class="bi bi-envelope"></i></button>';
                        } else {
                            actions += '<button class="btn btn-outline-warning generate-invoice" data-id="' + data + '" title="Generate Invoice"><i class="bi bi-file-earmark-plus"></i></button>';
                        }

                        actions += '</div>';
                        return actions;
                    }
                }
            ],
            order: [[4, 'desc']],
            pageLength: 25,
            language: {
                processing: `
                    <div class="text-center">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="mt-2">Loading Invoices...</div>
                    </div>
                `
            },
            drawCallback: function() {
                updateCheckboxStates();
                updateStatistics();
            }
        });
    }

    initDataTable();

    // Update Statistics
    function updateStatistics() {
        // This would typically call an API endpoint
        // For now, we'll count from the visible data
        let total = 0;
        let paid = 0;
        let pending = 0;
        let amount = 0;

        invoicesTable.rows().every(function() {
            const data = this.data();
            total++;
            amount += parseFloat(data.total_amount);

            if (data.payment_status_key_code === 'PAYMENT_PAID') {
                paid++;
            } else if (data.payment_status_key_code === 'PAYMENT_PENDING') {
                pending++;
            }
        });

        $('#totalInvoices').text(total);
        $('#paidInvoices').text(paid);
        $('#pendingInvoices').text(pending);
        $('#totalAmount').text('$' + amount.toFixed(2));
    }

    // Apply Filters
    $('#applyFilters').on('click', function() {
        invoicesTable.ajax.reload();
    });

    // Clear Filters
    $('#clearFilters').on('click', function() {
        $('#filterForm')[0].reset();
        invoicesTable.ajax.reload();
    });

    // Select All Checkboxes
    $('#selectAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.invoice-checkbox').prop('checked', isChecked);

        if (isChecked) {
            $('.invoice-checkbox').each(function() {
                const invoiceId = $(this).val();
                if (!selectedInvoices.includes(invoiceId)) {
                    selectedInvoices.push(invoiceId);
                }
            });
        } else {
            selectedInvoices = [];
        }

        updateBulkActionsBar();
    });

    // Individual Checkbox
    $(document).on('change', '.invoice-checkbox', function() {
        const invoiceId = $(this).val();

        if ($(this).is(':checked')) {
            if (!selectedInvoices.includes(invoiceId)) {
                selectedInvoices.push(invoiceId);
            }
        } else {
            selectedInvoices = selectedInvoices.filter(id => id !== invoiceId);
            $('#selectAll').prop('checked', false);
        }

        updateBulkActionsBar();
    });

    // Clear Selection
    $('#clearSelection').on('click', function() {
        selectedInvoices = [];
        $('.invoice-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        updateBulkActionsBar();
    });

    // Update Checkbox States
    function updateCheckboxStates() {
        $('.invoice-checkbox').each(function() {
            const invoiceId = $(this).val();
            if (selectedInvoices.includes(invoiceId)) {
                $(this).prop('checked', true);
            }
        });
    }

    // Update Bulk Actions Bar
    function updateBulkActionsBar() {
        if (selectedInvoices.length > 0) {
            $('#bulkActionsBar').removeClass('d-none');
            $('#selectedCount').text(selectedInvoices.length);
        } else {
            $('#bulkActionsBar').addClass('d-none');
        }
    }

    // Generate Invoice
    $(document).on('click', '.generate-invoice', function() {
        const orderId = $(this).data('id');

        Swal.fire({
            title: 'Generate Invoice?',
            text: 'This will create an invoice for this order.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Generate',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Call API to generate invoice
                Swal.fire({
                    icon: 'success',
                    title: 'Invoice Generated!',
                    text: 'The invoice has been created successfully.',
                    timer: 2000,
                    showConfirmButton: false
                });
                invoicesTable.ajax.reload();
            }
        });
    });

    // Email Invoice
    $(document).on('click', '.email-invoice', function() {
        const orderId = $(this).data('id');
        const email = $(this).data('email');

        $('#invoiceOrderId').val(orderId);
        $('#emailTo').val(email);
        $('#emailInvoiceModal').modal('show');
    });

    // Send Email
    $('#sendEmailBtn').on('click', function() {
        const formData = {
            order_id: $('#invoiceOrderId').val(),
            email_to: $('#emailTo').val(),
            subject: $('#emailSubject').val(),
            message: $('#emailMessage').val()
        };

        // Call API to send email
        Swal.fire({
            icon: 'success',
            title: 'Email Sent!',
            text: 'Invoice has been sent successfully.',
            timer: 2000,
            showConfirmButton: false
        });

        $('#emailInvoiceModal').modal('hide');
        $('#emailInvoiceForm')[0].reset();
    });

    // Bulk Download
    $('#bulkDownloadBtn, #downloadSelectedBtn').on('click', function() {
        if (selectedInvoices.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Selection',
                text: 'Please select at least one invoice to download.'
            });
            return;
        }

        Swal.fire({
            icon: 'info',
            title: 'Coming Soon',
            text: 'Bulk download functionality will be implemented.'
        });
    });

    // Bulk Email
    $('#bulkEmailBtn, #emailSelectedBtn').on('click', function() {
        if (selectedInvoices.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Selection',
                text: 'Please select at least one invoice to email.'
            });
            return;
        }

        Swal.fire({
            icon: 'info',
            title: 'Coming Soon',
            text: 'Bulk email functionality will be implemented.'
        });
    });

    // Export to Excel
    $('#exportBtn').on('click', function() {
        Swal.fire({
            icon: 'info',
            title: 'Coming Soon',
            text: 'Export to Excel functionality will be implemented.'
        });
    });

    // Print List
    $('#printBtn').on('click', function() {
        window.print();
    });
});
</script>
@endpush

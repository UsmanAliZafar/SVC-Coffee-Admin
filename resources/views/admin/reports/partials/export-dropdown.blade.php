{{-- resources/views/admin/reports/partials/export-dropdown.blade.php --}}
<div class="btn-group">
    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-download"></i> Export
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <h6 class="dropdown-header">Export Format</h6>
        </li>
        <li class="d-none">
            <a class="dropdown-item" href="{{ $pdfUrl }}" target="_blank">
                <i class="bi bi-file-pdf text-danger"></i> Export as PDF
            </a>
        </li>
        <li class="d-none">
            <a class="dropdown-item" href="{{ $excelUrl }}">
                <i class="bi bi-file-excel text-success"></i> Export as Excel
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ $csvUrl }}">
                <i class="bi bi-file-csv text-info"></i> Export as CSV
            </a>
        </li>
    </ul>
</div>

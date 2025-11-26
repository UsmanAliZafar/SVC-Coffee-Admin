{{-- Add to footer section --}}
<footer class="admin-footer mt-auto py-3 bg-white border-top">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-4 text-center text-md-start">
                <small class="text-muted">
                    © {{ date('Y') }} {{ store_name() }}. All rights reserved.
                </small>
            </div>
            <div class="col-md-4 text-center">
                <small class="text-muted">
                    <i class="bi bi-clock"></i> {{ store_datetime_now() }}
                    <span class="badge bg-secondary ms-2">{{ store_timezone() }}</span>
                </small>
            </div>
            <div class="col-md-4 text-center text-md-end">
                <small class="text-muted">
                    <a href="mailto:{{ store_email() }}" class="text-decoration-none">
                        {{ store_email() }}
                    </a>
                </small>
            </div>
        </div>
    </div>
</footer>

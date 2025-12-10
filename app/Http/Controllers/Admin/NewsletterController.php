<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use App\Models\AdminUser;
use App\Services\NewsletterVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class NewsletterController extends Controller
{
    protected NewsletterVerificationService $verificationService;

    public function __construct(NewsletterVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    /**
     * Display a listing of newsletters.
     */
    public function index(Request $request)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('newsletters.read')) {
            abort(403, 'Unauthorized action.');
        }

        // Get statistics
        $stats = [
            'total' => Newsletter::count(),
            'subscribed' => Newsletter::where('is_subscribed', true)->count(),
            'unsubscribed' => Newsletter::where('is_subscribed', false)->count(),
            'verified' => Newsletter::where('email_verified', true)->count(),
            'unverified' => Newsletter::where('email_verified', false)->count(),
            'today' => Newsletter::whereDate('created_at', today())->count(),
            'this_week' => Newsletter::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => Newsletter::whereMonth('created_at', now()->month)
                                      ->whereYear('created_at', now()->year)
                                      ->count(),
        ];

        return view('admin.newsletters.index', compact('stats'));
    }

    /**
     * Get newsletters data for DataTable (AJAX)
     */
    public function getData(Request $request)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('newsletters.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Newsletter::query()->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            if ($request->status === 'subscribed') {
                $query->where('is_subscribed', true);
            } elseif ($request->status === 'unsubscribed') {
                $query->where('is_subscribed', false);
            }
        }

        if ($request->filled('verification_status')) {
            if ($request->verification_status === 'verified') {
                $query->where('email_verified', true);
            } elseif ($request->verification_status === 'unverified') {
                $query->where('email_verified', false);
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
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addColumn('checkbox', function($newsletter) {
                if (Auth::guard('admin')->user()->hasPermission('newsletters.delete')) {
                    return '<input type="checkbox" class="form-check-input newsletter-checkbox" value="' . $newsletter->id . '">';
                }
                return '';
            })
            ->addColumn('subscriber_info', function($newsletter) {
                $viewUrl = route('admin.newsletters.show', $newsletter->id);

                $html = '<div class="d-flex align-items-center">';

                // Avatar placeholder
                $initial = strtoupper(substr($newsletter->email, 0, 1));
                $html .= '<div class="bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center"
                         style="width: 40px; height: 40px; font-weight: bold;">
                         ' . $initial . '
                         </div>';

                // Email and Name
                $html .= '<div class="subscriber-details">';
                $html .= '<div class="subscriber-email">';
                $html .= '<strong><a href="' . $viewUrl . '" class="text-decoration-none subscriber-link">'
                     . htmlspecialchars($newsletter->email) . '</a></strong>';

                // Verification badge
                if ($newsletter->email_verified) {
                    $html .= ' <i class="bi bi-patch-check-fill text-success" title="Email Verified" data-bs-toggle="tooltip"></i>';
                } else {
                    $html .= ' <i class="bi bi-exclamation-circle text-warning" title="Email Not Verified" data-bs-toggle="tooltip"></i>';
                }

                $html .= '</div>';

                // Name if available
                if ($newsletter->name) {
                    $html .= '<div class="subscriber-name">';
                    $html .= '<small class="text-muted">' . htmlspecialchars($newsletter->name) . '</small>';
                    $html .= '</div>';
                }

                $html .= '</div>';
                $html .= '</div>';

                return $html;
            })
            ->addColumn('status_badge', function($newsletter) {
                if ($newsletter->is_subscribed) {
                    return '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Subscribed</span>';
                }
                return '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Unsubscribed</span>';
            })
            ->addColumn('verification_badge', function($newsletter) {
                if ($newsletter->email_verified) {
                    return '<span class="badge bg-success"><i class="bi bi-patch-check me-1"></i>Verified</span>';
                }
                return '<span class="badge bg-warning"><i class="bi bi-exclamation-circle me-1"></i>Unverified</span>';
            })
            ->addColumn('subscribed_at_formatted', function($newsletter) {
                if (!$newsletter->subscribed_at) {
                    return '<span class="text-muted small">N/A</span>';
                }

                $html = '<div class="subscribed-at-container">';
                $html .= '<div>' . $newsletter->subscribed_at->format('M d, Y') . '</div>';
                $html .= '<small class="text-muted">' . $newsletter->subscribed_at->format('h:i A') . '</small>';
                $html .= '<div><small class="text-muted">' . $newsletter->subscribed_at->diffForHumans() . '</small></div>';
                $html .= '</div>';

                return $html;
            })
            ->addColumn('verified_at_formatted', function($newsletter) {
                if (!$newsletter->email_verified_at) {
                    return '<span class="text-muted small">—</span>';
                }

                $html = '<div class="verified-at-container">';
                $html .= '<div>' . $newsletter->email_verified_at->format('M d, Y') . '</div>';
                $html .= '<small class="text-muted">' . $newsletter->email_verified_at->format('h:i A') . '</small>';
                $html .= '</div>';

                return $html;
            })
            ->addColumn('created_at_formatted', function($newsletter) {
                $html = '<div class="created-at-container">';
                $html .= '<div>' . $newsletter->created_at->format('M d, Y') . '</div>';
                $html .= '<small class="text-muted">' . $newsletter->created_at->format('h:i A') . '</small>';
                $html .= '<div><small class="text-muted">' . $newsletter->created_at->diffForHumans() . '</small></div>';
                $html .= '</div>';

                return $html;
            })
            ->addColumn('actions', function($newsletter) {
                $actions = '<div class="btn-group" role="group">';

                // View button
                if (Auth::guard('admin')->user()->hasPermission('newsletters.read')) {
                    $actions .= '<a href="' . route('admin.newsletters.show', $newsletter->id) . '"
                                class="btn btn-sm btn-info" title="View">
                                <i class="bi bi-eye"></i>
                                </a>';
                }

                // Edit button
                if (Auth::guard('admin')->user()->hasPermission('newsletters.update')) {
                    $actions .= '<a href="' . route('admin.newsletters.edit', $newsletter->id) . '"
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

                // Subscribe/Unsubscribe actions
                if (Auth::guard('admin')->user()->hasPermission('newsletters.update')) {
                    if ($newsletter->is_subscribed) {
                        $actions .= '<li><button type="button" class="dropdown-item unsubscribe-newsletter"
                                    data-id="' . $newsletter->id . '" data-email="' . htmlspecialchars($newsletter->email) . '">
                                    <i class="bi bi-x-circle text-warning me-2"></i>Unsubscribe
                                    </button></li>';
                    } else {
                        $actions .= '<li><button type="button" class="dropdown-item subscribe-newsletter"
                                    data-id="' . $newsletter->id . '" data-email="' . htmlspecialchars($newsletter->email) . '">
                                    <i class="bi bi-check-circle text-success me-2"></i>Subscribe
                                    </button></li>';
                    }

                    // Verify/Resend Verification actions
                    if (!$newsletter->email_verified) {
                        $actions .= '<li><button type="button" class="dropdown-item resend-verification"
                                    data-id="' . $newsletter->id . '" data-email="' . htmlspecialchars($newsletter->email) . '">
                                    <i class="bi bi-envelope text-primary me-2"></i>Resend Verification
                                    </button></li>';

                        $actions .= '<li><button type="button" class="dropdown-item verify-email"
                                    data-id="' . $newsletter->id . '" data-email="' . htmlspecialchars($newsletter->email) . '">
                                    <i class="bi bi-patch-check text-success me-2"></i>Mark as Verified
                                    </button></li>';
                    }

                    $actions .= '<li><hr class="dropdown-divider"></li>';
                }

                // Delete
                if (Auth::guard('admin')->user()->hasPermission('newsletters.delete')) {
                    $actions .= '<li><button type="button" class="dropdown-item text-danger delete-newsletter"
                                data-id="' . $newsletter->id . '" data-email="' . htmlspecialchars($newsletter->email) . '">
                                <i class="bi bi-trash me-2"></i>Delete
                                </button></li>';
                }

                $actions .= '</ul></div>';
                $actions .= '</div>';

                return $actions;
            })
            ->rawColumns([
                'checkbox',
                'subscriber_info',
                'status_badge',
                'verification_badge',
                'subscribed_at_formatted',
                'verified_at_formatted',
                'created_at_formatted',
                'actions'
            ])
            ->make(true);
    }

    /**
     * Get statistics for newsletters
     */
    public function statistics()
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('newsletters.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $stats = [
            'total' => Newsletter::count(),
            'subscribed' => Newsletter::where('is_subscribed', true)->count(),
            'unsubscribed' => Newsletter::where('is_subscribed', false)->count(),
            'verified' => Newsletter::where('email_verified', true)->count(),
            'unverified' => Newsletter::where('email_verified', false)->count(),
            'today' => Newsletter::whereDate('created_at', today())->count(),
            'this_week' => Newsletter::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => Newsletter::whereMonth('created_at', now()->month)
                                      ->whereYear('created_at', now()->year)
                                      ->count(),
            'last_7_days' => Newsletter::where('created_at', '>=', now()->subDays(7))->count(),
            'last_30_days' => Newsletter::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Show the form for creating a new newsletter subscriber
     */
    public function create()
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('newsletters.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('admin.newsletters.create');
    }

    /**
     * Store a newly created newsletter subscriber
     */
    public function store(Request $request)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('newsletters.create')) {
            abort(403, 'Unauthorized action.');
        }

        // Validate request
        $validated = $request->validate([
            'email' => 'required|email|unique:newsletters,email',
            'name' => 'nullable|string|max:255',
            'is_subscribed' => 'nullable|boolean',
            'email_verified' => 'nullable|boolean',
            'send_verification' => 'nullable|boolean',
        ]);

        // Set defaults
        $validated['is_subscribed'] = $request->has('is_subscribed');
        $validated['email_verified'] = $request->has('email_verified');

        if ($validated['is_subscribed']) {
            $validated['subscribed_at'] = now();
        }

        if ($validated['email_verified']) {
            $validated['email_verified_at'] = now();
        } else {
            // Generate verification token if not verified
            $validated['verification_token'] = Str::random(64);
        }

        // Create newsletter
        $newsletter = Newsletter::create($validated);

        // Send verification email if requested and not verified
        if ($request->has('send_verification') && !$validated['email_verified']) {
            $this->verificationService->sendVerificationEmail($newsletter, $newsletter->verification_token);
        }

        // Log activity

        return redirect()
            ->route('admin.newsletters.index')
            ->with('success', 'Newsletter subscriber added successfully!');
    }

    /**
     * Display the specified newsletter subscriber
     */
    public function show($id)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('newsletters.read')) {
            abort(403, 'Unauthorized action.');
        }

        $newsletter = Newsletter::findOrFail($id);

        return view('admin.newsletters.show', compact('newsletter'));
    }

    /**
     * Show the form for editing the specified newsletter subscriber
     */
    public function edit($id)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('newsletters.update')) {
            abort(403, 'Unauthorized action.');
        }

        $newsletter = Newsletter::findOrFail($id);

        return view('admin.newsletters.edit', compact('newsletter'));
    }

    /**
     * Update the specified newsletter subscriber
     */
    public function update(Request $request, $id)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('newsletters.update')) {
            abort(403, 'Unauthorized action.');
        }

        $newsletter = Newsletter::findOrFail($id);

        // Validate request
        $validated = $request->validate([
            'email' => ['required', 'email', Rule::unique('newsletters', 'email')->ignore($id)],
            'name' => 'nullable|string|max:255',
            'is_subscribed' => 'nullable|boolean',
            'email_verified' => 'nullable|boolean',
        ]);

        // Set subscription status
        $validated['is_subscribed'] = $request->has('is_subscribed');
        $validated['email_verified'] = $request->has('email_verified');

        // Update timestamps based on status changes
        if ($validated['is_subscribed'] && !$newsletter->is_subscribed) {
            $validated['subscribed_at'] = now();
            $validated['unsubscribed_at'] = null;
        } elseif (!$validated['is_subscribed'] && $newsletter->is_subscribed) {
            $validated['unsubscribed_at'] = now();
        }

        if ($validated['email_verified'] && !$newsletter->email_verified) {
            $validated['email_verified_at'] = now();
            $validated['verification_token'] = null;
        }

        // Update newsletter
        $newsletter->update($validated);

        // Log activity

        return redirect()
            ->route('admin.newsletters.index')
            ->with('success', 'Newsletter subscriber updated successfully!');
    }

    /**
     * Remove the specified newsletter subscriber
     */
    public function destroy($id)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('newsletters.delete')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $newsletter = Newsletter::findOrFail($id);
        $newsletter->delete();

        // Log activity

        return response()->json([
            'success' => true,
            'message' => 'Newsletter subscriber deleted successfully!'
        ]);
    }

    /**
     * Subscribe a newsletter
     */
    public function subscribe($id)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('newsletters.update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $newsletter = Newsletter::findOrFail($id);
        $newsletter->subscribe();

        // Log activity

        return response()->json([
            'success' => true,
            'message' => 'Subscriber activated successfully!'
        ]);
    }

    /**
     * Unsubscribe a newsletter
     */
    public function unsubscribe($id)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('newsletters.update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $newsletter = Newsletter::findOrFail($id);
        $newsletter->unsubscribe();

        // Log activity

        return response()->json([
            'success' => true,
            'message' => 'Subscriber unsubscribed successfully!'
        ]);
    }

    /**
     * Resend verification email
     */
    public function resendVerification($id)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('newsletters.update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $newsletter = Newsletter::findOrFail($id);

            if ($newsletter->email_verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is already verified!'
                ], 400);
            }

            $token = $newsletter->generateVerificationToken();
            $this->verificationService->sendVerificationEmail($newsletter, $token);

            return response()->json([
                'success' => true,
                'message' => 'Verification email has been resent successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resend verification email.'
            ], 500);
        }
    }

    /**
     * Manually verify email
     */
    public function verifyEmail($id)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('newsletters.update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $newsletter = Newsletter::findOrFail($id);

        if ($newsletter->email_verified) {
            return response()->json([
                'success' => false,
                'message' => 'Email is already verified!'
            ], 400);
        }

        $newsletter->verify();

        // Log activity

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully!'
        ]);
    }

    /**
     * Bulk delete newsletters
     */
    public function bulkDelete(Request $request)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('newsletters.delete')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'newsletter_ids' => 'required|array',
            'newsletter_ids.*' => 'exists:newsletters,id',
        ]);

        $count = Newsletter::whereIn('id', $request->newsletter_ids)->delete();

        // Log activity

        return response()->json([
            'success' => true,
            'message' => "{$count} subscriber(s) deleted successfully!"
        ]);
    }

    /**
     * Bulk update subscription status
     */
    public function bulkUpdateStatus(Request $request)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('newsletters.update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'newsletter_ids' => 'required|array',
            'newsletter_ids.*' => 'exists:newsletters,id',
            'is_subscribed' => 'required|boolean',
        ]);

        $updateData = [
            'is_subscribed' => $request->is_subscribed
        ];

        if ($request->is_subscribed) {
            $updateData['subscribed_at'] = now();
            $updateData['unsubscribed_at'] = null;
        } else {
            $updateData['unsubscribed_at'] = now();
        }

        Newsletter::whereIn('id', $request->newsletter_ids)
            ->update($updateData);

        $status = $request->is_subscribed ? 'subscribed' : 'unsubscribed';

        // Log activity

        return response()->json([
            'success' => true,
            'message' => count($request->newsletter_ids) . " subscriber(s) {$status} successfully!"
        ]);
    }

    /**
     * Export newsletters to CSV
     */
    public function export(Request $request)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('newsletters.read')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Newsletter::query();

        // Apply filters
        if ($request->filled('status')) {
            if ($request->status === 'subscribed') {
                $query->where('is_subscribed', true);
            } elseif ($request->status === 'unsubscribed') {
                $query->where('is_subscribed', false);
            }
        }

        if ($request->filled('verification_status')) {
            if ($request->verification_status === 'verified') {
                $query->where('email_verified', true);
            } elseif ($request->verification_status === 'unverified') {
                $query->where('email_verified', false);
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $newsletters = $query->get();

        $filename = 'newsletters_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($newsletters) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            fputcsv($file, [
                'ID',
                'Email',
                'Name',
                'Subscription Status',
                'Email Verified',
                'Subscribed At',
                'Unsubscribed At',
                'Verified At',
                'Created At',
                'Updated At'
            ]);

            // Data rows
            foreach ($newsletters as $newsletter) {
                fputcsv($file, [
                    $newsletter->id,
                    $newsletter->email,
                    $newsletter->name ?? 'N/A',
                    $newsletter->is_subscribed ? 'Subscribed' : 'Unsubscribed',
                    $newsletter->email_verified ? 'Yes' : 'No',
                    $newsletter->subscribed_at?->format('Y-m-d H:i:s') ?? 'N/A',
                    $newsletter->unsubscribed_at?->format('Y-m-d H:i:s') ?? 'N/A',
                    $newsletter->email_verified_at?->format('Y-m-d H:i:s') ?? 'N/A',
                    $newsletter->created_at->format('Y-m-d H:i:s'),
                    $newsletter->updated_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

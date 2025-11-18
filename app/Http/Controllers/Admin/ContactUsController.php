<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
//
use App\Models\ContactUs;
use App\Models\SystemStatus;
use App\Models\AdminUser;

class ContactUsController extends Controller
{
    /**
     * Display a listing of contact messages
     */
    public function index()
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('contact_us.read')) {
            abort(403, 'Unauthorized access');
        }

        // Get status list for filters
        $statusList = SystemStatus::where('module', 'contact_us')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        // Get admin users for assignment filter
        $adminUsers = AdminUser::active()
            ->get();

        // Get statistics
        $stats = [
            'total' => ContactUs::count(),
            'new' => ContactUs::whereHas('status', function($q) {
                $q->where('key_code', 'CONTACT_NEW');
            })->count(),
            'pending' => ContactUs::whereHas('status', function($q) {
                $q->where('key_code', 'CONTACT_PENDING');
            })->count(),
            'in_progress' => ContactUs::whereHas('status', function($q) {
                $q->where('key_code', 'CONTACT_IN_PROGRESS');
            })->count(),
            'resolved' => ContactUs::whereHas('status', function($q) {
                $q->where('key_code', 'CONTACT_RESOLVED');
            })->count(),
            'unread' => ContactUs::whereNull('read_at')->count(),
            'today' => ContactUs::whereDate('created_at', today())->count(),
        ];

        return view('admin.contact-us.index', compact(
            'statusList',
            'adminUsers',
            'stats'
        ));
    }

    /**
     * Get contact messages data for DataTable (AJAX)
     */
    public function getData(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('contact_us.read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = ContactUs::with([
            'status',
            'assignedAdmin'
        ])->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status_key_code', $request->status);
        }

        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        if ($request->filled('read_status')) {
            if ($request->read_status === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->read_status === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
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
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addColumn('checkbox', function($contact) {
                if (auth('admin')->user()->hasPermission('contact_us.delete')) {
                    return '<input type="checkbox" class="form-check-input contact-checkbox" value="' . $contact->id . '">';
                }
                return '';
            })
            ->addColumn('contact_info', function($contact) {
                $viewUrl = route('admin.contact-us.show', $contact->id);

                $html = '<div class="contact-info">';

                // Name with read/unread indicator
                $html .= '<div class="contact-name">';
                if (!$contact->read_at) {
                    $html .= '<i class="bi bi-envelope-fill text-primary me-1" title="Unread"></i>';
                } else {
                    $html .= '<i class="bi bi-envelope-open text-muted me-1" title="Read"></i>';
                }
                $html .= '<strong><a href="' . $viewUrl . '" class="text-decoration-none contact-link">'
                     . htmlspecialchars($contact->name) . '</a></strong>';
                $html .= '</div>';

                // Email
                $html .= '<div class="contact-email">';
                $html .= '<i class="bi bi-envelope me-1"></i>';
                $html .= '<a href="mailto:' . htmlspecialchars($contact->email) . '" class="text-muted">'
                     . htmlspecialchars($contact->email) . '</a>';
                $html .= '</div>';

                // Phone (if available)
                if ($contact->phone) {
                    $html .= '<div class="contact-phone">';
                    $html .= '<i class="bi bi-telephone me-1"></i>';
                    $html .= '<a href="tel:' . htmlspecialchars($contact->phone) . '" class="text-muted">'
                         . htmlspecialchars($contact->phone) . '</a>';
                    $html .= '</div>';
                }

                $html .= '</div>';

                return $html;
            })
            ->addColumn('subject_message', function($contact) {
                $html = '<div class="subject-message-container">';

                // Subject
                $html .= '<div class="message-subject mb-1">';
                $html .= '<strong>' . htmlspecialchars($contact->subject) . '</strong>';
                $html .= '</div>';

                // Message preview (truncated)
                $messagePreview = Str::limit(strip_tags($contact->message), 100);
                $html .= '<div class="message-preview text-muted">';
                $html .= '<small>' . htmlspecialchars($messagePreview) . '</small>';
                $html .= '</div>';

                $html .= '</div>';

                return $html;
            })
            ->addColumn('status_badge', function($contact) {
                return $contact->getStatusBadge();
            })
            ->addColumn('priority_badge', function($contact) {
                if (!$contact->priority) {
                    return '<span class="badge bg-secondary">Normal</span>';
                }

                $badges = [
                    'low' => '<span class="badge bg-info"><i class="bi bi-arrow-down"></i> Low</span>',
                    'normal' => '<span class="badge bg-secondary">Normal</span>',
                    'high' => '<span class="badge bg-warning"><i class="bi bi-arrow-up"></i> High</span>',
                    'urgent' => '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill"></i> Urgent</span>',
                ];

                return $badges[$contact->priority] ?? '<span class="badge bg-secondary">Normal</span>';
            })
            ->addColumn('assigned_to_badge', function($contact) {
                if (!$contact->assignedAdmin) {
                    if (auth('admin')->user()->hasPermission('contact_us.update')) {
                        return '
                            <button type="button" class="btn btn-sm btn-outline-secondary assign-contact-btn"
                                data-id="' . $contact->id . '"
                                data-name="' . htmlspecialchars($contact->name) . '"
                                title="Assign">
                                <i class="bi bi-person-plus"></i> Assign
                            </button>
                        ';
                    }
                    return '<span class="text-muted"><i class="bi bi-dash-circle"></i> Unassigned</span>';
                }

                $html = '<div class="d-flex align-items-center">';
                $html .= '<div class="avatar-circle me-2">';
                $html .= strtoupper(substr($contact->assignedAdmin->name, 0, 1));
                $html .= '</div>';
                $html .= '<div>';
                $html .= '<small>' . htmlspecialchars($contact->assignedAdmin->name) . '</small>';
                $html .= '</div>';
                $html .= '</div>';

                return $html;
            })
            ->addColumn('created_at_formatted', function($contact) {
                $html = '<div class="created-at-container">';
                $html .= '<div>' . $contact->created_at->format('M d, Y') . '</div>';
                $html .= '<small class="text-muted">' . $contact->created_at->format('h:i A') . '</small>';
                $html .= '<div><small class="text-muted">' . $contact->created_at->diffForHumans() . '</small></div>';
                $html .= '</div>';

                return $html;
            })
            ->addColumn('read_status', function($contact) {
                if (!$contact->read_at) {
                    return '<span class="badge bg-primary">Unread</span>';
                }

                return '<span class="badge bg-secondary" title="Read at: ' . $contact->read_at->format('M d, Y h:i A') . '">Read</span>';
            })
            ->addColumn('actions', function($contact) {
                $actions = '<div class="btn-group" role="group">';

                // View button
                if (auth('admin')->user()->hasPermission('contact_us.read')) {
                    $actions .= '<a href="' . route('admin.contact-us.show', $contact->id) . '" class="btn btn-sm btn-info" title="View">
                        <i class="bi bi-eye"></i>
                    </a>';
                }

                // Quick Reply button
                if (auth('admin')->user()->hasPermission('contact_us.update')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-success quick-reply-btn"
                        data-id="' . $contact->id . '"
                        data-email="' . htmlspecialchars($contact->email) . '"
                        data-name="' . htmlspecialchars($contact->name) . '"
                        data-subject="' . htmlspecialchars($contact->subject) . '"
                        title="Quick Reply">
                        <i class="bi bi-reply-fill"></i>
                    </button>';

                    // Status Update button
                    $actions .= '<button type="button" class="btn btn-sm btn-warning update-status-btn"
                        data-id="' . $contact->id . '"
                        data-status="' . $contact->status_key_code . '"
                        title="Update Status">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>';
                }

                // Delete button
                if (auth('admin')->user()->hasPermission('contact_us.delete')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-danger delete-contact" data-id="' . $contact->id . '" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>';
                }

                $actions .= '</div>';

                return $actions;
            })
            ->addColumn('time_info', function($contact) {
                $html = '<div class="time-info">';

                // Created at
                $html .= '<div class="mb-1">';
                $html .= '<small class="text-muted"><i class="bi bi-calendar-plus"></i> Created:</small><br>';
                $html .= '<small>' . $contact->created_at->format('M d, Y h:i A') . '</small>';
                $html .= '</div>';

                // Read at
                if ($contact->read_at) {
                    $html .= '<div class="mb-1">';
                    $html .= '<small class="text-muted"><i class="bi bi-eye"></i> Read:</small><br>';
                    $html .= '<small>' . $contact->read_at->format('M d, Y h:i A') . '</small>';
                    $html .= '</div>';
                }

                // Resolved at
                if ($contact->resolved_at) {
                    $html .= '<div>';
                    $html .= '<small class="text-muted"><i class="bi bi-check-circle"></i> Resolved:</small><br>';
                    $html .= '<small>' . $contact->resolved_at->format('M d, Y h:i A') . '</small>';
                    $html .= '</div>';
                }

                $html .= '</div>';

                return $html;
            })
            ->rawColumns(['checkbox', 'contact_info', 'subject_message', 'status_badge', 'priority_badge', 'assigned_to_badge', 'created_at_formatted', 'read_status', 'actions', 'time_info'])
            ->make(true);
    }

    /**
     * Display the specified contact message
     */
    public function show($id)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('contact_us.read')) {
            abort(403, 'Unauthorized access');
        }

        $contact = ContactUs::with(['status', 'assignedAdmin'])->findOrFail($id);

        // Mark as read when viewed
        if (!$contact->read_at) {
            $contact->read_at = now();
            $contact->save();
        }

        // Get status list
        $statusList = SystemStatus::where('module', 'contact_us')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        // Get admin users for assignment
        $adminUsers = AdminUser::active()
            ->get();

        return view('admin.contact-us.show', compact('contact', 'statusList', 'adminUsers'));
    }

    /**
     * Update the status of contact message
     */
    public function updateStatus(Request $request, $id)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('contact_us.update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status_key_code' => 'required|exists:system_statuses,key_code',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $contact = ContactUs::findOrFail($id);
        $contact->status_key_code = $request->status_key_code;

        if ($request->filled('admin_notes')) {
            $contact->admin_notes = $request->admin_notes;
        }

        // If status is resolved, set resolved_at
        $status = SystemStatus::where('key_code', $request->status_key_code)->first();
        if ($status && $status->is_final && !$contact->resolved_at) {
            $contact->resolved_at = now();
        }

        $contact->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully!'
        ]);
    }

    /**
     * Assign contact to admin user
     */
    public function assign(Request $request, $id)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('contact_us.update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $contact = ContactUs::findOrFail($id);
        $contact->assigned_to = $request->assigned_to;
        $contact->save();

        return response()->json([
            'success' => true,
            'message' => 'Contact assigned successfully!'
        ]);
    }

    /**
     * Update priority
     */
    public function updatePriority(Request $request, $id)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('contact_us.update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'priority' => 'required|in:low,normal,high,urgent',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $contact = ContactUs::findOrFail($id);
        $contact->priority = $request->priority;
        $contact->save();

        return response()->json([
            'success' => true,
            'message' => 'Priority updated successfully!'
        ]);
    }

    /**
     * Delete contact message
     */
    public function destroy($id)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('contact_us.delete')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $contact = ContactUs::findOrFail($id);
        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact message deleted successfully!'
        ]);
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('contact_us.update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'contact_ids' => 'required|array',
            'contact_ids.*' => 'exists:contact_us,id',
            'status_key_code' => 'required|exists:system_statuses,key_code',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        ContactUs::whereIn('id', $request->contact_ids)
            ->update(['status_key_code' => $request->status_key_code]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated for ' . count($request->contact_ids) . ' contacts!'
        ]);
    }

    /**
     * Bulk delete
     */
    public function bulkDelete(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('contact_us.delete')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'contact_ids' => 'required|array',
            'contact_ids.*' => 'exists:contact_us,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        ContactUs::whereIn('id', $request->contact_ids)->delete();

        return response()->json([
            'success' => true,
            'message' => count($request->contact_ids) . ' contacts deleted successfully!'
        ]);
    }

    /**
     * Bulk assign
     */
    public function bulkAssign(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('contact_us.update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'contact_ids' => 'required|array',
            'contact_ids.*' => 'exists:contact_us,id',
            'assigned_to' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        ContactUs::whereIn('id', $request->contact_ids)
            ->update(['assigned_to' => $request->assigned_to]);

        return response()->json([
            'success' => true,
            'message' => count($request->contact_ids) . ' contacts assigned successfully!'
        ]);
    }

    /**
     * Mark as read
     */
    public function markAsRead($id)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('contact_us.update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $contact = ContactUs::findOrFail($id);

        if (!$contact->read_at) {
            $contact->read_at = now();
            $contact->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Marked as read!'
        ]);
    }

    /**
     * Bulk mark as read
     */
    public function bulkMarkAsRead(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('contact_us.update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'contact_ids' => 'required|array',
            'contact_ids.*' => 'exists:contact_us,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        ContactUs::whereIn('id', $request->contact_ids)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Contacts marked as read!'
        ]);
    }

    /**
     * Export contacts to CSV
     */
    public function export(Request $request)
    {
        // Check permission
        if (!auth('admin')->user()->hasPermission('contact_us.read')) {
            abort(403, 'Unauthorized access');
        }

        $query = ContactUs::with(['status', 'assignedAdmin']);

        // Apply same filters as getData
        if ($request->filled('status')) {
            $query->where('status_key_code', $request->status);
        }

        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        $contacts = $query->get();

        $filename = 'contact_us_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($contacts) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'ID',
                'Name',
                'Email',
                'Phone',
                'Subject',
                'Message',
                'Status',
                'Priority',
                'Assigned To',
                'Read At',
                'Resolved At',
                'Created At'
            ]);

            // Data
            foreach ($contacts as $contact) {
                fputcsv($file, [
                    $contact->id,
                    $contact->name,
                    $contact->email,
                    $contact->phone,
                    $contact->subject,
                    $contact->message,
                    $contact->status ? $contact->status->name : 'N/A',
                    $contact->priority ? ucfirst($contact->priority) : 'Normal',
                    $contact->assignedAdmin ? $contact->assignedAdmin->name : 'Unassigned',
                    $contact->read_at ? $contact->read_at->format('Y-m-d H:i:s') : 'N/A',
                    $contact->resolved_at ? $contact->resolved_at->format('Y-m-d H:i:s') : 'N/A',
                    $contact->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

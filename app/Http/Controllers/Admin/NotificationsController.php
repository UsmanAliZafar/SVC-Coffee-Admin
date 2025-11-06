<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\NotificationLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationsController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display notifications list
     */
    public function index(Request $request)
    {
        $adminId = auth('admin')->id();

        // Get statistics
        $stats = Notification::getStatsForAdmin($adminId);

        // Get categories for filter
        $categories = config('notifications.categories', []);
        // dd($unreadCount);
        return view('admin.notifications.index', compact('stats', 'categories'));
    }

    /**
     * Get notifications data for DataTable (AJAX)
     */
    public function getNotificationsdata(Request $request)
    {
        $adminId = auth('admin')->id();

        $query = Notification::where('admin_user_id', $adminId)->orderBy('created_at', 'desc')
            ->notExpired();

        // Apply filters
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->unread();
            } elseif ($request->status === 'read') {
                $query->read();
            }
        }

        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        return datatables()->eloquent($query)
            ->addColumn('badge_html', function ($notification) {
                return $notification->getBadgeHtml();
            })
            ->addColumn('priority_badge', function ($notification) {
                return $notification->getPriorityBadge();
            })
            ->addColumn('time_ago', function ($notification) {
                return $notification->getTimeAgo();
            })
            ->addColumn('action', function ($notification) {
                $html = '';

                if ($notification->action_url) {
                    $html .= '<a href="' . $notification->action_url . '" class="btn btn-sm btn-outline-primary me-1">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>';
                }

                if ($notification->isUnread()) {
                    $html .= '<button class="btn btn-sm btn-outline-success mark-read-btn me-1" data-id="' . $notification->id . '">
                        <i class="bi bi-check"></i>
                    </button>';
                }

                $html .= '<button class="btn btn-sm btn-outline-danger delete-notification-btn" data-id="' . $notification->id . '">
                    <i class="bi bi-trash"></i>
                </button>';

                return $html;
            })
            ->rawColumns(['badge_html', 'priority_badge', 'action', 'title'])
            ->make(true);
    }

    /**
     * Display notification settings page
     */
    public function settings()
    {
        $adminId = auth('admin')->id();

        // Get all notification settings for this admin
        $settings = NotificationSetting::getAllForAdmin($adminId);

        // Group by category
        $groupedSettings = $settings->groupBy(function($setting) {
            $config = $setting->getConfig();
            return $config['category'] ?? 'general';
        });

        // Get categories
        $categories = config('notifications.categories', []);

        return view('admin.notifications.settings', compact('groupedSettings', 'categories'));
    }

    /**
     * Update notification settings
     */
    public function updateSettings(Request $request)
    {
        try {
            $adminId = auth('admin')->id();

            $validated = $request->validate([
                'settings' => 'required|array',
                'settings.*.notification_type' => 'required|string',
                'settings.*.is_enabled' => 'sometimes|boolean',
                'settings.*.send_email' => 'sometimes|boolean',
                'settings.*.email_address' => 'nullable|email',
                'settings.*.threshold_value' => 'nullable|integer|min:0',
            ]);

            DB::beginTransaction();

            foreach ($validated['settings'] as $settingData) {
                NotificationSetting::updateOrCreate(
                    [
                        'admin_user_id' => $adminId,
                        'notification_type' => $settingData['notification_type'],
                    ],
                    [
                        'is_enabled' => $settingData['is_enabled'] ?? false,
                        'send_email' => $settingData['send_email'] ?? false,
                        'email_address' => $settingData['email_address'] ?? null,
                        'threshold_value' => $settingData['threshold_value'] ?? null,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Notification settings updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset settings to defaults
     */
    public function resetSettings()
    {
        try {
            $adminId = auth('admin')->id();

            NotificationSetting::resetToDefaultsForAdmin($adminId);

            return response()->json([
                'success' => true,
                'message' => 'Settings reset to defaults successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            $adminId = auth('admin')->id();

            $notification = Notification::where('id', $id)
                ->where('admin_user_id', $adminId)
                ->firstOrFail();

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            $adminId = auth('admin')->id();

            $count = Notification::markAllAsReadForAdmin($adminId);

            return response()->json([
                'success' => true,
                'message' => "{$count} notification(s) marked as read",
                'count' => $count
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notifications as read'
            ], 500);
        }
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        try {
            $adminId = auth('admin')->id();

            $notification = Notification::where('id', $id)
                ->where('admin_user_id', $adminId)
                ->firstOrFail();

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }
    }

    /**
     * Clear all read notifications
     */
    public function clearRead()
    {
        try {
            $adminId = auth('admin')->id();

            $count = Notification::deleteReadForAdmin($adminId);

            return response()->json([
                'success' => true,
                'message' => "{$count} read notification(s) cleared",
                'count' => $count
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear notifications'
            ], 500);
        }
    }

    /**
     * Clear all notifications
     */
    public function clearAll()
    {
        try {
            $adminId = auth('admin')->id();

            $count = Notification::deleteAllForAdmin($adminId);

            return response()->json([
                'success' => true,
                'message' => "{$count} notification(s) cleared",
                'count' => $count
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear notifications'
            ], 500);
        }
    }

    /**
     * Get unread notifications for dropdown (AJAX)
     */
    public function getUnread()
    {
        try {
            $adminId = auth('admin')->id();

            $notifications = Notification::getRecentUnreadForAdmin($adminId, 10);
            $unreadCount = Notification::getUnreadCountForAdmin($adminId);

            return response()->json([
                'success' => true,
                'notifications' => $notifications->map(function($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'icon' => $notification->icon,
                        'color' => $notification->color,
                        'action_url' => $notification->action_url,
                        'time_ago' => $notification->getTimeAgo(),
                        'priority' => $notification->priority,
                    ];
                }),
                'unread_count' => $unreadCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load notifications'
            ], 500);
        }
    }

    /**
     * Get notification statistics
     */
    public function statistics()
    {
        try {
            $adminId = auth('admin')->id();

            $stats = Notification::getStatsForAdmin($adminId);
            $logStats = NotificationLog::getStatsForAdmin($adminId);

            return response()->json([
                'success' => true,
                'notification_stats' => $stats,
                'email_stats' => $logStats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics'
            ], 500);
        }
    }

    /**
     * Test notification (for debugging)
     */
    public function test()
    {
        try {
            $adminId = auth('admin')->id();

            $this->notificationService->notify('order_created', [
                'order_id' => 'test-123',
                'order_number' => 'TEST-001',
                'total_amount' => '$100.00',
                'customer_name' => 'Test Customer',
            ], $adminId);

            return response()->json([
                'success' => true,
                'message' => 'Test notification sent successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification: ' . $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactUs extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'contact_us';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status_key_code',
        'priority',
        'ip_address',
        'user_agent',
        'assigned_to',
        'admin_notes',
        'read_at',
        'resolved_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'read_at' => 'datetime',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationship: Status
     */
    public function status()
    {
        return $this->belongsTo(SystemStatus::class, 'status_key_code', 'key_code');
    }

    /**
     * Relationship: Assigned admin user
     */
    public function assignedAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge(): string
    {
        if (!$this->status) {
            return '<span class="badge bg-secondary">No Status</span>';
        }

        $bgColor = $this->status->bg_color ?: '#6c757d';
        $textColor = $this->status->color ?: '#ffffff';
        $icon = $this->status->icon ?: '';

        $iconHtml = $icon ? '<i class="' . $icon . '"></i> ' : '';

        return '<span class="badge" style="background-color: ' . $bgColor . '; color: ' . $textColor . ';">'
            . $iconHtml . htmlspecialchars($this->status->name) .
            '</span>';
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, string $statusKeyCode)
    {
        return $query->where('status_key_code', $statusKeyCode);
    }

    /**
     * Scope: Unread messages
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope: Read messages
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope: Recent messages
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: By priority
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope: Assigned messages
     */
    public function scopeAssigned($query)
    {
        return $query->whereNotNull('assigned_to');
    }

    /**
     * Scope: Unassigned messages
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    /**
     * Mark as read
     */
    public function markAsRead(): bool
    {
        if (!$this->read_at) {
            $this->read_at = now();
            return $this->save();
        }
        return false;
    }

    /**
     * Mark as resolved
     */
    public function markAsResolved(): bool
    {
        $resolvedStatus = SystemStatus::where('module', 'contact_us')
            ->where('key_code', 'CONTACT_RESOLVED')
            ->first();

        if ($resolvedStatus) {
            $this->status_key_code = $resolvedStatus->key_code;
            $this->resolved_at = now();
            return $this->save();
        }

        return false;
    }

    /**
     * Update status
     */
    public function updateStatus(string $statusKeyCode): bool
    {
        $status = SystemStatus::where('key_code', $statusKeyCode)->first();

        if ($status) {
            $this->status_key_code = $statusKeyCode;

            // If status is final and not resolved yet, set resolved_at
            if ($status->is_final && !$this->resolved_at) {
                $this->resolved_at = now();
            }

            return $this->save();
        }

        return false;
    }

    /**
     * Check if message is new
     */
    public function isNew(): bool
    {
        return $this->status_key_code === 'CONTACT_NEW';
    }

    /**
     * Check if message is pending
     */
    public function isPending(): bool
    {
        return $this->status_key_code === 'CONTACT_PENDING';
    }

    /**
     * Check if message is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status_key_code === 'CONTACT_IN_PROGRESS';
    }

    /**
     * Check if message is resolved
     */
    public function isResolved(): bool
    {
        return $this->status_key_code === 'CONTACT_RESOLVED';
    }

    /**
     * Check if message is closed
     */
    public function isClosed(): bool
    {
        return $this->status_key_code === 'CONTACT_CLOSED';
    }

    /**
     * Check if message is spam
     */
    public function isSpam(): bool
    {
        return $this->status_key_code === 'CONTACT_SPAM';
    }

    /**
     * Check if message requires follow up
     */
    public function requiresFollowUp(): bool
    {
        return $this->status_key_code === 'CONTACT_FOLLOW_UP';
    }

    /**
     * Check if message is escalated
     */
    public function isEscalated(): bool
    {
        return $this->status_key_code === 'CONTACT_ESCALATED';
    }

    /**
     * Check if message is on hold
     */
    public function isOnHold(): bool
    {
        return $this->status_key_code === 'CONTACT_ON_HOLD';
    }

    /**
     * Get priority label
     */
    public function getPriorityLabel(): string
    {
        $labels = [
            'low' => 'Low',
            'normal' => 'Normal',
            'high' => 'High',
            'urgent' => 'Urgent',
        ];

        return $labels[$this->priority] ?? 'Normal';
    }

    /**
     * Get priority color
     */
    public function getPriorityColor(): string
    {
        $colors = [
            'low' => 'info',
            'normal' => 'secondary',
            'high' => 'warning',
            'urgent' => 'danger',
        ];

        return $colors[$this->priority] ?? 'secondary';
    }
}

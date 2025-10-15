<?php
// app/Helpers/StatusHelper.php

use App\Models\SystemStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

if (!function_exists('getModuleStatus')) {
    /**
     * Get current status for a module entity
     *
     * @param string $module Module name (categories, products, orders, etc.)
     * @param string|null $statusKeyCode Status key code from request or session
     * @param object|null $entity Entity object (e.g., Product, Order)
     * @return SystemStatus
     */
    function getModuleStatus(string $module, ?string $statusKeyCode = null, ?object $entity = null): SystemStatus
    {
        // 1. Request parameter first (but validate)
        $statusFromRequest = request('status') ?? $statusKeyCode;
        if ($statusFromRequest && isValidStatusKeyCode($statusFromRequest, $module)) {
            $status = SystemStatus::getByKeyCode($statusFromRequest);
            if ($status && $status->module === $module) {
                return $status;
            }
        }

        // 2. Entity's current status
        if ($entity && isset($entity->status_key_code)) {
            $entityStatus = SystemStatus::getByKeyCode($entity->status_key_code);
            if ($entityStatus && $entityStatus->module === $module) {
                return $entityStatus;
            }
        }

        // 3. Session stored status for this module
        $sessionKey = "status_{$module}";
        if (session()->has($sessionKey)) {
            $sessionStatusCode = session($sessionKey);
            $sessionStatus = SystemStatus::getByKeyCode($sessionStatusCode);
            if ($sessionStatus && $sessionStatus->module === $module) {
                return $sessionStatus;
            }
        }

        // 4. Default status for module
        $defaultStatus = SystemStatus::getDefaultForModule($module);
        if ($defaultStatus) {
            return $defaultStatus;
        }

        // 5. Fallback: First active status for module
        $firstStatus = SystemStatus::byModule($module)
            ->active()
            ->ordered()
            ->first();

        if ($firstStatus) {
            return $firstStatus;
        }

        // Log error if no status found
        Log::warning("No status found for module: {$module}");

        // Return a temporary status object to prevent errors
        return new SystemStatus([
            'module' => $module,
            'name' => 'Unknown',
            'key_code' => strtoupper($module) . '_UNKNOWN',
            'color' => '#6c757d',
            'bg_color' => '#f8f9fa',
            'is_active' => true,
        ]);
    }
}

if (!function_exists('isValidStatusKeyCode')) {
    /**
     * Validate if status key code exists and belongs to module
     *
     * @param string $keyCode Status key code
     * @param string|null $module Module name (optional)
     * @return bool
     */
    function isValidStatusKeyCode(string $keyCode, ?string $module = null): bool
    {
        $cacheKey = "valid_status_{$keyCode}" . ($module ? "_{$module}" : '');

        return Cache::remember($cacheKey, 3600, function () use ($keyCode, $module) {
            $query = SystemStatus::where('key_code', $keyCode)->active();

            if ($module) {
                $query->where('module', $module);
            }

            return $query->exists();
        });
    }
}

if (!function_exists('getStatusByKey')) {
    /**
     * Get status by key code with caching
     *
     * @param string $keyCode Status key code
     * @return SystemStatus|null
     */
    function getStatusByKey(string $keyCode): ?SystemStatus
    {
        return Cache::remember("status_{$keyCode}", 3600, function () use ($keyCode) {
            return SystemStatus::getByKeyCode($keyCode);
        });
    }
}

if (!function_exists('getModuleStatuses')) {
    /**
     * Get all active statuses for a module with caching
     *
     * @param string $module Module name
     * @param bool $includeInactive Include inactive statuses
     * @return \Illuminate\Support\Collection
     */
    function getModuleStatuses(string $module, bool $includeInactive = false)
    {
        $cacheKey = "statuses_{$module}" . ($includeInactive ? '_all' : '_active');

        return Cache::remember($cacheKey, 3600, function () use ($module, $includeInactive) {
            $query = SystemStatus::byModule($module);

            if (!$includeInactive) {
                $query->active();
            }

            return $query->ordered()->get();
        });
    }
}

if (!function_exists('getDefaultStatus')) {
    /**
     * Get default status for a module
     *
     * @param string $module Module name
     * @return SystemStatus|null
     */
    function getDefaultStatus(string $module): ?SystemStatus
    {
        return Cache::remember("default_status_{$module}", 3600, function () use ($module) {
            return SystemStatus::getDefaultForModule($module);
        });
    }
}

if (!function_exists('canTransitionStatus')) {
    /**
     * Check if status transition is allowed
     *
     * @param string $fromKeyCode Current status key code
     * @param string $toKeyCode Target status key code
     * @return bool
     */
    function canTransitionStatus(string $fromKeyCode, string $toKeyCode): bool
    {
        $fromStatus = getStatusByKey($fromKeyCode);

        if (!$fromStatus) {
            return false;
        }

        return $fromStatus->canTransitionTo($toKeyCode);
    }
}

if (!function_exists('getStatusBadge')) {
    /**
     * Get status badge HTML
     *
     * @param string $keyCode Status key code
     * @param bool $withIcon Include icon
     * @return string
     */
    function getStatusBadge(string $keyCode, bool $withIcon = true): string
    {
        $status = getStatusByKey($keyCode);

        if (!$status) {
            return '<span class="badge bg-secondary">Unknown</span>';
        }

        if (!$withIcon) {
            return sprintf(
                '<span class="badge" style="background-color: %s; color: %s;">%s</span>',
                $status->bg_color,
                $status->color,
                $status->name
            );
        }

        return $status->getBadgeHtml();
    }
}

if (!function_exists('getAvailableStatusTransitions')) {
    /**
     * Get available status transitions for current status
     *
     * @param string $currentKeyCode Current status key code
     * @return \Illuminate\Support\Collection
     */
    function getAvailableStatusTransitions(string $currentKeyCode)
    {
        $currentStatus = getStatusByKey($currentKeyCode);

        if (!$currentStatus) {
            return collect();
        }

        return $currentStatus->getTransitionStatuses();
    }
}

if (!function_exists('setModuleStatus')) {
    /**
     * Set status for a module entity and store in session
     *
     * @param object $entity Entity object
     * @param string $keyCode Status key code
     * @param string|null $module Module name
     * @return bool
     */
    function setModuleStatus(object $entity, string $keyCode, ?string $module = null): bool
    {
        $status = getStatusByKey($keyCode);

        if (!$status) {
            Log::warning("Invalid status key code: {$keyCode}");
            return false;
        }

        // Validate module if provided
        if ($module && $status->module !== $module) {
            Log::warning("Status {$keyCode} does not belong to module {$module}");
            return false;
        }

        try {
            // Update entity
            $entity->status_key_code = $keyCode;
            $entity->save();

            // Store in session for quick access
            session(["status_{$status->module}" => $keyCode]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to set status: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('getStatusColor')) {
    /**
     * Get status color for a key code
     *
     * @param string $keyCode Status key code
     * @param bool $background Get background color instead
     * @return string
     */
    function getStatusColor(string $keyCode, bool $background = false): string
    {
        $status = getStatusByKey($keyCode);

        if (!$status) {
            return $background ? '#f8f9fa' : '#6c757d';
        }

        return $background ? $status->bg_color : $status->color;
    }
}

if (!function_exists('isStatusFinal')) {
    /**
     * Check if status is final/terminal
     *
     * @param string $keyCode Status key code
     * @return bool
     */
    function isStatusFinal(string $keyCode): bool
    {
        $status = getStatusByKey($keyCode);

        return $status ? $status->is_final : false;
    }
}

if (!function_exists('clearStatusCache')) {
    /**
     * Clear status cache for a module or all modules
     *
     * @param string|null $module Module name (null for all)
     * @return void
     */
    function clearStatusCache(?string $module = null): void
    {
        if ($module) {
            Cache::forget("statuses_{$module}");
            Cache::forget("statuses_{$module}_all");
            Cache::forget("default_status_{$module}");
        } else {
            Cache::flush();
        }

        Log::info("Status cache cleared" . ($module ? " for module: {$module}" : " (all modules)"));
    }
}

if (!function_exists('getStatusesDropdown')) {
    /**
     * Get statuses for dropdown/select with key-value pairs
     *
     * @param string $module Module name
     * @param bool $includeInactive Include inactive statuses
     * @return array
     */
    function getStatusesDropdown(string $module, bool $includeInactive = false): array
    {
        return getModuleStatuses($module, $includeInactive)
            ->pluck('name', 'key_code')
            ->toArray();
    }
}

if (!function_exists('hasStatusPermission')) {
    /**
     * Check if current admin user has permission to set a status
     *
     * @param string $keyCode Status key code
     * @return bool
     */
    function hasStatusPermission(string $keyCode): bool
    {
        if (!auth('admin')->check()) {
            return false;
        }

        $status = getStatusByKey($keyCode);

        if (!$status) {
            return false;
        }

        return $status->userHasPermission(auth('admin')->user());
    }
}

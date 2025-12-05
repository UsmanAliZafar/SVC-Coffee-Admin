<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class AdminUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'phone',
        'is_active',
        'last_login_at',
        'last_login_ip'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Automatically hash password only if it's not already hashed
     */
    public function setPasswordAttribute($value)
    {
        // Check if password is already hashed (bcrypt hashes start with $2y$)
        if (!empty($value) && !preg_match('/^\$2y\$/', $value)) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }

    // Relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'admin_user_roles');
    }

    // Permission methods
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles()->where('name', $role)->exists();
        }
        return $this->roles()->where('id', $role->id)->exists();
    }

    public function hasPermission($permission)
    {
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function hasAnyPermission(array $permissions)
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function getAllPermissions()
    {
        $permissions = collect();
        foreach ($this->roles as $role) {
            $permissions = $permissions->merge($role->permissions);
        }
        return $permissions->unique('id');
    }

    // Update last login
    public function updateLastLogin($request)
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

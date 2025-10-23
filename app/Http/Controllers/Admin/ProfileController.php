<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the admin profile edit form
     */
    public function edit()
    {
        $admin = auth('admin')->user();
        $admin->load('roles');

        return view('admin.profile.edit', compact('admin'));
    }

    /**
     * Update admin profile (name, username, phone)
     */
    public function update(Request $request)
    {
        $admin = auth('admin')->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('admin_users')->ignore($admin->id)
            ],
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $admin->update([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
        ]);

        return redirect()->back()
                        ->with('success', 'Profile updated successfully.');
    }

    /**
     * Update admin password
     */
    public function updatePassword(Request $request)
    {
        $admin = auth('admin')->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'new_password.different' => 'New password must be different from current password.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->with('password_error', true);
        }

        // Check if current password is correct
        if (!Hash::check($request->current_password, $admin->password)) {
            return redirect()->back()
                           ->withErrors(['current_password' => 'Current password is incorrect.'])
                           ->with('password_error', true);
        }

        // Update password
        $admin->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->back()
                        ->with('password_success', 'Password updated successfully.');
    }
}

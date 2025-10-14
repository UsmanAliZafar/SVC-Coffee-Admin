<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Product;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function index()
    {
        // Dashboard statistics (you can customize based on your needs)
        $stats = [
            'total_products' => 0, // Product::count() when you create products table
            'total_orders' => 0,   // Order::count() when you create orders table
            'total_customers' => 0, // Customer::count() when you create customers table
            'active_admins' => AdminUser::where('is_active', true)->count(),
        ];

        $user = auth('admin')->user();

        return view('admin.dashboard', compact('stats', 'user'));
    }
}

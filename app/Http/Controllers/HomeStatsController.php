<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;

class HomeStatsController extends Controller
{
    public function index()
    {
        $totalVendors = User::where('role', 'vendor')->count();
        $totalProducts = Product::count();
        $totalOrders = Order::where('status', 'approved')->count();

        // Your platform started in 2024
        $startYear = 2024;
        $yearsExperience = Carbon::now()->year - $startYear;

        return response()->json([
            'vendors' => $totalVendors,
            'products' => $totalProducts,
            'orders' => $totalOrders,
            'experience' => $yearsExperience,
        ]);
    }
}

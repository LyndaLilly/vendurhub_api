<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class UserController extends Controller
{

    public function index()
    {
        $users = User::with('profile')
            ->whereHas('profile', function ($query) {
                $query->where('profile_updated', 1);
            })
            ->get();

        return response()->json(['users' => $users]);
    }

    public function show($id)
    {
        $user = User::with(['profile', 'products.images', 'deliveryLocations'])->findOrFail($id);

        return response()->json(['user' => $user]);
    }

    public function searchVendors(Request $request)
    {
        try {
            $query = trim($request->input('q'));

            \Log::info('🔍 Vendor search started', ['query' => $query]);

            $vendors = User::with(['profile', 'products.images', 'deliveryLocations'])
                ->where(function ($qBuilder) use ($query) {
                    $qBuilder->where('firstname', 'LIKE', '%' . $query . '%')
                        ->orWhere('lastname', 'LIKE', '%' . $query . '%')
                        ->orWhere('email', 'LIKE', '%' . $query . '%')
                        ->orWhereHas('profile', function ($q) use ($query) {
                            $q->where('profile_updated', 1)
                                ->where(function ($q2) use ($query) {
                                    $q2->where('business_name', 'LIKE', '%' . $query . '%')
                                        ->orWhere('email', 'LIKE', '%' . $query . '%')
                                        ->orWhere('contact_number_whatsapp', 'LIKE', '%' . $query . '%');
                                });
                        })
                        ->orWhereHas('deliveryLocations', function ($q) use ($query) {
                            $q->where('country', 'LIKE', '%' . $query . '%')
                                ->orWhere('state', 'LIKE', '%' . $query . '%')
                                ->orWhere('city', 'LIKE', '%' . $query . '%');
                        })
                        ->orWhereHas('products', function ($q) use ($query) {
                            $q->where('name', 'LIKE', '%' . $query . '%')
                                ->orWhere('product_number', 'LIKE', '%' . $query . '%');
                        });
                })
                ->get();

            \Log::info('✅ Vendor search completed', ['results_count' => $vendors->count()]);

            return response()->json([
                'message' => 'Vendor search results',
                'vendors' => $vendors,
            ]);
        } catch (\Exception $e) {
            \Log::error('Vendor search error: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

public function topVendors()
{
    try {
        Log::info('🚀 topVendors() called');

        // Step 1: Fetch vendors who have at least 1 order AND at least 1 product
        $topVendors = User::with('profile')
            ->withCount(['orders', 'products'])
            ->having('orders_count', '>', 0)
            ->having('products_count', '>', 0)
            ->orderByDesc('orders_count')     // sort by orders first
            ->orderByDesc('products_count')   // then by products count
            ->take(3)                         // top 3 vendors
            ->get();

        Log::info('✅ Top vendors fetched', ['count' => $topVendors->count()]);

        // Step 2: Attach latest products for each vendor
        foreach ($topVendors as $vendor) {
            $latestProducts = Product::with('images')
                ->where('user_id', $vendor->id)
                ->latest()
                ->take(5)
                ->get();

            $vendor->setRelation('latest_products', $latestProducts);

            Log::info('🛒 Latest products fetched', [
                'vendor_id' => $vendor->id,
                'count' => $latestProducts->count()
            ]);
        }

        // Step 3: Return response
        Log::info('✅ Returning top vendor data.');
        return response()->json([
            'message' => 'Top vendors with latest products retrieved successfully',
            'vendors' => $topVendors,
        ]);

    } catch (\Throwable $e) {
        Log::error('❌ Error in topVendors()', [
            'error' => $e->getMessage(),
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'message' => 'An unexpected error occurred in topVendors().',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function recentVendors()
{
    try {
        Log::info('🚀 recentVendors() called');

        // Fetch 6 most recently registered users with completed profiles
        $recentVendors = User::with('profile')
            ->whereHas('profile', function ($query) {
                $query->where('profile_updated', 1);
            })
            ->orderByDesc('created_at')
            ->take(6)
            ->get(); // 👈 remove column restriction here

        Log::info('✅ Recent vendors fetched', ['count' => $recentVendors->count()]);

        return response()->json([
            'message' => '6 most recently registered vendors retrieved successfully',
            'vendors' => $recentVendors,
        ]);

    } catch (\Throwable $e) {
        Log::error('❌ Error in recentVendors()', [
            'error' => $e->getMessage(),
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
        ]);

        return response()->json([
            'message' => 'An unexpected error occurred while fetching recent vendors.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


}
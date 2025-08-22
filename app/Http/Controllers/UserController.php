<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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

}

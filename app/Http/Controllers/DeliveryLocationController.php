<?php
namespace App\Http\Controllers;

use App\Models\DeliveryLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryLocationController extends Controller
{
    // 🔹 Get all delivery locations for the authenticated user
    public function index(Request $request)
    {
        $userId = Auth::id();
        \Log::info("Fetching locations for user ID: " . $userId);

        $locations = DeliveryLocation::where('user_id', $userId)->get();

        return response()->json([
            'locations' => $locations,
        ]);
    }

    // 🔹 Store new delivery locations
    public function store(Request $request)
    {
        $request->validate([
            'locations'                  => 'required|array|min:1',
            'locations.*.other_country'  => 'required|boolean',
            'locations.*.delivery_price' => 'nullable|numeric',
            'locations.*.note'           => 'nullable|string',
            'locations.*.country'        => 'required_if:locations.*.other_country,false|string|nullable',
            'locations.*.state'          => 'required_if:locations.*.other_country,false|string|nullable',
            'locations.*.city'           => 'required_if:locations.*.other_country,false|string|nullable',
        ]);

        $userId = Auth::id();
        $saved  = [];

        foreach ($request->locations as $item) {
            $isOther = $item['other_country'];

            $saved[] = DeliveryLocation::create([
                'user_id'        => $userId,
                'delivery_price' => $item['delivery_price'] ?? null,
                'other_country'  => $isOther,
                'note'           => $item['note'] ?? null,
                'country'        => $isOther ? null : $item['country'],
                'state'          => $isOther ? null : $item['state'],
                'city'           => $isOther ? null : $item['city'],
            ]);
        }

        return response()->json([
            'message'   => 'Delivery locations added successfully',
            'locations' => $saved,
        ]);
    }

    // 🔹 Update a delivery location
    public function update(Request $request, $id)
    {
        $location = DeliveryLocation::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'other_country'  => 'required|boolean',
            'delivery_price' => 'nullable|numeric',
            'note'           => 'nullable|string',
            'country'        => 'required_if:other_country,false|string|nullable',
            'state'          => 'required_if:other_country,false|string|nullable',
            'city'           => 'required_if:other_country,false|string|nullable',
        ]);

        $isOther = $request->boolean('other_country');

        $data = [
            'delivery_price' => $request->input('delivery_price'),
            'other_country'  => $isOther,
            'note'           => $request->input('note'),
            'country'        => $isOther ? null : $request->input('country'),
            'state'          => $isOther ? null : $request->input('state'),
            'city'           => $isOther ? null : $request->input('city'),
        ];

        $location->update($data);

        return response()->json([
            'message'  => 'Delivery location updated',
            'location' => $location,
        ]);
    }

    // 🔹 Delete a delivery location
    public function destroy($id)
    {
        $location = DeliveryLocation::where('user_id', Auth::id())->findOrFail($id);
        $location->delete();

        return response()->json(['message' => 'Delivery location deleted']);
    }

    // 🔹 Delete all delivery locations for the authenticated user
    public function destroyAll()
    {
        $userId = Auth::id();

        $deletedCount = DeliveryLocation::where('user_id', $userId)->delete();

        return response()->json([
            'message' => "$deletedCount delivery location(s) deleted successfully.",
        ]);
    }

    // 🔹 Public method to get delivery locations for a specific vendor
    public function getVendorLocations($vendorId)
    {
        $locations = DeliveryLocation::where('user_id', $vendorId)->get();

        return response()->json([
            'locations' => $locations,
        ]);
    }

}

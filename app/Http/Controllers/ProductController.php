<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Trial;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        // 1. Check if profile is updated
        if (! $user->profile_updated) {
            return response()->json([
                'message' => 'Please complete your profile first.',
            ], 403);
        }

        // 2. Subscription and trial check
        if (! $user->is_subscribed) {
            $trial = $user->trial;

            if (! $trial) {
                // Create new trial record and start now
                $trial = Trial::create([
                    'user_id'    => $user->id,
                    'started_at' => now(),
                ]);
            }

            $startedAt = Carbon::parse($trial->started_at);
            $trialEnds = $startedAt->copy()->addDays(3);

            if (now()->greaterThan($trialEnds)) {
                return response()->json([
                    'message'     => 'Your 3-day free trial has ended. Please subscribe to continue uploading products.',
                    'subscribe'   => true,
                    'payment_url' => route('paystack.init'),
                ], 402); // Payment Required
            }
        }

        // 3. Validate request (same as before)
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required',
            'price'       => 'required|numeric',
            'category_id' => 'required|exists:product_categories,id',
            'images'      => 'required|array|min:1|max:3',
            'images.*'    => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // 4. Create product
        $product = Product::create([
            'user_id'        => $user->id,
            'name'           => $request->name,
            'description'    => $request->description,
            'price'          => $request->price,
            'category_id'    => $request->category_id,
            'shareable_link' => Str::uuid(),
            'product_number' => $this->generateProductNumber(),
        ]);

        // 5. Upload and save images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');

                Log::info('Uploaded image stored at: ' . storage_path('app/public/' . $path));
                Log::info('Accessible URL: ' . url('storage/' . $path));

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                ]);
            }
        }

        return response()->json([
            'message'        => 'Product created successfully!',
            'product'        => $product->load('images', 'user.deliveryLocations'),
            'shareable_link' => url('/product/' . $product->shareable_link),
        ]);
    }

    protected function generateProductNumber()
    {
        do {
            $datePart   = now()->format('Ymd');
            $randomPart = strtoupper(Str::random(6));
            $number     = 'VHB-' . $datePart . '-' . $randomPart;
        } while (Product::where('product_number', $number)->exists());

        return $number;
    }

    // ✅ Edit product
    public function update(Request $request, $id)
    {
        $product = Product::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();

        $product->update($request->only(['name', 'description', 'price', 'category_id']));

        // Handle deleting images
        if ($request->has('images_to_delete')) {
            $imageIdsToDelete = json_decode($request->input('images_to_delete'), true);
            if (is_array($imageIdsToDelete)) {
                ProductImage::whereIn('id', $imageIdsToDelete)->where('product_id', $product->id)->delete();
                // optionally delete files from storage
            }
        }

        // Handle new image uploads
        if ($request->hasFile('new_images')) {
            foreach ($request->file('new_images') as $file) {
                $path = $file->store('product_images', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                ]);
            }
        }

        return response()->json([
            'message' => 'Product updated successfully!',
            'product' => $product->load('images', 'user.deliveryLocations'),
        ]);
    }

    public function showByLink($link)
    {
        $product = Product::where('shareable_link', $link)
            ->with(['images', 'category', 'user.deliveryLocations'])
            ->firstOrFail();

        return response()->json($product);
    }

    public function myProducts(Request $request)
    {
        $user = $request->user();

        $products = $user->products()
            ->with(['images', 'category', 'user.deliveryLocations'])
            ->latest()
            ->get();

        return response()->json([
            'message'  => 'My uploaded products retrieved successfully',
            'products' => $products,
        ]);
    }

    // ✅ Delete product
    public function destroy(Request $request, $id)
    {
        $product = Product::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Delete related images (no longer deleting delivery locations)
        $product->images()->delete();
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        $products = Product::with(['images', 'category', 'user.deliveryLocations'])
            ->where('product_number', 'LIKE', '%' . $query . '%')
            ->orWhere('name', 'LIKE', '%' . $query . '%')
            ->orWhereHas('category', function ($q) use ($query) {
                $q->where('name', 'LIKE', '%' . $query . '%');
            })
            ->latest()
            ->get();

        return response()->json([
            'message'  => 'Search results',
            'products' => $products,
        ]);
    }

    public function getByUser($userId)
    {
        $products = Product::with(['images', 'category', 'user.deliveryLocations'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return response()->json([
            'products' => $products,
        ]);
    }

    public function showById($id)
    {
        $product = Product::with([
            'images',
            'category',
            'user.deliveryLocations',
            'user.profile', // 👈 Include the user's profile
        ])->findOrFail($id);

        return response()->json($product);
    }

    // ✅ Get all products publicly in random order
    public function getAllPublic()
    {
        $products = Product::with(['images', 'category', 'user.deliveryLocations'])
            ->inRandomOrder()
            ->take(50) // optional: limit results
            ->get();

        return response()->json([
            'message'  => 'All public products (randomized)',
            'products' => $products,
        ]);
    }

}

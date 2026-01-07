<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // <-- Import Log

class ProductCategoryController extends Controller
{
    // 🟢 Fetch all categories
    public function index()
    {
        return response()->json(ProductCategory::all());
    }

    // 🟢 Fetch all products under a specific category
    public function getProducts($id)
    {
        try {
            $category = ProductCategory::find($id);

            if (! $category) {
                Log::warning("Category with ID {$id} not found");
                return response()->json(['message' => 'Category not found'], 404);
            }

            $products = $category->products()->with('images')->get();

            return response()->json([
                'category' => $category,
                'products' => $products,
            ]);

        } catch (\Exception $e) {
            // Log the full exception message
            Log::error("Error fetching products for category {$id}: " . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'message' => 'Something went wrong while fetching products',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

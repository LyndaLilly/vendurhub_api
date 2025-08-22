<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;

class ProductCategoryController extends Controller
{
    public function index()
    {
        return response()->json(ProductCategory::all());
    }
}

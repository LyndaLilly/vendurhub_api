<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NewsletterController extends Controller
{
    // Submit newsletter
    public function store(Request $request)
    {
        try {

            Log::info("Newsletter submit request received", [
                'email' => $request->email
            ]);

            // Validate email
            $request->validate([
                'email' => 'required|email|unique:newsletters,email',
            ]);

            // Create newsletter entry
            $newsletter = Newsletter::create([
                'email' => $request->email
            ]);

            Log::info("Newsletter successfully saved", [
                'newsletter_id' => $newsletter->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Subscribed successfully'
            ], 200);

        } catch (\Exception $e) {

            Log::error("Newsletter submit failed", [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed: ' . $e->getMessage()
            ], 500);
        }
    }

    // Fetch all emails (admin)
    public function index()
    {
        return Newsletter::orderBy('id', 'desc')->get();
    }

    // Delete subscription (admin)
    public function destroy($id)
    {
        Newsletter::findOrFail($id)->delete();
        
        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }
}

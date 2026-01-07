<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Submit feedback (public)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'message' => 'required|string',
        ]);

        $feedback = Feedback::create([
            'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feedback submitted successfully!',
            'feedback' => $feedback,
        ]);
    }

    /**
     * Fetch all feedbacks (public)
     */
    public function index()
    {
        $feedbacks = Feedback::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'feedbacks' => $feedbacks,
        ]);
    }

    /**
     * Delete feedback by ID (can protect with Sanctum later)
     */
    public function destroy($id)
    {
        $feedback = Feedback::find($id);

        if (!$feedback) {
            return response()->json(['error' => 'Feedback not found'], 404);
        }

        $feedback->delete();

        return response()->json([
            'success' => true,
            'message' => 'Feedback deleted successfully!',
        ]);
    }
}

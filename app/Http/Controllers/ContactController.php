<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ContactMessageMail;
use Throwable;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        Log::info('Contact form submission started');

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
            ]);

            Log::info('Contact form validated', $validated);

            // ✅ Save to DB
            $contact = Contact::create($validated);
            Log::info('Contact saved to DB', ['contact_id' => $contact->id]);

            // ✅ Send email
            Log::info('Attempting to send contact email', [
                'to' => 'support@vendurhub.com'
            ]);

            Mail::to('support@vendurhub.com')
                ->send(new ContactMessageMail($contact));

            Log::info('Contact email sent successfully');

            return response()->json([
                'message' => 'Contact message submitted successfully ✅',
                'data' => $contact
            ]);
        } catch (Throwable $e) {
            Log::error('Contact form failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Message saved but email failed ❌'
            ], 500);
        }
    }

    public function index()
    {
        return response()->json(Contact::all());
    }
}

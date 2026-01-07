<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMessageMail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // ✅ Save to DB
        $contact = Contact::create($validated);

        // ✅ Send email to support
        Mail::to('support@vendurhub.com')
            ->send(new ContactMessageMail($contact));

        return response()->json([
            'message' => 'Contact message submitted successfully ✅',
            'data' => $contact
        ]);
    }

    public function index()
    {
        return response()->json(Contact::all());
    }
}
